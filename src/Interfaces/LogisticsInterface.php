<?php
declare(strict_types=1);


namespace Sqz\Logistics\Interfaces;

use Sqz\Logistics\Exceptions\GatewayAvailableException;
use Sqz\Logistics\Exceptions\GatewayErrorException;
use Sqz\Logistics\Logistics;


/**
 * 物流接口
 *
 * Interface LogisticsInterface
 * Author ShuQingZai
 * DateTime 2020/7/31 16:09
 *
 * @package Sqz\Logistics\Interfaces
 */
interface LogisticsInterface
{
    /**
     * 查询物流
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 15:12
     *
     * @param string      $trackingNumber 物流单号
     * @param string|null $company        物流公司代号
     * @param array       $gateways       需要使用的网关，如果不指定，则使用所有可用的网关
     * @return array
     * @throws GatewayErrorException
     * @throws GatewayAvailableException
     */
    public function query(string $trackingNumber, ?string $company = null, $gateways = []): array;

    /**
     * 获取物流公司信息
     *
     * Author ShuQingZai
     * DateTime 2020/7/31 16:18
     *
     * @param array $companyList
     * @return LogisticsInterface
     */
    public function setCompanyList(array $companyList): LogisticsInterface;

    /**
     * 设置物流公司信息
     *
     * Author ShuQingZai
     * DateTime 2020/7/31 16:18
     *
     * @return array
     */
    public function getCompanyList(): array;
}