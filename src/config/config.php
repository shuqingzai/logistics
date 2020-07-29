<?php
return [
    // http请求配置 参考 https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html
    'http'              => [
        'timeout'         => 5.0,
        'connect_timeout' => 5.0
    ],
    // 默认配置，如果设置此项，就只会使用该网关请求，否则会循环 gateways 调用请求不同的网关
    'default'           => 'kuaidi100',
    // 网关配置
    'gateways'          => [
        'kuaidi100' => [
            'app_key'    => env('LOGISTICS_APP_KEY'), // appKey
            'app_secret' => env('LOGISTICS_SECRET'), // customer
        ],
        'kdniao'    => [
            'app_key'    => env('LOGISTICS_APP_CODE'), // appKey
            'app_secret' => env('LOGISTICS_CUSTOMER'), // eBusinessID
        ],
    ],
    // 快递公司名称文件目录，可自定义，参考 /vendor/logistics/src/config/company.php
    'company_file' => []
];