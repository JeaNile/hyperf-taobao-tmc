<?php

declare(strict_types=1);

namespace KkguanBe\HyperfTaobaoTmc;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class Config
{
    protected ContainerInterface $container;

    protected array $configs;

    protected ConfigInterface $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
        if (! $this->config->get('tmc.enable')) {
            return;
        }
        $configs = $this->config->get('tmc.conn', []);
        foreach ($configs as $key => $config) {
            if (empty($config['app_key'])) {
                continue;
            }
            $this->configs[$key] = ClientConfig::build($config);
        }
    }

    public function getConfigs(): array
    {
        return $this->configs ?? [];
    }

    public function get(string $name): array
    {
        return $this->configs[$name] ?? [];
    }

    public function isEnable(): bool
    {
        return $this->config->get('tmc.enable', false);
    }
}
