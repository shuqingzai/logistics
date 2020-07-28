<?php
/**
 * 工厂类
 *
 * FileName Factory.php
 * Created By PhpStorm.
 * Author ShuQingZai
 * DateTime 2020/7/28 15:44
 */
declare(strict_types=1);

namespace Sqz\Logistics;


use Wythe\Logistics\Exceptions\InvalidArgumentException;

class Factory
{
    /**
     * 驱动集合
     *
     * @var array $appInstance
     * DateTime 2020/7/28 16:47
     * @package Sqz\Logistics\Factory
     */
    protected $appInstance = [];

    public function make(string $name)
    {
        $className = $this->formatClassName($name);
        if (!class_exists($className)) {
            throw new InvalidArgumentException(sprintf('Class "%s" not exists.', $className));
        }
    }

    /**
     * 注销服务实例
     *
     * Author ShuQingZai
     * DateTime 2020/7/7 11:35
     *
     * @param string|null $name 指定服务标识注销
     * @return bool
     */
    public function unsetAppInstance(?string $name = null): bool
    {
        if (\is_null($name)) {
            $this->appInstance = [];
        }
        if (isset($this->appInstance[$name])) {
            unset($this->appInstance[$name]);
        }
        return true;
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
            return strtoupper($match[1]);
        }, $name);
        return __NAMESPACE__ . '\\Gateways\\' . ucfirst($name) . 'Gateway';
    }
}