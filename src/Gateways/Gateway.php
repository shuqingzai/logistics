<?php
/**
 * 网关服务基类
 *
 * FileName Gateway.php
 * Created By PhpStorm.
 * Author ShuQingZai
 * DateTime 2020/7/29 14:01
 */
declare(strict_types=1);

namespace Sqz\Logistics\Gateways;


use Sqz\Logistics\Interfaces\GatewayInterface;
use Sqz\Logistics\Supports\Config;
use Sqz\Logistics\Traits\HasHttpRequest;

abstract class Gateway implements GatewayInterface
{
    use HasHttpRequest;

    const DEFAULT_TIMEOUT = 5.0;
    const DEFAULT_CONNECT_TIMEOUT = 5.0;

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
     * @var array $httpConfig
     * DateTime 2020/7/29 15:20
     * @package Sqz\Logistics\Gateways\Gateway
     */
    protected $httpConfig;

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

    public function __construct(array $config)
    {
        $this->setConfig($config)
             ->setHttpConfig($this->config->get('http', []));
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
     * @return Gateway
     */
    public function setConfig($config)
    {
        $this->config = !($config instanceof Config) ? new Config($config) : $config;

        return $this;
    }

    /**
     * @return array
     */
    public function getHttpConfig(): array
    {
        return $this->httpConfig;
    }

    /**
     * @return array
     */
    public function getGuzzleOptions(): array
    {
        return $this->getHttpConfig();
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
     * @param array $httpConfig
     * @return Gateway
     */
    public function setHttpConfig(array $httpConfig)
    {
        $this->httpConfig = $httpConfig;

        return $this;
    }

    public function getGatewayName(): string
    {
        return \strtolower(\str_replace([__NAMESPACE__ . '\\', 'Gateway'], '', \get_class($this)));
    }


}