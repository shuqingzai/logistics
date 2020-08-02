<?php
declare(strict_types=1);

namespace Overbeck\Logistics\Laravel;


use Illuminate\Support\Facades\Facade;

/**
 * Laravel 门面
 *
 * Class Facade
 * Author ShuQingZai
 * DateTime 2020/8/2 14:01
 *
 * @mixin \Overbeck\Logistics\Logistics
 * @see \Overbeck\Logistics\Logistics
 * @package Overbeck\Logistics
 */
class Logistics extends Facade
{
    /**
     * 获取门面标识
     *
     * Author ShuQingZai
     * DateTime 2020/8/2 14:02
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'logistics';
    }
}
