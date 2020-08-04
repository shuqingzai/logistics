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

use RuntimeException;
use Overbeck\Logistics\Exceptions\InvalidArgumentException;
use Overbeck\Logistics\Gateways\GatewayAbstract;
use Overbeck\Logistics\Interfaces\GatewayInterface;
use Overbeck\Logistics\Supports\Config;

/**
 * 物流网关管理.
 *
 * Class LogisticsGatewayManager
 *
 * @author ShuQingZai
 * DateTime 2020/8/1 18:41
 */
class LogisticsGatewayManager
{
    /**
     * 配置.
     *
     * @var Config
     */
    protected $config;

    /**
     * 默认网关.
     *
     * @var string
     */
    protected $defaultGateway;

    /**
     * 网关服务集合.
     *
     * @var array
     */
    protected $gateways = [];

    /**
     * 自定义网关集合.
     *
     * @var array
     */
    protected $customGateway = [];

    /**
     * 禁用的网关.
     *
     * @var array
     */
    protected $disableGateways = [];

    /**
     * @var Logistics
     */
    protected $logistics;

    public function __construct(array $config, Logistics $logistics)
    {
        $this->config    = new Config($config);
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
     * @param string|null $name
     *
     * @return GatewayInterface
     *
     * @throws InvalidArgumentException
     *
     * @author ShuQingZai<929024757@qq.com>
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
     * @return string
     *
     * @throws \RuntimeException 没有配置默认网关
     *
     * @author ShuQingZai<929024757@qq.com>
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
     * @return bool
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function hasDefaultGateway(): bool
    {
        return !empty($this->defaultGateway);
    }

    /**
     * 设置默认网关.
     *
     * @param string $defaultGateway
     *
     * @return Logistics
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function setDefaultGateway(string $defaultGateway): Logistics
    {
        $this->defaultGateway = $defaultGateway;

        return $this->logistics;
    }

    /**
     * 设置默认网关.
     *
     * @return Config
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * 获取禁用网关.
     *
     * @return array
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function getDisableGateways(): array
    {
        return $this->disableGateways;
    }

    /**
     * 设置禁用网关.
     *
     * @param array $disableGateways
     *
     * @return Logistics
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function setDisableGateways(array $disableGateways): Logistics
    {
        $this->disableGateways = $disableGateways;

        return $this->logistics;
    }

    /**
     * 注销网关服务实例.
     *
     * @param string|null $name
     *
     * @return Logistics
     *
     * @author ShuQingZai<929024757@qq.com>
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
     * @param string   $name
     * @param \Closure $closure
     *
     * @return Logistics
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function registerCustomGateway(string $name, \Closure $closure): Logistics
    {
        $this->customGateway[$name] = $closure;

        return $this->logistics;
    }

    /**
     * 创建网关服务
     *
     * @param string $name
     *
     * @return GatewayInterface
     *
     * @throws InvalidArgumentException
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    protected function makeGateway(string $name): GatewayInterface
    {
        $config = $this->config->get('gateways.' . $name, []);
        if (!isset($config['http'])) {
            $config['http'] = $this->config->get('http', []);
        }

        $config['http']['timeout']         = $config['http']['timeout'] ?: GatewayAbstract::DEFAULT_TIMEOUT;
        $config['http']['connect_timeout'] = $config['http']['connect_timeout'] ?: GatewayAbstract::DEFAULT_CONNECT_TIMEOUT;

        if (isset($this->customGateway[$name])) {
            $appInstance = $this->callCustomCreator($name, $config);
        } else {
            $className = $this->formatGatewayClassName($name);

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

        /* @var GatewayInterface $appInstance */
        return $appInstance;
    }

    /**
     * 创建网关服务
     *
     * @param string $gateway
     * @param array  $config
     *
     * @return GatewayInterface
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    protected function callCustomCreator(string $gateway, array $config = []): GatewayInterface
    {
        return \call_user_func($this->customGateway[$gateway], $config ?: $this->config->get('gateways.' . $gateway, []));
    }

    /**
     * 格式化网关类名称.
     *
     * @param string $name
     *
     * @return string
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    protected function formatGatewayClassName(string $name): string
    {
        if (\class_exists($name)) {
            return $name;
        }

        $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $name);

        return __NAMESPACE__ . '\\Gateways\\' . \ucfirst($name) . 'Gateway';
    }
}
