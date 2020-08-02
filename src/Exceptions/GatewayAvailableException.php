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

use Overbeck\Logistics\Interfaces\GatewayAvailableInterface;
use Throwable;

/**
 * 网关可用异常.
 *
 * Class GatewayAvailableException
 *
 * @author ShuQingZai
 * DateTime 2020/7/31 16:07
 */
class GatewayAvailableException extends Exception implements GatewayAvailableInterface
{
    protected $results = [];

    protected $exceptions = [];

    public function __construct(array $results = [], $code = 0, Throwable $previous = null)
    {
        $this->results = $results;
        $this->exceptions = \array_column($results, 'exception', 'gateway');

        parent::__construct('The gateways have failed. You can check "\Overbeck\Logistics\Interfaces\GatewayAvailableInterface" to get the results', $code, $previous);
    }

    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @param string $gateway
     *
     * @return mixed|null
     */
    public function getException(string $gateway)
    {
        return $this->exceptions[$gateway] ?? null;
    }

    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    /**
     * @return mixed
     */
    public function getLastException()
    {
        return end($this->exceptions);
    }
}
