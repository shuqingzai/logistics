<h1 align="center"> logistics </h1>

<p align="center"> A quick library for querying express logistics information.</p>

[![Build Status](https://travis-ci.org/shuqingzai/logistics.svg?branch=master)](https://travis-ci.org/shuqingzai/logistics)
![StyleCI build status](https://github.styleci.io/repos/283132448/shield)
[![Latest Stable Version](https://poser.pugx.org/overbeck/logistics/v)](//packagist.org/packages/overbeck/logistics)
[![Total Downloads](https://poser.pugx.org/overbeck/logistics/downloads)](//packagist.org/packages/overbeck/logistics)
[![Latest Unstable Version](https://poser.pugx.org/overbeck/logistics/v/unstable)](//packagist.org/packages/overbeck/logistics)
[![License](https://poser.pugx.org/overbeck/logistics/license)](//packagist.org/packages/overbeck/logistics)
[![composer.lock](https://poser.pugx.org/overbeck/logistics/composerlock)](//packagist.org/packages/overbeck/logistics)
[![.gitattributes](https://poser.pugx.org/overbeck/logistics/gitattributes)](//packagist.org/packages/overbeck/logistics)


## 简介

**用于快捷查询快递物流信息，返回统一格式，无需担心字段格式不一致导致的各种问题**

## 支持平台

目前支持以下平台

* [快递100](https://www.kuaidi100.com/)
* [快递鸟](https://www.kdniao.com/)

## 环境依赖

* PHP >= 7.2
* json拓展
* openssl拓展
* simplexml拓展

## 安装

```shell
$ composer require overbeck/logistics -vvv
```

## 使用

```php
require __DIR__ . '/vendor/autoload.php';

use Overbeck\Logistics\Logistics;

$config = [
    /*
     * 全局 http 
     * 请求配置 参考 https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html
     */
    'http'         => [
        'timeout'         => 5.0,
        'connect_timeout' => 5.0
    ],

    /*
     * 默认网关配置，如果设置此项，则只会使用该网关请求，否则会循环 gateways 调用请求不同的网关
     */
    'default'      => '',
    
    /*
     * 禁用网关，默认情况下会循环调用  gateways 下的所有可用网关，你可以添加网关名称到此禁用
     */
    'disable'      => [],
    
    /*
     * 网关配置
     */
    'gateways'     => [
        'kuaidi100' => [
            'key'      => '12124564561', // key
            'customer' => 'sahdkjsadjashuidhasdbak', // customer
            /*
             * 可以单独为指定的网关配置 http 请求信息，未设置则读取全局
             */
            'http'         => [
                'timeout'         => 15.0,
                'connect_timeout' => 15.0
            ],
        ],
        'kdniao'    => [
            'appKey'      => '', // appKey
            'EBusinessID' => '', // EBusinessID
        ],
        // ...
    ],
    /*
     * 格外配置物流公司列表
     */
    'company_file' => []
];
$logistics = new Logistics($config);
$res       = $logistics->query('123456789','顺丰速运');
```

### 快速使用示例

```php
$logistics->query('123456789'); // 仅用快递单号查询，不清楚快递公司时可用
$logistics->query('123456789','顺丰速运'); // 锁定快递公司，更快速的查询
```

### 物流公司

如果传入物流公司名称，可以快速锁定物流单号，无需额外的`http`请求第三方网关获取，减少开销

默认提供 `/vendor/overbeck/logistics/src/config/company.php` 物流公司列表文件，已包含一些常用的物流公司与物流公司`code` ,允许用户自定义配置文件或动态设置

#### 文件配置物流公司

在 `congfig.company_file` 设置文件路径，支持配置多个文件，并且支持两种格式 ( `php`、`json` )

```php
// 格外配置物流公司列表文件
'company_file' => [__DIR__ . '/company1.php',__DIR__ . '/company1.json']
```

每种文件格式示例

**php**

`php` 格式文件是直接使用 `include` 关键字引入，所以必须使用`return` 关键字返回数组 

`company1.php` 内容 

```php
return [
    [
        'name' => '顺丰速运',
        'code' =>
            [
                'aliyun'    => 'SFEXPRESS',
                'juhe'      => 'sf',
                'kuaidi100' => 'shunfeng',
                'kdniao'    => 'SF',
            ],
    ],
    [
        'name' => '申通快递',
        'code' =>
            [
                'aliyun'    => 'STO',
                'juhe'      => 'sto',
                'kuaidi100' => 'shentong',
                'kdniao'    => 'STO',
            ],
    ]
];
```

**json**

`company1.json` 文件内容

```json
[
    {
        "name": "顺丰速运1",
        "code": {
            "aliyun": "SFEXPRESS",
            "juhe": "sf",
            "kuaidi100": "shunfeng",
            "kdniao": "SF"
        }
    },
    {
        "name": "申通快递2",
        "code": {
            "aliyun": "STO",
            "juhe": "sto",
            "kuaidi100": "shentong",
            "kdniao": "STO"
        }
    }
]
```

#### 动态配置物流公司

当然，你也可以动态直接传入二维数组而不需要额外创建物流公司配置文件

```php
$logistics->setCompanyList(array $companyList);
```

**注意：不管你是直接传入数据还是配置文件，都需要保持与上述示例中的数据结构一致，如果物流公司名称和已有的物流公司名称(`name`)一样，则会替代已有的物流公司**

#### 获取物流公司列表

你可以获取所有的物流公司列表

```php
$logistics->getCompanyList();
```

也可以获取默认的物流公司列表

```php
$logistics->getDefaultCompanyList();
```



### 可用网关

一般情况下，可用网关是在配置文件中读取，你也可以直接传递第三个参数指定网关

```php
$logistics->query('123456789','顺丰速运', 'kuaidi100'); // 指定单个网关的时，可以直接传递字符串
$logistics->query('123456789','顺丰速运', ['kuaidi100', 'kuaidiniao']);
```

**注意：如果传递的网关不可用，会抛出 `\Overbeck\Logistics\Exceptions\InvalidArgumentException` 异常**

### 禁用网关

如果你配置的网关很多，你也可以临时禁用某些网关，只需要在 `config.disable` 配置需要禁用的网关即可

```php
 // 禁用网关，默认情况下会循环调用  gateways 下的所有可用网关，你可以添加网关名称到此禁用
 'disable'      => [],
```

### 默认网关

当你只是使用一个网关时，可以直接配置默认网关即可

**注意：如果配置了默认网关，那么其他网关都会失效，只有默认网关会有效**

```php
 // 默认配置，如果设置此项，就只会使用该网关请求，否则会循环 gateways 调用请求不同的网关
 'default'      => 'kuaidi100',
```

## 响应结果

统一返回一个二维数组，数组中的值是每个网关的结果集，它是 `\Overbeck\Logistics\Supports\Collection` 结果集对象

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
                      "context" => "铜仁市 【中国邮政集团公司贵州省石阡县寄递事业部本部揽投部】已收件,揽投员:胡俊,电话:13758785985"
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
      "exception" => \Overbeck\Logistics\Exceptions\Exceptions // 错误响应对象
    ]
  }
     // ...
]

```

**读取信息**

```php
$res = $logistics->query('123456789','顺丰速运');

/** @var \Overbeck\Logistics\Supports\Collection $kd */
$kd  = $res['kuaidi100'];
echo $kd->get('status');
dump($kd->get('result.list'));
// ....
```



**`result.status` 说明**

| 值（status） | 名称（status_name） |                             解析                             |
| :----------: | :-----------------: | :----------------------------------------------------------: |
|      99      |    快递查询异常     | 请求发送成功，但是业务查询失败，具体原因查看 `original_data` 的原数据 |
|      1       |   快递收件(揽件)    |                     货物已由快递公司揽收                     |
|      2       |       运输中        |                      货物处于运输过程中                      |
|      3       |       派件中        |                       货物正在进行派件                       |
|      4       |       已签收        |                           正常签收                           |
|      5       |       疑难件        |        网关无法解析或解析错误的状态，最好需要人工核实        |
|      6       |      退件签收       |                     货物退回发货人并签收                     |
|      7       |        拒签         |                        收件人明确拒收                        |
|      8       |        退回         |                  货物正处于返回发货人的途中                  |

## 异常处理

系统定义三个异常类

`\Overbeck\Logistics\Exceptions\InvalidArgumentException` 用于处理参数错误异常

`\Overbeck\Logistics\Exceptions\GatewayErrorException` 用于处理请求网关响应数据错误

`\Overbeck\Logistics\Exceptions\GatewayAvailableException` 当所有可用网关都不能使用时，抛出该异常

## Laravel 应用

### 1. 注册服务

在 `config/app.php` 注册 **ServiceProvider** 和 **Facade** ( `Laravel 5.5 +` 无需手动注册，可跳过此步)

```php
'providers' => [
    // ...
    Overbeck\Logistics\Laravel\ServiceProvider::class,
],
'aliases' => [
    // ...
    'Logistics' => Overbeck\Logistics\Laravel\Logistics::class,
],
```

### 2. 发布配置文件

```php
php artisan vendor:publish --provider="Overbeck\Logistics\Laravel\ServiceProvider"
```

修改应用根目录下的 `config/logistics.php` 中对应的参数即可。

### 3. 门面

**门面类是 `\Overbeck\Logistics\Laravel\Logistics`**

示例

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Overbeck\Logistics\Laravel\Logistics;

class LogisticsController extends Controller
{
    public function query(Request $request)
    {
        dd(Logistics::query($request->route('code'), $request->input('company')));
    }
}
```


## 参考

* [PHP 扩展包实战教程 - 从入门到发布](https://learnku.com/courses/creating-package)
* [overtrue/easy-sms](https://github.com/overtrue/easy-sms)
* [overtrue/wechat](https://github.com/overtrue/wechat)

## 最后
欢迎提出 [Issue](https://github.com/shuqingzai/logistics/issues) 和 [Pull request](https://github.com/shuqingzai/logistics/pulls)，也可以留言`929024757@qq.com`

## License

MIT