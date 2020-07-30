<?php
/**
 * 网关接口
 *
 * FileName GatewayInterface.php
 * Created By PhpStorm.
 * Author ShuQingZai
 * DateTime 2020/7/29 9:53
 */
declare(strict_types=1);

namespace Sqz\Logistics\Interfaces;



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

    public function query(string $trackingNumber, ?string $company = null);
}