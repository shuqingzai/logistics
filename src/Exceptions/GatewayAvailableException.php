<?php
declare(strict_types=1);


namespace Sqz\Logistics\Exceptions;


use Sqz\Logistics\Interfaces\GatewayAvailableInterface;
use Throwable;


/**
 * 网关可用异常
 *
 * Class GatewayAvailableException
 * Author ShuQingZai
 * DateTime 2020/7/31 16:07
 *
 * @package Sqz\Logistics\Exceptions
 */
class GatewayAvailableException extends Exception implements GatewayAvailableInterface
{
    protected $results = [];

    protected $exceptions = [];

    public function __construct(array $results = [], $code = 0, Throwable $previous = null)
    {
        $this->results    = $results;
        $this->exceptions = \array_column($results, 'exception', 'gateway');

        parent::__construct('The gateways have failed. You can check "\Sqz\Logistics\Interfaces\GatewayAvailableInterface" to get the results', $code, $previous);
    }


    /**
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @param string $gateway
     *
     * @return mixed|null
     */
    public function getException(string $gateway)
    {
        return isset($this->exceptions[$gateway]) ? $this->exceptions[$gateway] : null;
    }

    /**
     * @return array
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    /**
     * @return mixed
     */
    public function getLastException()
    {
        return end($this->exceptions);
    }

}