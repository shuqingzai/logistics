<?php
declare(strict_types=1);


namespace Sqz\Logistics\Gateways;


use Psr\Http\Message\ResponseInterface;
use Sqz\Logistics\Exceptions\GatewayAvailableException;
use Sqz\Logistics\Exceptions\InvalidArgumentException;
use Sqz\Logistics\Interfaces\GatewayInterface;
use Sqz\Logistics\Supports\Config;
use Sqz\Logistics\Traits\HasHttpRequest;


/**
 * 网关基类
 *
 * Class GatewayAbstract
 * Author ShuQingZai
 * DateTime 2020/7/31 16:08
 *
 * @package Sqz\Logistics\Gateways
 */
abstract class GatewayAbstract implements GatewayInterface
{
    use HasHttpRequest;

    const DEFAULT_TIMEOUT = 5.0;

    const DEFAULT_CONNECT_TIMEOUT = 5.0;

    const LOGISTICS_ERROR = 99;

    const LOGISTICS_TAKING = 1;

    const LOGISTICS_IN_TRANSIT = 2;

    const LOGISTICS_DELIVERING = 3;

    const LOGISTICS_SIGNED = 4;

    const LOGISTICS_PROBLEM = 5;

    const LOGISTICS_RETURN_RECEIPT = 6;

    const LOGISTICS_REJECTED = 7;

    const LOGISTICS_SEND_RETURN = 8;

    const LOGISTICS_TIMEOUT = 9;

    const LOGISTICS_DELIVERY_FAILED = 10;

    const LOGISTICS_DESCRIPTION = [
        self::LOGISTICS_ERROR           => '快递查询异常',
        self::LOGISTICS_TAKING          => '快递收件(揽件)',
        self::LOGISTICS_IN_TRANSIT      => '运输中',
        self::LOGISTICS_DELIVERING      => '派件中',
        self::LOGISTICS_SIGNED          => '已签收',
        self::LOGISTICS_PROBLEM         => '疑难件',
        self::LOGISTICS_RETURN_RECEIPT  => '退件签收',
        self::LOGISTICS_REJECTED        => '拒签',
        self::LOGISTICS_SEND_RETURN     => '退回',
        self::LOGISTICS_TIMEOUT         => '超时件',
        self::LOGISTICS_DELIVERY_FAILED => '派送失败',
    ];

    /**
     * 配置
     *
     * @var Config $config
     * DateTime 2020/7/29 15:17
     * @package Sqz\Logistics\Gateways\Gateway
     */
    protected $config;

    /**
     * guzzleHttp配置信息
     *
     * @var array $httpOptions
     * DateTime 2020/7/29 15:20
     * @package Sqz\Logistics\Gateways\Gateway
     */
    protected $httpOptions;

    /**
     * 请求超时时间
     *
     * @var  $timeout
     * DateTime 2020/7/29 15:49
     * @package Sqz\Logistics\Gateways\Gateway
     */
    protected $timeout;

    /**
     * 响应超时时间
     *
     * @var  $connectTimeout
     * DateTime 2020/7/29 15:53
     * @package Sqz\Logistics\Gateways\Gateway
     */
    protected $connectTimeout;

    /**
     * 物流公司名称
     *
     * @var string $companyName
     * DateTime 2020/7/30 15:27
     * @package Sqz\Logistics\Gateways\GatewayAbstract
     */
    protected $companyName = '';

    /**
     * 物流公司列表
     *
     * @var array $companyList
     * DateTime 2020/7/30 17:11
     * @package Sqz\Logistics\Gateways\GatewayAbstract
     */
    protected $companyList = [];


    /**
     * 统一格式化物流状态code
     *
     * Author ShuQingZai
     * DateTime 2020/7/30 11:28
     *
     * @param int|string $originalStatus 原始返回的状态
     * @return int
     */
    abstract protected function formatStatus($originalStatus): int;

    /**
     * 格式化响应数据
     *
     * Author ShuQingZai
     * DateTime 2020/7/30 14:22
     *
     * @param ResponseInterface|array|string $response 原始响应数据
     * @return array
     * @throws GatewayAvailableException
     */
    abstract protected function formatData($response): array;


    public function __construct(array $config)
    {
        $this->setConfig($config)
             ->setHttpOptions($this->config->get('http', []));
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param array|Config $config
     * @return GatewayAbstract
     */
    public function setConfig($config)
    {
        $this->config = !($config instanceof Config) ? new Config($config) : $config;

        return $this;
    }

    /**
     * @return array
     */
    public function getHttpOptions(): array
    {
        return $this->httpOptions;
    }

    /**
     * @return array
     */
    public function getGuzzleOptions(): array
    {
        return $this->getHttpOptions();
    }

    /**
     * 获取请求超时时间
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 16:05
     *
     * @return float
     */
    public function getTimeout()
    {
        return $this->timeout ?: $this->config->get('timeout', self::DEFAULT_TIMEOUT);
    }

    /**
     * 设置请求超时时间
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 16:05
     *
     * @param float $timeout
     * @return $this
     */
    public function setTimeout(float $timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * 获取响应超时时间
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 16:06
     *
     * @return float
     */
    public function getConnectTimeout()
    {
        return $this->connectTimeout ?: $this->config->get('connect_timeout', self::DEFAULT_CONNECT_TIMEOUT);
    }

    /**
     * 设置响应超时时间
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 16:03
     *
     * @param float $connectTimeout
     * @return $this
     */
    public function setConnectTimeout(float $connectTimeout)
    {
        $this->connectTimeout = $connectTimeout;

        return $this;
    }


    /**
     * @param array $httpOptions
     * @return GatewayAbstract
     */
    public function setHttpOptions(array $httpOptions)
    {
        $this->httpOptions = $httpOptions;

        return $this;
    }

    public function getGatewayName(): string
    {
        return \strtolower(\str_replace([__NAMESPACE__ . '\\', 'Gateway'], '', \get_class($this)));
    }

    /**
     * 获取物流公司信息
     *
     * Author ShuQingZai
     * DateTime 2020/7/31 17:29
     *
     * @return array
     */
    public function getCompanyList(): array
    {
        return $this->companyList;
    }

    /**
     * 设置物流公司信息
     *
     * Author ShuQingZai
     * DateTime 2020/7/31 17:26
     *
     * @param array $companyList
     * @return GatewayInterface
     */
    public function setCompanyList(array $companyList): GatewayInterface
    {
        $this->companyList = $companyList;

        return $this;
    }


    /**
     * 获取物流状态描述名称
     *
     * Author ShuQingZai
     * DateTime 2020/7/30 11:30
     *
     * @param int $status
     * @return string
     */
    protected function getStatusName(int $status): string
    {
        return self::LOGISTICS_DESCRIPTION[$status];
    }

    /**
     * 根据快递公司名称从配置文件中获取code
     *
     * Author ShuQingZai
     * DateTime 2020/7/30 8:40
     *
     * @param string $company
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getCompanyCodeByCompanyList(string $company): string
    {
        $index = \array_search($company, \array_column($this->companyList, 'name'));
        if (false !== $index) {
            $this->companyName = $this->companyList[$index]['name'];
            return $this->companyList[$index]['code'][$this->getGatewayName()];
        }

        throw new InvalidArgumentException('Error obtaining courier code');
    }

}