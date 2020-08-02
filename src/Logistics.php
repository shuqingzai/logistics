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

namespace Overbeck\Logistics;

use Overbeck\Logistics\Exceptions\GatewayAvailableException;
use Overbeck\Logistics\Exceptions\InvalidArgumentException;
use Overbeck\Logistics\Interfaces\LogisticsInterface;
use Overbeck\Logistics\Supports\Collection;
use Overbeck\Logistics\Supports\ParseContentToArray;

/**
 * 物流API.
 *
 * Class Logistics
 *
 * @author ShuQingZai
 * DateTime 2020/7/31 17:37
 *
 * @mixin  LogisticsGatewayManager
 */
class Logistics implements LogisticsInterface
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';

    /**
     * 物流网关管理.
     *
     * @var LogisticsGatewayManager
     */
    protected $logisticsGatewayManager;

    /**
     * 物流公司列表.
     *
     * @var array
     */
    protected $companyList = [];

    /**
     * Logistics constructor.
     *
     * @throws Exceptions\InvalidArgumentException
     */
    public function __construct(array $config)
    {
        $this->logisticsGatewayManager = new LogisticsGatewayManager($config, $this);
        $this->companyList = $this->initCompanyFiles();
    }

    /**
     * 查询物流
     *
     * @author ShuQingZai
     *
     * @param string       $logisticNumber 物流单号
     * @param string|null  $company        物流公司名称
     * @param array|string $gateways       需要使用的网关，如果不指定，则使用所有可用的网关
     *
     * @throws GatewayAvailableException
     * @throws InvalidArgumentException
     */
    public function query(string $logisticNumber, ?string $company = null, $gateways = []): array
    {
        if (\is_string($gateways) && !empty($gateways)) {
            $gateways = \explode(',', $gateways);
        }

        $gatewaysConfig = $this->logisticsGatewayManager->getGateways();
        $results = [];
        $errResults = 0;
        foreach ($gatewaysConfig as $gateway => $config) {
            if (!empty($gateways) && !\in_array($gateway, $gateways)) {
                throw new InvalidArgumentException('The gateway "'.$gateway.'" is unavailable');
            }

            if ($this->logisticsGatewayManager->hasDefaultGateway() && $gateway !== $this->logisticsGatewayManager->getDefaultGateway()) {
                continue;
            }

            if (\in_array($gateway, $this->logisticsGatewayManager->getDisableGateways())) {
                continue;
            }

            try {
                $results[$gateway] = new Collection([
                                                        'gateway' => $gateway,
                                                        'status' => self::STATUS_SUCCESS,
                                                        'result' => $this->logisticsGatewayManager->gateway($gateway)
                                                                                                   ->setCompanyList($this->getCompanyList())
                                                                                                   ->query($logisticNumber, $company),
                                                    ]);
            } catch (\Throwable $e) {
                $results[$gateway] = new Collection([
                                                        'gateway' => $gateway,
                                                        'status' => self::STATUS_FAILURE,
                                                        'exception' => $e,
                                                    ]);
                ++$errResults;
            }
        }

        if (empty($results) || \count($results) === $errResults) {
            throw new GatewayAvailableException($results);
        }

        return $results;
    }

    /**
     * 获取物流公司信息.
     *
     * @author ShuQingZai
     */
    public function getCompanyList(): array
    {
        empty($this->companyList) && $this->companyList = $this->getDefaultCompanyList();

        return $this->companyList;
    }

    /**
     * 设置物流公司信息.
     *
     * @author ShuQingZai
     */
    public function setCompanyList(array $companyList): LogisticsInterface
    {
        $this->companyList = \array_values(\array_column(\array_merge($this->getCompanyList(), $companyList), null, 'name'));

        return $this;
    }

    /**
     * 获取默认的物流公司列表.
     *
     * @author ShuQingZai
     */
    public function getDefaultCompanyList(): array
    {
        return include __DIR__.'/config/company.php';
    }

    /**
     * 初始化配置文件的物流公司列表.
     *
     * @author ShuQingZai
     *
     * @return array
     *
     * @throws Exceptions\InvalidArgumentException
     */
    protected function initCompanyFiles()
    {
        $companyFiles = $this->getConfig()->get('company_file', []);
        $companyFiles = \is_array($companyFiles) ? $companyFiles : \explode(',', $companyFiles);
        $companyFilesList = [];
        foreach ($companyFiles as $file) {
            if (\is_file((string) $file)) {
                $type = \pathinfo($file, PATHINFO_EXTENSION);
                $fileArr = ParseContentToArray::parseContent($file, $type);
                $companyFilesList = \array_merge($companyFilesList, $fileArr);
            }
        }

        return \array_values($companyFilesList ?
                                 \array_column(\array_merge($this->getCompanyList(), $companyFilesList), null, 'name')
                                 : $this->getCompanyList());
    }

    /**
     * 魔术方法.
     *
     * @author ShuQingZai
     *
     * @param mixed $avg
     *
     * @return mixed
     */
    public function __call(string $name, $avg)
    {
        return call_user_func_array([$this->logisticsGatewayManager, $name], $avg);
    }
}
