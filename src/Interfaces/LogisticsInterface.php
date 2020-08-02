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

use Overbeck\Logistics\Exceptions\GatewayAvailableException;
use Overbeck\Logistics\Exceptions\InvalidArgumentException;

/**
 * 物流接口.
 *
 * Interface LogisticsInterface
 *
 * @author ShuQingZai
 * DateTime 2020/7/31 16:09
 */
interface LogisticsInterface
{
    /**
     * 查询物流
     *
     * @param string       $logisticNumber 物流单号
     * @param string|null  $company        物流公司名称
     * @param array|string $gateways       需要使用的网关，如果不指定，则使用所有可用的网关
     *
     * @throws GatewayAvailableException
     * @throws InvalidArgumentException
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function query(string $logisticNumber, ?string $company = null, $gateways = []): array;

    /**
     * 获取物流公司信息.
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function getCompanyList(): array;

    /**
     * 设置物流公司信息.
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function setCompanyList(array $companyList): LogisticsInterface;

    /**
     * 获取默认的物流公司列表.
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function getDefaultCompanyList(): array;
}
