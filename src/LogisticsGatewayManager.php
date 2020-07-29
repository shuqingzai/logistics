<?php
/**
 * 物流网关管理者
 *
 * FileName Logistics.php
 * Created By PhpStorm.
 * Author ShuQingZai
 * DateTime 2020/7/28 15:47
 */
declare(strict_types=1);

namespace Sqz\Logistics;


use RuntimeException;
use Sqz\Logistics\Exceptions\InvalidArgumentException;
use Sqz\Logistics\Gateways\Gateway;
use Sqz\Logistics\Interfaces\GatewayInterface;
use Sqz\Logistics\Supports\Config;

class LogisticsGatewayManager
{

    /**
     * 配置
     *
     * @var Config $config
     * DateTime 2020/7/29 9:41
     * @package Sqz\Logistics\Logistics
     */
    protected $config;

    /**
     * 默认网关
     *
     * @var string $defaultGateway
     * DateTime 2020/7/29 9:57
     * @package Sqz\Logistics\Logistics
     */
    protected $defaultGateway;

    /**
     * 网关服务集合
     *
     * @var array $gateways
     * DateTime 2020/7/28 16:47
     * @package Sqz\Logistics\Factory
     */
    protected $gateways = [];

    /**
     * 自定义网关集合
     *
     * @var array $customGateway
     * DateTime 2020/7/29 14:11
     * @package Sqz\Logistics\LogisticsGatewayManager
     */
    protected $customGateway = [];

    public function __construct(array $config)
    {
        $this->config = new Config($config);
        empty($config['default']) || $this->setDefaultGateway($this->config->get('default'));
    }

    /**
     * @return array
     */
    public function getGateways(): array
    {
        empty($this->gateways) && $this->config->has('gateways') && $this->gateways = $this->config->get('gateways');
        return $this->gateways;
    }

    /**
     * 获取网关实例
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 11:32
     *
     * @param string|null $name
     * @return GatewayInterface
     * @throws InvalidArgumentException
     */
    public function gateway(?string $name = null): GatewayInterface
    {
        $name = $name ?: $this->getDefaultGateway();

        if (!isset($this->gateways[$name])) {
            $this->gateways[$name] = $this->makeGateway($name);
        }

        return $this->gateways[$name];
    }

    /**
     * 获取默认网关
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 10:59
     *
     * @return string
     * @throws \RuntimeException 没有配置默认网关
     */
    public function getDefaultGateway(): string
    {
        if (!$this->hasDefaultGateway()) {
            throw new RuntimeException('No default gateway configured.');
        }
        return $this->defaultGateway;
    }

    /**
     * 是否设置默认网关
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 13:46
     *
     * @return bool
     */
    public function hasDefaultGateway(): bool
    {
        return !empty($this->defaultGateway);
    }

    /**
     * @param string $defaultGateway
     */
    public function setDefaultGateway(string $defaultGateway): void
    {
        $this->defaultGateway = $defaultGateway;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * 注销网关服务实例
     *
     * Author ShuQingZai
     * DateTime 2020/7/7 11:35
     *
     * @param string|null $name 指定服务标识注销
     * @return LogisticsGatewayManager
     */
    public function unregisterAppInstance(?string $name = null): LogisticsGatewayManager
    {
        if (\is_null($name)) {
            $this->gateways = [];
        }
        elseif (isset($this->gateways[$name])) {
            unset($this->gateways[$name]);
        }
        return $this;
    }

    /**
     * 注册自定义网关
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 14:13
     *
     * @param string   $name
     * @param \Closure $closure
     * @return LogisticsGatewayManager
     */
    public function registerCustomGateway(string $name, \Closure $closure): LogisticsGatewayManager
    {
        $this->customGateway[$name] = $closure;
        return $this;
    }

    /**
     * 创建网关服务
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 9:12
     *
     * @param string $name
     * @return GatewayInterface
     * @throws InvalidArgumentException
     */
    protected function makeGateway(string $name): GatewayInterface
    {
        $config = $this->config->get("gateways.{$name}", []);
        if (!isset($config['http'])) {
            $config['http'] = $this->config->get('http', []);
        }

        $config['http']['timeout']         = $config['http']['timeout'] ?: Gateway::DEFAULT_TIMEOUT;
        $config['http']['connect_timeout'] = $config['http']['timeout'] ?: Gateway::DEFAULT_CONNECT_TIMEOUT;

        if (isset($this->customGateway[$name])) {
            $appInstance = $this->callCustomCreator($name, $config);
        }
        else {
            $className = $this->formatClassName($name);

            try {
                $app         = new \ReflectionClass($className);
                $appInstance = $app->newInstance($config);
            } catch (\ReflectionException $e) {
                throw new InvalidArgumentException($e->getMessage());
            }
        }

        if (!($appInstance instanceof GatewayInterface)) {
            throw new InvalidArgumentException(sprintf('Gateway "%s" must implement interface %s.', $name, GatewayInterface::class));
        }

        return $appInstance;
    }

    /**
     * 创建网关服务
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 14:17
     *
     * @param string $gateway
     * @param array  $config
     * @return GatewayInterface
     */
    protected function callCustomCreator(string $gateway, array $config = []): GatewayInterface
    {
        return \call_user_func($this->customGateway[$gateway], $config ?: $this->config->get("gateways.{$gateway}", []));
    }

    /**
     * 格式化服务提供者命名空间 网关
     *
     * Author ShuQingZai
     * DateTime 2020/7/7 9:16
     *
     * @param string $name
     * @return string
     */
    protected function formatClassName(string $name): string
    {
        if (class_exists($name)) {
            return $name;
        }
        $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return \strtoupper($match[1]);
        }, $name);
        return __NAMESPACE__ . '\\Gateways\\' . \ucfirst($name) . 'Gateway';
    }
}