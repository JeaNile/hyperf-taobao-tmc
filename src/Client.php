<?php

declare(strict_types=1);

namespace KkguanBe\HyperfTaobaoTmc;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\WebSocketClient\Client as WsClient;
use Hyperf\WebSocketClient\ClientFactory;
use Hyperf\WebSocketClient\Frame;
use KkguanBe\HyperfTaobaoTmc\Constants\MessageFields;
use KkguanBe\HyperfTaobaoTmc\Constants\MessageKind;
use KkguanBe\HyperfTaobaoTmc\Constants\MessageType;
use KkguanBe\HyperfTaobaoTmc\Exception\OnMessageException;
use KkguanBe\HyperfTaobaoTmc\Message\Message;
use KkguanBe\HyperfTaobaoTmc\Message\Reader;
use KkguanBe\HyperfTaobaoTmc\Message\Writer;
use KkguanBe\HyperfTaobaoTmc\Util\TmcUtil;
use Psr\Container\ContainerInterface;
use Swoole\Timer;

class Client
{
    public WsClient $client;

    protected StdoutLoggerInterface $logger;

    protected ClientConfig $config;

    protected ClientFactory $clientFactory;

    protected ?int $reconnectTimerId = null;

    protected ?int $pullRequestTimerId = null;

    protected string $token;

    protected bool $connected = false;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->clientFactory = $container->get(ClientFactory::class);
    }

    public function __destruct()
    {
        $this->close();
    }

    public function setConfig(ClientConfig $config)
    {
        $this->config = $config;
    }

    public function connect(): Client
    {
        $this->doConnect();
        $this->reconnect();
        $this->pullRequest();
        return $this;
    }

    public function reconnect()
    {
        if ($this->reconnectTimerId) {
            Timer::clear($this->reconnectTimerId);
        }
        // 定期检查连接，关闭进行重连
        $this->reconnectTimerId = Timer::tick($this->config->getOptions()->getReconnectTime(), function () {
            try {
                if (! $this->isConnected()) {
                    $this->logger->info('reconnect...');
                    $this->close();
                    $this->doConnect();
                }
            } catch (\Throwable $throwable) {
                $this->logger->error('reconnect error:' . $throwable->getMessage());
            }
        });
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function close()
    {
        $this->connected = false;
        unset($this->client);
    }

    public function createSign(array $params, string $secret, string $signMethod = 'MD5'): string
    {
        // 第一步：把字典按Key的字母顺序排序
        ksort($params);

        // 第二步：把所有参数名和参数值串在一起
        $query = '';
        if ($signMethod === 'MD5') {
            $query .= $secret;
        }
        foreach ($params as $key => $value) {
            if (! empty($key) && ! empty($value)) {
                $query .= $key . $value;
            }
        }

        // 第三步：把请求主体拼接在参数后面
        if (! empty($body)) {
            $query .= $body;
        }

        // 第四步：使用MD5/HMAC加密
        if ($signMethod === 'HMAC') {
            $bytes = hash_hmac('md5', $query, $secret, true);
        } elseif ($signMethod === 'HMAC_SHA256') {
            $bytes = hash_hmac('sha256', $query, $secret, true);
        } else {
            $query .= $secret;
            $bytes = md5($query, true);
        }

        // 第五步：把二进制转化为大写的十六进制
        return strtoupper(bin2hex($bytes));
    }

    public function send(Message $msg): bool
    {
        $w = Writer::write($msg);
        $this->logger->info(sprintf('send message:%s:%s', json_encode($msg->toArray()), $w));
        return $this->client->push($w, WEBSOCKET_OPCODE_BINARY, 33);
    }

    // public function send(string $topic, string $content, string $session = null)
    // {
    // }

    public function onMessage(Frame $ret)
    {
        $resMsg = $this->parseMessage($ret->getData());
        try {
            if ($resMsg->getMessageType() == MessageType::CONNECTACK) {
                $this->connectAck($resMsg);
                return;
            }

            $reqMsg = new Message();

            if ($resMsg->getMessageType() == MessageType::SEND) {
                $reqMsg = $this->confirm($reqMsg, intval($resMsg->getContent()['id']));
            }

            $this->send($reqMsg);

            if ($this->config->getHandler()) {
                $this->config->getHandler()->handle($this->config->getServiceName(), $resMsg);
            }
        } catch (\Throwable $throwable) {
            $reqMsg = new Message();
            $this->fail($reqMsg, intval($resMsg->getContent()['id']), $throwable->getMessage());
            throw new OnMessageException(sprintf('process message error:%s:%s', $this->config->getAppKey(), $throwable->getMessage()));
        }
    }

    public function parseMessage(string $data): Message
    {
        $this->logger->info(sprintf('app_key:%s raw data:%s', $this->config->getAppKey(), $data));
        return Reader::read($data);
    }

    protected function connectAck(Message $msg)
    {
        $this->connected = true;
        $this->logger->info(sprintf('app_key:%s token:%s', $this->config->getAppKey(), $msg->getToken()));
        $this->token = $msg->getToken();
    }

    protected function confirm(Message $msg, int $id): Message
    {
        $this->connected = true;
        $msg->setMessageType(MessageType::SEND);
        $msg->setContent([
            MessageFields::KIND => MessageKind::CONFIRM,
            MessageFields::CONFIRM_ID => $id,
        ]);
        $msg->setToken($this->token);
        return $msg;
    }

    protected function fail(Message $msg, int $id, $errorMsg): Message
    {
        $msg->setMessageType(MessageType::SEND);
        $msg->setContent([
            MessageFields::KIND => MessageKind::FAILED,
            MessageFields::CONFIRM_ID => $id,
            MessageFields::CONFIRM_MSG => $errorMsg,
        ]);
        return $msg;
    }

    protected function pullRequest()
    {
        if ($this->pullRequestTimerId) {
            Timer::clear($this->pullRequestTimerId);
        }
        $client = $this->client;
        $this->pullRequestTimerId = Timer::tick($this->config->getOptions()->getPullRequestTime(), function () use ($client) {
            try {
                $msg = new Message();
                $msg->setMessageType(MessageType::SEND);
                $msg->setContent([
                    MessageFields::KIND => MessageKind::PULL_REQUEST,
                ]);
                $msg->setToken($this->token);
                $this->client = $client;
                if (! $this->send($msg)) {
                    $this->logger->error('pull_request send message fail');
                    $this->close();
                    $this->doConnect();
                }
            } catch (\Throwable $throwable) {
                $this->logger->error('pull_request error:' . $throwable->getMessage());
            }
        });
    }

    private function doConnect(): void
    {
        $this->client = $this->clientFactory->create($this->config->getUri());

        $connHeader = $signHeader = [
            'app_key' => $this->config->getAppKey(),
            'group_name' => $this->config->getGroupName(),
            'timestamp' => TmcUtil::getMillisecondTimestamp(),
        ];
        try {
            $connHeader['sign'] = $this->createSign($signHeader, $this->config->getAppSecret());
        } catch (\Throwable $throwable) {
            $this->logger->error('failed to generate signature:' . $throwable->getMessage());
        }

        $connHeader = array_merge($connHeader, [
            'sdk' => ClientConfig::SDK,
            'intranet_ip' => swoole_get_local_ip()['eth0'],
        ]);

        $msg = new Message();
        $msg->setMessageType(MessageType::CONNECT);
        $msg->setContent($connHeader);

        $this->send($msg);

        $this->logger->Info(sprintf('app_key:%s connected to TMC server: %s', $this->config->getAppKey(), $this->config->getUri()));
    }
}
