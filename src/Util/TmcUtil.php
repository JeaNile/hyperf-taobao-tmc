<?php

declare(strict_types=1);

namespace KkguanBe\HyperfTaobaoTmc\Util;

class TmcUtil
{
    public static function getMillisecondTimestamp(): string
    {
        [$microseconds, $seconds] = explode(' ', microtime());
        return sprintf('%.0f', (floatval($microseconds) + floatval($seconds)) * 1000);
    }
}
