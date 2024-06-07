<?php

declare(strict_types=1);

namespace KkguanBe\HyperfTaobaoTmc\Handler;

use KkguanBe\HyperfTaobaoTmc\Message\Message;

interface MessageHandlerInterface
{
    public function handle(string $serviceName, Message $resMsg): void;
}
