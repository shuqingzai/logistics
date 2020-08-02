<?php
declare(strict_types=1);

return [
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
            'key'      => env('LOGISTICS_APP_KEY_KD100'), // key
            'customer' => env('LOGISTICS_SECRET_KD100'), // customer
            /*
             * 可以单独为指定的网关配置 http 请求信息，未设置则读取全局
             */
            'http'     => [
                'timeout'         => 15.0,
                'connect_timeout' => 15.0
            ],
        ],
        'kdniao'    => [
            'appKey'      => env('LOGISTICS_APP_KEY_KDNIAN'), // appKey
            'EBusinessID' => env('LOGISTICS_SECRET_KDNIAN'), // EBusinessID
        ],
        // ...
    ],

    /*
     * 格外配置物流公司列表
     */
    'company_file' => []
];