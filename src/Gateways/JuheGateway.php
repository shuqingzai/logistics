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

namespace Overbeck\Logistics\Gateways;

use Overbeck\Logistics\Exceptions\GatewayErrorException;
use Overbeck\Logistics\Exceptions\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

class JuheGateway extends GatewayAbstract
{
    const API_QUERY_URL = 'http://v.juhe.cn/exp/index';

    /**
     * 查询物流信息.
     *
     * @param string      $logisticNumber 物流单号
     * @param string|null $company        物流公司名称
     * @param string|null $phone          收|寄件人的电话号码（顺丰必填，其他选填）
     *
     * @return array
     *
     * @throws GatewayErrorException
     * @throws InvalidArgumentException
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    public function query(string $logisticNumber, ?string $company = null, ?string $phone = null): array
    {
        if (empty($company)) {
            throw new InvalidArgumentException('The name of the logistics company is required.');
        }

        $senderPhone = 0;
        if ('顺丰速运' === $company) {
            if (empty($phone)) {
                throw new InvalidArgumentException('SF Express must fill in a mobile phone number.');
            }
            $senderPhone = \substr($phone, -4);
        }

        $companyCode = $this->getCompanyCodeByCompanyList($company);
        if (empty($companyCode)) {
            throw new InvalidArgumentException('Error obtaining courier code');
        }

        $params = [
            'com' => $companyCode,
            'no' => $logisticNumber,
            'senderPhone' => \intval($senderPhone),
            'key' => $this->config->get('key'),
            'dtype' => 'json',
        ];
        $response = $this->post(self::API_QUERY_URL, $params);

        return $this->formatData($response);
    }

    /**
     * 格式化响应数据.
     *
     * @param ResponseInterface|array|string $response 原始响应数据
     *
     * @return array
     *
     * @throws GatewayErrorException
     *
     * @author ShuQingZai<929024757@qq.com>
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
        if (0 === \intval($response['error_code'] ?? 1)) {
            $code = 1;
            $originalStatus = $response['result']['status_detail'];
            $companyCode = $response['result']['com'];
            $logisticNumber = $response['result']['no'];
            foreach ($response['result']['list'] as $item) {
                $list[] = [
                    'context' => $item['remark'],
                    'date_time' => $item['datetime'],
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
     * @param int|string $originalStatus 请求响应中返回的状态
     *
     * @return int
     *
     * @author ShuQingZai<929024757@qq.com>
     */
    protected function formatStatus($originalStatus): int
    {
        switch ($originalStatus) {
            case 'TAKING':
                $status = self::LOGISTICS_TAKING;
                break;
            case 'IN_TRANSIT':
                $status = self::LOGISTICS_IN_TRANSIT;
                break;
            case 'DELIVERING':
                $status = self::LOGISTICS_DELIVERING;
                break;
            case 'SIGNED':
                $status = self::LOGISTICS_SIGNED;
                break;
            case 'PROBLEM':
                $status = self::LOGISTICS_PROBLEM;
                break;
            case 'REJECTED':
                $status = self::LOGISTICS_REJECTED;
                break;
            case 'SEND_BACK':
                $status = self::LOGISTICS_SEND_RETURN;
                break;
            case 'TIMEOUT':
                $status = self::LOGISTICS_TIMEOUT;
                break;
            case 'FAILED':
                $status = self::LOGISTICS_DELIVERY_FAILED;
                break;
            default:
                $status = self::LOGISTICS_ERROR;
                break;
        }

        return $status;
    }
}
