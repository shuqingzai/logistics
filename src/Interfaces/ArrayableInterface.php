<?php
declare(strict_types=1);


namespace Sqz\Logistics\Interfaces;

use ArrayAccess;


interface ArrayableInterface extends ArrayAccess
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray();
}
