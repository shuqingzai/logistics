<?php
declare(strict_types=1);


namespace Sqz\Logistics;


use Sqz\Logistics\Exceptions\GatewayErrorException;
use Sqz\Logistics\Exceptions\GatewayAvailableException;
use Sqz\Logistics\Exceptions\InvalidArgumentException;
use Sqz\Logistics\Interfaces\LogisticsInterface;
use Sqz\Logistics\Supports\Collection;
use Sqz\Logistics\Supports\ParseContentToArray;

/**
 * 物流API
 *
 *
 * Class Logistics
 * Author ShuQingZai
 * DateTime 2020/7/31 17:37
 *
 * @mixin  LogisticsGatewayManager
 * @package Sqz\Logistics
 */
class Logistics implements LogisticsInterface
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
     * 物流公司列表
     *
     * @var array $companyList
     * DateTime 2020/7/31 16:11
     * @package Sqz\Logistics\Logistics
     */
    protected $companyList = [];

    /**
     * Logistics constructor.
     *
     * @param array $config
     * @throws Exceptions\InvalidArgumentException
     */
    public function __construct(array $config)
    {
        $this->logisticsGatewayManager = new LogisticsGatewayManager($config, $this);
        $this->companyList             = $this->initCompanyFiles();
    }


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
     * @throws GatewayAvailableException
     * @throws InvalidArgumentException
     */
    public function query(string $logisticNumber, ?string $company = null, $gateways = []): array
    {
        if (\is_string($gateways) && !empty($gateways)) {
            $gateways = \explode(',', $gateways);
        }

        $gatewaysConfig = $this->logisticsGatewayManager->getGateways();
        $results        = [];
        $errResults     = 0;
        foreach ($gatewaysConfig as $gateway => $config) {

            if (!empty($gateways) && !\in_array($gateway, $gateways)) {
                throw new InvalidArgumentException('The gateway "' . $gateway . '" is unavailable');
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
                                                        'status'  => self::STATUS_SUCCESS,
                                                        'result'  => $this->logisticsGatewayManager->gateway($gateway)
                                                                                                   ->setCompanyList($this->getCompanyList())
                                                                                                   ->query($logisticNumber, $company),
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

    /**
     * 获取物流公司信息
     *
     * Author ShuQingZai
     * DateTime 2020/7/31 16:18
     *
     * @return array
     */
    public function getCompanyList(): array
    {
        empty($this->companyList) && $this->companyList = $this->getDefaultCompanyList();

        return $this->companyList;
    }

    /**
     * 设置物流公司信息
     *
     * Author ShuQingZai
     * DateTime 2020/7/31 16:18
     *
     * @param array $companyList
     * @return LogisticsInterface
     */
    public function setCompanyList(array $companyList): LogisticsInterface
    {
        $this->companyList = \array_values(\array_column(\array_merge($this->getCompanyList(), $companyList), null, 'name'));

        return $this;
    }

    /**
     * 获取默认的物流公司列表
     *
     * Author ShuQingZai
     * DateTime 2020/8/1 18:12
     *
     * @return array
     */
    public function getDefaultCompanyList(): array
    {
        return include __DIR__ . '/config/company.php';
    }

    /**
     * 初始化配置文件的物流公司列表
     *
     * Author ShuQingZai
     * DateTime 2020/8/1 18:15
     *
     * @return array
     * @throws Exceptions\InvalidArgumentException
     */
    protected function initCompanyFiles()
    {
        $companyFiles     = $this->getConfig()->get('company_file', []);
        $companyFiles     = \is_array($companyFiles) ? $companyFiles : \explode(',', $companyFiles);
        $companyFilesList = [];
        foreach ($companyFiles as $file) {
            if (\is_file((string)$file)) {
                $type             = \pathinfo($file, PATHINFO_EXTENSION);
                $fileArr          = ParseContentToArray::parseContent($file, $type);
                $companyFilesList = \array_merge($companyFilesList, $fileArr);
            }
        }

        return \array_values($companyFilesList ?
                                 \array_column(\array_merge($this->getCompanyList(), $companyFilesList), null, 'name')
                                 : $this->getCompanyList());
    }

    /**
     * 魔术方法
     *
     * Author ShuQingZai
     * DateTime 2020/7/31 17:32
     *
     * @param string $name
     * @param mixed  $avg
     * @return mixed
     */
    public function __call(string $name, $avg)
    {
        return call_user_func_array([$this->logisticsGatewayManager, $name], $avg);
    }
}