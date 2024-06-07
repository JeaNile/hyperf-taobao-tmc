<?php

declare(strict_types=1);

namespace KkguanBe\HyperfTaobaoTmc;

use Hyperf\Utils\Str;

class ClientOptionsConfig
{
    protected int $reconnectTime = 15;

    protected int $pullRequestTime = 15;

    private array $configurable = [
        'reconnect_time',
        'pull_request_time',
    ];

    public function __construct(array $options)
    {
        if (! empty($options)) {
            $this->initialize($options);
        }
    }

    public function getReconnectTime(): int
    {
        return $this->reconnectTime;
    }

    public function setReconnectTime(int $reconnectTime): ClientOptionsConfig
    {
        $this->reconnectTime = $reconnectTime * 1000;
        return $this;
    }

    public function getPullRequestTime(): int
    {
        return $this->pullRequestTime;
    }

    public function setPullRequestTime(int $pullRequestTime): ClientOptionsConfig
    {
        $this->pullRequestTime = $pullRequestTime * 1000;
        return $this;
    }

    private function initialize(array $options)
    {
        foreach ($options as $key => $value) {
            if (in_array($key, $this->configurable, true) === false) {
                continue;
            }

            $method = 'set' . ucfirst(Str::camel($key));

            if (method_exists($this, $method) === true) {
                $this->{$method}($value);
            }
        }
    }
}
