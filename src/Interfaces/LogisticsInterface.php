<?php
declare(strict_types=1);


namespace Overbeck\Logistics\Interfaces;

use Overbeck\Logistics\Exceptions\GatewayAvailableException;
use Overbeck\Logistics\Exceptions\GatewayErrorException;
use Overbeck\Logistics\Logistics;


/**
 * 物流接口
 *
 * Interface LogisticsInterface
 * Author ShuQingZai
 * DateTime 2020/7/31 16:09
 *
 * @package Overbeck\Logistics\Interfaces
 */
interface LogisticsInterface
{
    /**
     * 查询物流
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 15:12
     *
     * @param string       $logisticNumber 物流单号
     * @param string|null  $company        物流公司名称
     * @param array|string $gateways       需要使用的网关，如果不指定，则使用所有可用的网关
     * @return array
     * @throws GatewayErrorException
     * @throws GatewayAvailableException
     */
    public function query(string $logisticNumber, ?string $company = null, $gateways = []): array;

    /**
     * 获取物流公司信息
     *
     * Author ShuQingZai
     * DateTime 2020/7/31 16:18
     *
     * @return array
     */
    public function getCompanyList(): array;

    /**
     * 设置物流公司信息
     *
     * Author ShuQingZai
     * DateTime 2020/7/31 16:18
     *
     * @param array $companyList
     * @return LogisticsInterface
     */
    public function setCompanyList(array $companyList): LogisticsInterface;

    /**
     * 获取默认的物流公司列表
     *
     * Author ShuQingZai
     * DateTime 2020/8/1 18:12
     *
     * @return array
     */
    public function getDefaultCompanyList(): array;
}