<?php

declare(strict_types=1);

namespace KkguanBe\HyperfTaobaoTmc;

use Hyperf\Utils\ApplicationContext;
use KkguanBe\HyperfTaobaoTmc\Exception\InvalidArgumentException;
use KkguanBe\HyperfTaobaoTmc\Handler\MessageHandlerInterface;

class ClientConfig
{
    public const SDK = 'SDK';

    protected string $uri = 'ws://mc.api.taobao.com';

    protected string $appKey;

    protected string $appSecret;

    protected string $groupName = 'default';

    protected ClientOptionsConfig $options;

    protected string $serviceName;

    protected MessageHandlerInterface $handler;

    public static function build($config): ClientConfig
    {
        $clientConfig = new static();
        ! empty($config['uri']) && $clientConfig->setUri($config['uri']);
        ! empty($config['group_name']) && $clientConfig->setGroupName($config['group_name']);
        if (! $config['app_key'] || ! $config['app_secret']) {
            throw new \InvalidArgumentException('app_key and app_secret must be configured');
        }
        if (! $config['service_name']) {
            throw new \InvalidArgumentException('service_name must be configured');
        }
        $clientConfig->setAppKey($config['app_key']);
        $clientConfig->setAppSecret($config['app_secret']);
        $clientConfig->setOptions($config['options'] ?? []);
        $clientConfig->setServiceName($config['service_name']);
        $clientConfig->setHandler($config['handler'] ?? null);

        return $clientConfig;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setUri(string $uri): ClientConfig
    {
        $this->uri = $uri;
        return $this;
    }

    public function getAppKey(): string
    {
        return $this->appKey;
    }

    public function setAppKey(string $appKey): ClientConfig
    {
        $this->appKey = $appKey;
        return $this;
    }

    public function getAppSecret(): string
    {
        return $this->appSecret;
    }

    public function setAppSecret(string $appSecret): ClientConfig
    {
        $this->appSecret = $appSecret;
        return $this;
    }

    public function getGroupName(): string
    {
        return $this->groupName;
    }

    public function setGroupName(string $groupName): ClientConfig
    {
        $this->groupName = $groupName;
        return $this;
    }

    public function getOptions(): ClientOptionsConfig
    {
        return $this->options;
    }

    public function setOptions(array $options): ClientConfig
    {
        $this->options = new ClientOptionsConfig($options);
        return $this;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function setServiceName(string $serviceName): ClientConfig
    {
        $this->serviceName = $serviceName;
        return $this;
    }

    public function setHandler($handler): ClientConfig
    {
        if (! $handler) {
            throw new InvalidArgumentException('handler must be configured');
        }

        $container = ApplicationContext::getContainer();
        $this->handler = $container->get($handler);
        return $this;
    }

    public function getHandler(): ?MessageHandlerInterface
    {
        return $this->handler;
    }
}
