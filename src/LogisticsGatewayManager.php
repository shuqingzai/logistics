<?php

declare(strict_types=1);

/*
 * This file is part of the overbeck/logistics.
 *
 * (c) overbeck<i@overbeck.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overbeck\Logistics;

use RuntimeException;
use Overbeck\Logistics\Exceptions\InvalidArgumentException;
use Overbeck\Logistics\Gateways\GatewayAbstract;
use Overbeck\Logistics\Interfaces\GatewayInterface;
use Overbeck\Logistics\Supports\Config;

/**
 * 物流网关管理.
 *
 * Class LogisticsGatewayManager
 * Author ShuQingZai
 * DateTime 2020/8/1 18:41
 */
class LogisticsGatewayManager
{
    /**
     * 配置.
     *
     * @var Config
     *             DateTime 2020/7/29 9:41
     */
    protected $config;

    /**
     * 默认网关.
     *
     * @var string
     *             DateTime 2020/7/29 9:57
     */
    protected $defaultGateway;

    /**
     * 网关服务集合.
     *
     * @var array
     *            DateTime 2020/7/28 16:47
     */
    protected $gateways = [];

    /**
     * 自定义网关集合.
     *
     * @var array
     *            DateTime 2020/7/29 14:11
     */
    protected $customGateway = [];

    /**
     * 禁用的网关.
     *
     * @var array
     *            DateTime 2020/7/31 10:01
     */
    protected $disableGateways = [];

    /**
     * @var Logistics
     *                DateTime 2020/8/1 19:12
     */
    protected $logistics;

    public function __construct(array $config, Logistics $logistics)
    {
        $this->config = new Config($config);
        $this->logistics = $logistics;
        $this->config->has('default') && $this->setDefaultGateway($this->config->get('default'));
        $this->config->has('disable') && $this->setDisableGateways($this->config->get('disable'));
    }

    public function getGateways(): array
    {
        empty($this->gateways) && $this->config->has('gateways') && $this->gateways = $this->config->get('gateways');

        return $this->gateways;
    }

    /**
     * 获取网关实例.
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 11:32
     *
     * @throws InvalidArgumentException
     */
    public function gateway(?string $name = null): GatewayInterface
    {
        $name = $name ?: $this->getDefaultGateway();

        if (!isset($this->gateways[$name]) || !($this->gateways[$name] instanceof GatewayInterface)) {
            $this->gateways[$name] = $this->makeGateway($name);
        }

        return $this->gateways[$name];
    }

    /**
     * 获取默认网关.
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 10:59
     *
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
     * 是否设置默认网关.
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 13:46
     */
    public function hasDefaultGateway(): bool
    {
        return !empty($this->defaultGateway);
    }

    /**
     * 设置默认网关.
     *
     * Author ShuQingZai
     * DateTime 2020/7/30 8:53
     */
    public function setDefaultGateway(string $defaultGateway): Logistics
    {
        $this->defaultGateway = $defaultGateway;

        return $this->logistics;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getDisableGateways(): array
    {
        return $this->disableGateways;
    }

    public function setDisableGateways(array $disableGateways): Logistics
    {
        $this->disableGateways = $disableGateways;

        return $this->logistics;
    }

    /**
     * 注销网关服务实例.
     *
     * Author ShuQingZai
     * DateTime 2020/7/7 11:35
     *
     * @param string|null $name 指定服务标识注销
     */
    public function unregisterAppInstance(?string $name = null): Logistics
    {
        if (\is_null($name)) {
            $this->gateways = [];
        } elseif (isset($this->gateways[$name])) {
            unset($this->gateways[$name]);
        }

        return $this->logistics;
    }

    /**
     * 注册自定义网关.
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 14:13
     */
    public function registerCustomGateway(string $name, \Closure $closure): Logistics
    {
        $this->customGateway[$name] = $closure;

        return $this->logistics;
    }

    /**
     * 创建网关服务
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 9:12
     *
     * @throws InvalidArgumentException
     */
    protected function makeGateway(string $name): GatewayInterface
    {
        $config = $this->config->get('gateways.'.$name, []);
        if (!isset($config['http'])) {
            $config['http'] = $this->config->get('http', []);
        }

        $config['http']['timeout'] = $config['http']['timeout'] ?: GatewayAbstract::DEFAULT_TIMEOUT;
        $config['http']['connect_timeout'] = $config['http']['connect_timeout'] ?: GatewayAbstract::DEFAULT_CONNECT_TIMEOUT;

        if (isset($this->customGateway[$name])) {
            $appInstance = $this->callCustomCreator($name, $config);
        } else {
            $className = $this->formatGatewayClassName($name);

            try {
                $app = new \ReflectionClass($className);
                $appInstance = $app->newInstance($config);
            } catch (\ReflectionException $e) {
                throw new InvalidArgumentException($e->getMessage());
            }
        }

        if (!($appInstance instanceof GatewayInterface)) {
            throw new InvalidArgumentException(sprintf('Gateway "%s" must implement interface %s.', $name, GatewayInterface::class));
        }

        /* @var GatewayInterface $appInstance */
        return $appInstance;
    }

    /**
     * 创建网关服务
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 14:17
     */
    protected function callCustomCreator(string $gateway, array $config = []): GatewayInterface
    {
        return \call_user_func($this->customGateway[$gateway], $config ?: $this->config->get('gateways.'.$gateway, []));
    }

    /**
     * 格式化网关类名称.
     *
     * Author ShuQingZai
     * DateTime 2020/7/30 8:47
     */
    protected function formatGatewayClassName(string $name): string
    {
        if (\class_exists($name)) {
            return $name;
        }

        $name = \ucfirst(\str_replace(['-', '_', ''], '', $name));

        return __NAMESPACE__.'\\Gateways\\'.$name.'Gateway';
    }
}
