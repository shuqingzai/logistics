<?php
declare(strict_types=1);


namespace Overbeck\Logistics\Exceptions;


/**
 * 网关发生错误异常
 *
 * Class GatewayErrorException
 * Author ShuQingZai
 * DateTime 2020/7/31 16:08
 *
 * @package Overbeck\Logistics\Exceptions
 */
class GatewayErrorException extends Exception
{
    protected $raw = [];

    public function __construct(string $message, int $code = 0, array $raw = [])
    {
        $this->raw = $raw;

        parent::__construct($message, $code);
    }

    /**
     * @return array
     */
    public function getRaw(): array
    {
        return $this->raw;
    }
}