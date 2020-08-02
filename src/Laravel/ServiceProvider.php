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

namespace Overbeck\Logistics\Laravel;

use Illuminate\Foundation\Application as LaravelApplication;
use Overbeck\Logistics\Logistics;

/**
 * 集成于Laravel.
 *
 * Class ServiceProvider
 * @author ShuQingZai
 * DateTime 2020/8/2 13:17
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * 标记着提供器是延迟加载的.
     *
     * @var bool
     */
    protected $defer = true;

    public function boot()
    {
        $source = \realpath(__DIR__.'/../config/config.php');

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('logistics.php')], 'logistics');
        }

        $this->mergeConfigFrom($source, 'logistics');
    }

    /**
     * 注册服务提供者.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Logistics::class, function () {
            return new Logistics(config('logistics'));
        });

        $this->app->alias(Logistics::class, 'logistics');
    }

    /**
     * 取得提供者提供的服务
     *
     * @return array
     */
    public function provides()
    {
        return [Logistics::class, 'logistics'];
    }
}
