<?php

declare(strict_types=1);

/*
 * This file is part of the overbeck/logistics.
 *
 * (c) overbeck<i@overbeck.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overbeck\Logistics\Exceptions;

/**
 * 网关发生错误异常.
 *
 * Class GatewayErrorException
 * Author ShuQingZai
 * DateTime 2020/7/31 16:08
 */
class GatewayErrorException extends Exception
{
    protected $raw = [];

    public function __construct(string $message, int $code = 0, array $raw = [])
    {
        $this->raw = $raw;

        parent::__construct($message, $code);
    }

    public function getRaw(): array
    {
        return $this->raw;
    }
}
