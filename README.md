## 概述
[淘宝消息服务 TMC](https://open.taobao.com/doc.htm?docId=101663&docType=1) 的 PHP 版本，是基于 Hyperf 框架实现。

## 特性
- 基于 hyperf 框架
- 使用 swoole 的 websocket client 实现
- 可配置化
- 支持多应用多店铺
- 自动重连
- 心跳维持

## 使用要求

- PHP 7.4+
- Swoole 4.4LTS+
- Hyperf 2.2+

## 安装

### 1. Composer安装
```php
composer require jeanile/hyperf-taobao-tmc
```

### 2. 发布配置文件
```
php bin/hyperf.php vendor:publish jeanile/hyperf-taobao-tmc
```

配置参数说明：

| 配置项          | 类型          | 说明                     | 默认值         |
|:-------------|:------------|:-----------------------|:------------|
| enable       | bool        | 消息回调开关                 | true        |
| uri          | string      | 淘宝 ws 链接               | ws://mc.api.taobao.com |
| app_key      | string      | 应用 app_key             | 30          |
| app_secret   | string      | 应用 app_secret          | 30          |
| group_name   | string      | 分组名，目前仅支持默认分组          | default     |
| options.reconnect_time     | int         | 每隔n秒执行重连检查             | 15（秒）       |
| options.pull_request_time     | int         | 每隔n秒主动请求维持连接           | 60（秒）       |
| service_name | string      | 店铺唯一标识，可用 app_key 或自定义 |             |
| handler      | string      | 消息处理类                  |             |

多个店铺配置：

```php
<?php

declare(strict_types=1);

return [
    'enable' => true,
    'conn' => [
        '官方旗舰店' => [
            'uri' => env('TMC_URI'),
            'app_key' => env('TIANMAO_APP_KEY'),
            'app_secret' => env('TIANMAO_APP_SECRET'),
            'group_name' => 'default',
            'options' => [
                'reconnect_time' => 15,
                'pull_request_time' => 60,
            ],
            'service_name' => 'shop_unique_id1',
            'handler' => \App\Notify\TMCMessageHandler::class,
        ],
        '海外旗舰店' => [
            'uri' => env('TMC_URI'),
            'app_key' => env('TIANMAO_APP_KEY'),
            'app_secret' => env('TIANMAO_APP_SECRET'),
            'group_name' => 'default',
            'options' => [
                'reconnect_time' => 15,
                'pull_request_time' => 60,
            ],
            'service_name' => 'shop_unique_id2',
            'handler' => \App\Notify\TMCMessageHandler::class,
        ],
    ],
];
```

## 使用

#### 创建一个 handle 类，并实现 MessageHandlerInterface

```php
<?php

declare(strict_types=1);

namespace App\Notify;

use Hyperf\Contract\StdoutLoggerInterface;
use JeaNile\HyperfTaobaoTmc\Handler\MessageHandlerInterface;
use JeaNile\HyperfTaobaoTmc\Message\Message;
use Psr\Container\ContainerInterface;

class TMCMessageHandler implements MessageHandlerInterface
{
    protected ContainerInterface $container;

    protected StdoutLoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function handle(string $serviceName, Message $resMsg): void
    {
        $this->logger->info(sprintf('shop:%s:resMsg:%s', $serviceName, $resMsg->toString()));

        // 业务处理
    }
}
```
## 注意事项

项目部署在多个台服务器（pod）上时会建立多个连接，但消息只会被一台机器消费了，因此如果区分多个环境，如：测试、预发布、生产环境，建议上线后仅**生产环境**开启（enable 设置 true），其他环境关闭，避免抢占消费消息。

## TODO

- [ ] 支持多环境接收消息开关（如：测试环境也能开启但仅接收不发送 ACK）
- [ ] 支持分组
- [ ] 店铺按独立进程隔离

## 参考

https://github.com/period331/phptmc/tree/master
