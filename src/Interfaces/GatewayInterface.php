<?php
declare(strict_types=1);


namespace Sqz\Logistics\Interfaces;


use Sqz\Logistics\Gateways\GatewayAbstract;

/**
 * 网关接口
 *
 * Interface GatewayInterface
 * Author ShuQingZai
 * DateTime 2020/7/31 16:09
 *
 * @package Sqz\Logistics\Interfaces
 */
interface GatewayInterface
{
    /**
     * 获取服务标识名称
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 17:54
     *
     * @return string
     */
    public function getGatewayName(): string;

    /**
     * 查询物流信息
     *
     * Author ShuQingZai
     * DateTime 2020/7/31 17:55
     *
     * @param string      $trackingNumber 快递单号
     * @param string|null $company        快递公司名称
     * @return array
     */
    public function query(string $trackingNumber, ?string $company = null): array;

    /**
     * 设置物流公司信息
     *
     * Author ShuQingZai
     * DateTime 2020/7/31 17:26
     *
     * @param array $companyList
     * @return GatewayInterface
     */
    public function setCompanyList(array $companyList): GatewayInterface;

    /**
     * 获取物流公司信息
     *
     * Author ShuQingZai
     * DateTime 2020/7/31 17:28
     *
     * @return array
     */
    public function getCompanyList(): array;
}