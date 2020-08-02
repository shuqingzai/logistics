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

namespace Overbeck\Logistics\Laravel;

use Illuminate\Support\Facades\Facade;

/**
 * Laravel 门面.
 *
 * Class Facade
 *
 * @author ShuQingZai
 * DateTime 2020/8/2 14:01
 *
 * @mixin \Overbeck\Logistics\Logistics
 *
 * @see \Overbeck\Logistics\Logistics
 */
class Logistics extends Facade
{
    /**
     * 获取门面标识.
     *
     * @author ShuQingZai
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'logistics';
    }
}
