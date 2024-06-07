<?php

declare(strict_types=1);

namespace KkguanBe\HyperfTaobaoTmc;

use KkguanBe\HyperfTaobaoTmc\Listener\MainWorkerStartListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                MainWorkerStartListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of tmc.',
                    'source' => __DIR__ . '/../publish/tmc.php',
                    'destination' => BASE_PATH . '/config/autoload/tmc.php',
                ],
            ],
        ];
    }
}
