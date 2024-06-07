<?php

declare(strict_types=1);

namespace KkguanBe\HyperfTaobaoTmc\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use KkguanBe\HyperfTaobaoTmc\Client;
use KkguanBe\HyperfTaobaoTmc\Config;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine;

class MainWorkerStartListener implements ListenerInterface
{
    protected StdoutLoggerInterface $logger;

    protected ContainerInterface $container;

    protected Config $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            MainWorkerStart::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        $this->config = $this->container->get(Config::class);

        if (! $this->config->isEnable()) {
            return;
        }
        foreach ($this->config->getConfigs() as $config) {
            Coroutine::create(function () use ($config) {
                $clientFactory = new Client($this->container);
                $clientFactory->setConfig($config);
                if ($clientFactory->connect()) {
                    while (true) {
                        try {
                            $ret = $clientFactory->client->recv();
                            if ($ret === false || empty($ret->getData())) {
                                Coroutine::sleep(0.1);
                                if ($ret === false) {
                                    $this->logger->error('tmc client recv failed:' . swoole_last_error());
                                }
                                continue;
                            }
                            $clientFactory->onMessage($ret);
                        } catch (\Throwable $throwable) {
                            $this->logger->error((string) $throwable);
                        }
                    }
                }
            });
        }
    }
}
