<?php
declare(strict_types=1);


namespace Sqz\Logistics\Interfaces;

/**
 * 网关可用异常接口
 *
 * Interface GatewayAvailableInterface
 * Author ShuQingZai
 * DateTime 2020/7/31 16:09
 *
 * @package Sqz\Logistics\Interfaces
 */
interface GatewayAvailableInterface
{
    /**
     * 获取结果
     *
     * @return array
     */
    public function getResults(): array;

    /**
     * 获取单一网关异常
     *
     * @param string $gateway
     *
     * @return mixed|null
     */
    public function getException(string $gateway);

    /**
     * 获取所有网关异常
     *
     * @return array
     */
    public function getExceptions(): array;

    /**
     * 获取以后一个异常
     *
     * @return mixed
     */
    public function getLastException();
}