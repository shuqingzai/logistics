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

namespace Overbeck\Logistics\Interfaces;

/**
 * 网关可用异常接口.
 *
 * Interface GatewayAvailableInterface
 *
 * @author ShuQingZai
 * DateTime 2020/7/31 16:09
 */
interface GatewayAvailableInterface
{
    /**
     * 获取结果.
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function getResults(): array;

    /**
     * 获取单一网关异常.
     *
     * @return mixed
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function getException(string $gateway);

    /**
     * 获取所有网关异常.
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function getExceptions(): array;

    /**
     * 获取以后一个异常.
     *
     * @return mixed
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function getLastException();
}
