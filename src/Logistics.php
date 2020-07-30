<?php
/**
 * 物流API
 *
 * FileName Logistics.php
 * Created By PhpStorm.
 * Author ShuQingZai
 * DateTime 2020/7/29 11:14
 */
declare(strict_types=1);

namespace Sqz\Logistics;


use Sqz\Logistics\Exceptions\GatewayErrorException;
use Sqz\Logistics\Exceptions\GatewayAvailableException;
use Sqz\Logistics\Supports\Collection;

class Logistics
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';

    /**
     * 物流网关管理
     *
     * @var LogisticsGatewayManager $logisticsGatewayManager
     * DateTime 2020/7/29 11:29
     * @package Sqz\Logistics\Logistics
     */
    protected $logisticsGatewayManager;

    /**
     * Logistics constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->logisticsGatewayManager = new LogisticsGatewayManager($config);
    }


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
    public function query(string $trackingNumber, ?string $company = null, $gateways = []): array
    {
        if (\is_string($gateways) && !empty($gateways)) {
            $gateways = \explode(',', $gateways);
        }

        $gatewaysConfig = $this->logisticsGatewayManager->getGateways();
        $results        = [];
        $errResults     = 0;
        foreach ($gatewaysConfig as $gateway => $config) {

            if (!empty($gateways) && !\in_array($gateway, $gateways)) {
                throw new GatewayErrorException('The gateway "' . $gateway . '" is unavailable');
            }

            if ($this->logisticsGatewayManager->hasDefaultGateway() && $gateway !== $this->logisticsGatewayManager->getDefaultGateway()) {
                continue;
            }

            try {
                $results[$gateway] = new Collection([
                                                        'gateway' => $gateway,
                                                        'status'  => self::STATUS_SUCCESS,
                                                        'result'  => $this->logisticsGatewayManager->gateway($gateway)->query($trackingNumber, $company),
                                                    ]);
            } catch (\Throwable $e) {
                $results[$gateway] = new Collection([
                                                        'gateway'   => $gateway,
                                                        'status'    => self::STATUS_FAILURE,
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


}