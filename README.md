<h1 align="center"> logistics </h1>

<p align="center"> A logistics library.</p>

## 简介

**用于快捷查询快递物流信息，返回统一格式，无需担心字段格式不一致导致的各种问题**

## 支持平台

目前支持以下平台

* [快递100](https://www.kuaidi100.com/)
* [快递鸟](https://www.kdniao.com/)

## 环境依赖

* PHP >= 7.2

## 安装

```shell
$ composer require shuqingzai/logistics -vvv
```

## 使用

```php
require __DIR__ . '/vendor/autoload.php';

use Sqz\Logistics\Logistics;

$config = [
    // 全局 http 请求配置 参考 https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html
    'http'         => [
        'timeout'         => 5.0,
        'connect_timeout' => 5.0
    ],
    // 默认配置，如果设置此项，就只会使用该网关请求，否则会循环 gateways 调用请求不同的网关
    'default'      => '',
    // 禁用网关，默认情况下会循环调用  gateways 下的所有可用网关，你可以添加网关名称到此禁用
    'disable'      => [],
    // 网关配置
    'gateways'     => [
        'kuaidi100' => [
            'app_key'    => 'VFPrytxx7930', // appKey
            'app_secret' => '5D22D64CBB3F58EABB1ECC1095DF1A4F', // customer
            // 可以单独为指定的网关配置 http 请求信息，未设置则读取全局
            'http'         => [
                'timeout'         => 15.0,
                'connect_timeout' => 15.0
            ],
        ],
        'kdniao'    => [
            'app_key'    => '', // appKey
            'app_secret' => '', // eBusinessID
        ],
        // ...
    ]
];
$logistics = new Logistics($config);
$res       = $logistics->query('123456789','顺丰速运');
```

### 快速使用示例

```php
$logistics->query('123456789'); // 仅用快递单号查询，不清楚快递公司时可用
$logistics->query('123456789','顺丰速运'); // 锁定快递公司，更快速的查询
```

### 可用网关

一般情况下，可用网关是在配置文件中读取，你也可以直接传递第三个参数指定网关

```php
$logistics->query('123456789','顺丰速运', 'kuaidi100'); // 指定单个网关的时，可以直接传递字符串
$logistics->query('123456789','顺丰速运', ['kuaidi100', 'kuaidiniao']);
```

**注意：如果传递的网关不可用，会抛出 `\Sqz\Logistics\Exceptions\GatewayErrorException` 异常**

## 返回结果

统一返回一个二维数组，数组中的值是每个网关的结果集，它是 `\Sqz\Logistics\Supports\Collection` 结果集对象

```php
 [
  "kuaidi100" => {
      [
          "gateway" => "kuaidi100"
          "status" => "success" // 网关请求成功 success 发生异常 failure
          "result" => [ // 查询结果
              "code" => 1 // 1 表示有结果 list会有数据 0 是查无结果
              "status" => 4 // 统一的状态返回
              "status_name" => "已签收" // 状态名称描述
              "company_code" => "youzhengbk" // 快递公司code
              "company_name" => "邮政快递包裹" // 快递公司名称
              "tracking_number" => "1178007591424" // 快递单号
              "list" => [
                  // ...
                  [
                      "context" => "离开【中国邮政集团公司贵州省石阡县寄递事业部本部揽投部】,下一站【贵州石阡县中心】"
                      "date_time" => "2020-07-27 17:46:27"
                  ],
                  [
                      "context" => "铜仁市 【中国邮政集团公司贵州省石阡县寄递事业部本部揽投部】已收件,揽投员:胡万军,电话:13765610418"
                      "date_time" => "2020-07-27 17:26:49"
                  ],
                 // ...            
              ],
              "original_data" => "{...}" // json字符串 响应原数据信息
          ]
  	  ]
  }
  "kdniao" => {
    [
      "gateway" => "kdniao"
      "status" => "failure"
      "exception" => \Sqz\Logistics\Exceptions\Exceptions // 错误响应对象
    ]
  }
     // ...
]

```

**`result.status` 说明**

|  值  | 含义（status_name） |
| :--: | :-----------------: |
|  99  |    快递查询异常     |
|  1   |   快递收件(揽件)    |
|  2   |       运输中        |
|  3   |       派件中        |
|  4   |       已签收        |
|  5   |       疑难件        |
|  6   |      退件签收       |
|  7   |        拒签         |
|  8   |        退回         |
|  9   |       超时件        |
|  10  |      派送失败       |


## 参考
* [PHP 扩展包实战教程 - 从入门到发布](https://laravel-china.org/courses/creating-package)
* [overtrue/easy-sms](https://github.com/overtrue/easy-sms)

## 最后
欢迎提出 issue 和 pull request，也可以留言`929024757@qq.com`

## License

MIT