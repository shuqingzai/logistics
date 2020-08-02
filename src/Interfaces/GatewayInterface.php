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
 * 网关接口.
 *
 * Interface GatewayInterface
 *
 * @author ShuQingZai
 * DateTime 2020/7/31 16:09
 */
interface GatewayInterface
{
    /**
     * 获取服务标识名称.
     *
     * @return string
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function getGatewayName(): string;

    /**
     * 查询物流信息.
     *
     * @param string      $logisticNumber 物流单号
     * @param string|null $company        物流公司名称
     *
     * @return array
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function query(string $logisticNumber, ?string $company = null): array;

    /**
     * 设置物流公司信息.
     *
     * @param array $companyList
     *
     * @return GatewayInterface
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function setCompanyList(array $companyList): GatewayInterface;

    /**
     * 获取物流公司信息.
     *
     * @return array
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function getCompanyList(): array;
}
