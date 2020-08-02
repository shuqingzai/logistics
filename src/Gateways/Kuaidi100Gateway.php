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

namespace Overbeck\Logistics\Gateways;

use Psr\Http\Message\ResponseInterface;
use Overbeck\Logistics\Exceptions\GatewayErrorException;
use Overbeck\Logistics\Exceptions\InvalidArgumentException;

/**
 * 快递100.
 *
 * Class Kuaidi100Gateway
 * Author ShuQingZai
 * DateTime 2020/7/31 16:08
 */
class Kuaidi100Gateway extends GatewayAbstract
{
    const API_QUERY_URL = 'https://poll.kuaidi100.com/poll/query.do';

    const API_QUERY_CODE_URL = 'http://www.kuaidi100.com/autonumber/auto';

    /**
     * 查询物流信息.
     *
     * Author ShuQingZai
     * DateTime 2020/7/31 17:55
     *
     * @param string      $logisticNumber 物流单号
     * @param string|null $company        物流公司名称
     *
     * @throws GatewayErrorException
     * @throws InvalidArgumentException
     */
    public function query(string $logisticNumber, ?string $company = null): array
    {
        $companyCode = \is_null($company) ? $this->queryCompanyCode($logisticNumber) : $this->getCompanyCodeByCompanyList($company);

        if (empty($companyCode)) {
            throw new InvalidArgumentException('Error obtaining courier code');
        }

        $param = [
            'com' => $companyCode,
            'num' => $logisticNumber,
            'resultv2' => 1,
        ];
        $appSecret = $this->config->get('customer');
        $params = [
            'customer' => $appSecret,
            'param' => \json_encode($param),
            'sign' => $this->generateSign($param, $this->config->get('key'), $appSecret),
        ];

        $response = $this->post(self::API_QUERY_URL, $params);

        return $this->formatData($response);
    }

    /**
     * 请求API获取快递公司code.
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 17:19
     *
     * @param string $logisticNumber 快递单号
     *
     * @throws GatewayErrorException
     */
    protected function queryCompanyCode(string $logisticNumber): string
    {
        $params = [
            'key' => $this->config->get('key'),
            'num' => $logisticNumber,
        ];

        $response = $this->get(self::API_QUERY_CODE_URL, $params);

        if (!\is_array($response)) {
            $response = \json_decode($response, true);
        }

        $code = \current($response)['comCode'] ?? null;
        if (empty($response) || \is_null($code)) {
            throw new GatewayErrorException('Could not find this company code.', 404, (array) $response);
        }

        $code = \strtolower($code);
        $this->companyName = $this->getCompanyNameByCode($code);

        return $code;
    }

    /**
     * 签名.
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 16:18
     *
     * @param array  $params    签名参数
     * @param string $appKey    密匙 ( key )
     * @param string $appSecret 密钥 ( customer )
     *
     * @return string
     */
    protected function generateSign(array $params, string $appKey, string $appSecret)
    {
        return \strtoupper(\md5(\json_encode($params).$appKey.$appSecret));
    }

    /**
     * 格式化响应数据.
     *
     * Author ShuQingZai
     * DateTime 2020/7/30 14:22
     *
     * @param ResponseInterface|array|string $response 原始响应数据
     *
     * @throws GatewayErrorException
     */
    protected function formatData($response): array
    {
        if (!\is_array($response)) {
            $response = \json_decode($response, true);
        }

        if (empty($response)) {
            throw new GatewayErrorException('Failed to find data.', 404, (array) $response);
        }

        $list = [];
        if (200 === \intval($response['status'] ?? 500)) {
            $code = 1;
            $originalStatus = $response['state'];
            $companyCode = $response['com'];
            $logisticNumber = $response['nu'];
            foreach ($response['data'] as $item) {
                $list[] = [
                    'context' => $item['context'],
                    'date_time' => $item['ftime'],
                ];
            }
        } else {
            $code = 0;
            $originalStatus = 99;
            $companyCode = '';
            $logisticNumber = '';
        }

        $status = $this->formatStatus($originalStatus);

        return [
            'code' => $code,
            'status' => $status,
            'status_name' => $this->getStatusName($status),
            'company_code' => $companyCode,
            'company_name' => $this->companyName,
            'tracking_number' => $logisticNumber,
            'list' => $list,
            'original_data' => \json_encode($response),
        ];
    }

    /**
     * 统一格式化物流状态code.
     *
     * Author ShuQingZai
     * DateTime 2020/7/30 11:28
     *
     * @param int|string $originalStatus 请求响应中返回的状态
     */
    protected function formatStatus($originalStatus): int
    {
        switch (\intval($originalStatus)) {
            case 0:
            case 7:
            case 10:
            case 11:
            case 12:
                $status = self::LOGISTICS_IN_TRANSIT;
                break;
            case 1:
                $status = self::LOGISTICS_TAKING;
                break;
            case 2:
                $status = self::LOGISTICS_PROBLEM;
                break;
            case 3:
                $status = self::LOGISTICS_SIGNED;
                break;
            case 4:
                $status = self::LOGISTICS_RETURN_RECEIPT;
                break;
            case 5:
                $status = self::LOGISTICS_DELIVERING;
                break;
            case 6:
                $status = self::LOGISTICS_SEND_RETURN;
                break;
            case 14:
                $status = self::LOGISTICS_REJECTED;
                break;
            default:
                $status = self::LOGISTICS_ERROR;
                break;
        }

        return $status;
    }
}
