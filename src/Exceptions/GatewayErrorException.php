<?php

declare(strict_types=1);

/*
 * This file is part of the overbeck/logistics.
 *
 * (c) overbeck<929024757@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overbeck\Logistics\Exceptions;

/**
 * 网关发生错误异常.
 *
 * Class GatewayErrorException
 *
 * @author ShuQingZai
 * DateTime 2020/7/31 16:08
 */
class GatewayErrorException extends Exception
{
    protected $results = [];

    public function __construct(string $message, int $code = 0, array $results = [])
    {
        $this->results = $results;

        parent::__construct($message, $code);
    }

    /**
     * 原始响应数据.
     *
     * @return array
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
