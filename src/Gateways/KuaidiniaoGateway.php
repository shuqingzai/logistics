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

use Psr\Http\Message\ResponseInterface;
use Overbeck\Logistics\Exceptions\GatewayErrorException;
use Overbeck\Logistics\Exceptions\InvalidArgumentException;

/**
 * 快递鸟
 *
 * Class Kuaidiniao
 *
 * @author ShuQingZai
 * DateTime 2020/8/2 5:27
 */
class KuaidiniaoGateway extends GatewayAbstract
{
    const API_QUERY_URL = 'http://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx';

    const API_QUERY_CODE_URL = 'http://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx';

    const REQUEST_TYPE_QUERY = '1002';

    const REQUEST_TYPE_QUERY_CODE = '2002';

    /**
     * 查询物流信息.
     *
     * @author ShuQingZai
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

        $params = [
            'ShipperCode' => $companyCode,
            'LogisticCode' => $logisticNumber,
        ];
        $baseParams = $this->getBaseParams($params);
        $response = $this->post(self::API_QUERY_URL, $baseParams);

        return $this->formatData($response);
    }

    /**
     * 请求API获取快递公司code.
     *
     * @author ShuQingZai
     *
     * @throws GatewayErrorException
     */
    protected function queryCompanyCode(string $logisticNumber): string
    {
        $params = ['LogisticCode' => $logisticNumber];
        $baseParams = $this->getBaseParams($params, self::REQUEST_TYPE_QUERY_CODE);
        $response = $this->post(self::API_QUERY_CODE_URL, $baseParams);

        if (!\is_array($response)) {
            $response = \json_decode($response, true);
        }

        $code = $response['Shipper'][0]['ShipperCode'] ?? null;

        if (empty($response) || \is_null($code)) {
            throw new GatewayErrorException('Could not find this company code.', 404, (array) $response);
        }

        $this->companyName = $response['Shipper'][0]['ShipperName'] ?? $this->getCompanyNameByCode($code);

        return $code;
    }

    /**
     * 统一格式化物流状态code.
     *
     * @author ShuQingZai
     *
     * @param int|string $originalStatus 请求响应中返回的状态
     */
    protected function formatStatus($originalStatus): int
    {
        switch (\intval($originalStatus)) {
            case 2:
                $status = self::LOGISTICS_IN_TRANSIT;
                break;
            case 3:
                $status = self::LOGISTICS_SIGNED;
                break;
            case 4:
                $status = self::LOGISTICS_PROBLEM;
                break;
            default:
                $status = self::LOGISTICS_ERROR;
                break;
        }

        return $status;
    }

    /**
     * 格式化响应数据.
     *
     * @author ShuQingZai
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
        if (true === \boolval($response['Success'] ?? false)) {
            $code = 1;
            $originalStatus = $response['State'];
            $companyCode = $response['ShipperCode'];
            $logisticNumber = $response['LogisticCode'];
            foreach ($response['Traces'] as $item) {
                $list[] = [
                    'context' => $item['AcceptStation'],
                    'date_time' => $item['AcceptTime'],
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
     * 签名.
     *
     * @author ShuQingZai
     */
    protected function generateSign(array $param, string $appKey): string
    {
        return \urlencode(\base64_encode(\md5(\json_encode($param).$appKey)));
    }

    /**
     * 组装请求参数.
     *
     * @author ShuQingZai
     *
     * @param array  $params      请求参数
     * @param string $requestType 请求类型
     */
    private function getBaseParams(array $params, string $requestType = self::REQUEST_TYPE_QUERY): array
    {
        return [
            'RequestData' => \urlencode(\json_encode($params)),
            'EBusinessID' => $this->config->get('EBusinessID'),
            'RequestType' => $requestType,
            'DataSign' => $this->generateSign($params, $this->config->get('appKey')),
            'DataType' => '2',
        ];
    }
}
