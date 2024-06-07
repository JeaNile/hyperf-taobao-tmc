<?php

declare(strict_types=1);

namespace KkguanBe\HyperfTaobaoTmc;

use Psr\Container\ContainerInterface;
use Swoole\Coroutine;

class ConsumerManager
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container) {}

    public function run()
    {
        foreach ($this->config->getConfigs() as $config) {
            Coroutine::create(function () use ($config) {
                $clientFactory = new Client($this->container);
                $clientFactory->setConfig($config);
                if ($clientFactory->connect()) {
                    while (true) {
                        $ret = $clientFactory->client->recv();
                        if ($ret === false) {
                            $this->logger->error('tmc client recv failed:%s' . swoole_last_error());
                        }
                        if (empty($ret->getData())) {
                            Coroutine::sleep(0.1);
                        }

                        try {
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
