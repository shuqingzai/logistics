<?php
declare(strict_types=1);


namespace Sqz\Logistics\Gateways;


use Psr\Http\Message\ResponseInterface;
use Sqz\Logistics\Exceptions\GatewayErrorException;
use Sqz\Logistics\Exceptions\InvalidArgumentException;


/**
 * 快递100
 *
 * Class Kuaidi100Gateway
 * Author ShuQingZai
 * DateTime 2020/7/31 16:08
 *
 * @package Sqz\Logistics\Gateways
 */
class Kuaidi100Gateway extends GatewayAbstract
{
    const API_QUERY_URL = 'https://poll.kuaidi100.com/poll/query.do';

    const API_COM_CODE_URL = 'http://www.kuaidi100.com/autonumber/auto';

    /**
     * 查询物流信息
     *
     * Author ShuQingZai
     * DateTime 2020/7/31 17:55
     *
     * @param string      $trackingNumber 快递单号
     * @param string|null $company        快递公司名称
     * @return array
     */
    public function query(string $trackingNumber, ?string $company = null): array
    {
        $companyCode = \is_null($company) ? $this->getCompanyCode($trackingNumber) : $this->getCompanyCodeByCompanyList($company);

        if (empty($companyCode)) {
            throw new InvalidArgumentException('Error obtaining courier code');
        }

        $param     = [
            'com'      => $companyCode,
            'num'      => $trackingNumber,
            'resultv2' => 1
        ];
        $appSecret = $this->config->get('app_secret');
        $params    = [
            'customer' => $appSecret,
            'param'    => \json_encode($param),
            'sign'     => $this->generateSign($param, $this->config->get('app_key'), $appSecret),
        ];

        $response = $this->post(self::API_QUERY_URL, $params);

        return $this->formatData($response);
    }

    /**
     * 请求API获取快递公司code
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 17:19
     *
     * @param string $trackingNumber 快递单号
     * @return string
     * @throws GatewayErrorException
     */
    protected function getCompanyCode(string $trackingNumber): string
    {
        $params = [
            'key' => $this->config->get('app_key'),
            'num' => $trackingNumber,
        ];

        $response = $this->get(self::API_COM_CODE_URL, $params);

        if (!\is_array($response)) {
            $response = \json_decode($response, true);
        }

        if (empty($response)) {
            throw new GatewayErrorException('Could not find this company code.', 404);
        }
        
        return \strtolower(\current($response)['comCode']);
    }

    /**
     * 签名
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 16:18
     *
     * @param array  $params    签名参数
     * @param string $appKey    密匙 ( key )
     * @param string $appSecret 密钥 ( customer )
     * @return string
     */
    protected function generateSign(array $params, string $appKey, string $appSecret)
    {
        return \strtoupper(\md5(\json_encode($params) . $appKey . $appSecret));
    }

    /**
     * 格式化响应数据
     *
     * Author ShuQingZai
     * DateTime 2020/7/30 14:22
     *
     * @param ResponseInterface|array|string $response 原始响应数据
     * @return array
     * @throws GatewayErrorException
     */
    protected function formatData($response): array
    {
        if (!\is_array($response)) {
            $response = \json_decode($response, true);
        }

        if (empty($response)) {
            throw new GatewayErrorException('Failed to find data.', 404);
        }

        $list = [];
        if (\intval($response['status'] ?? 500) === 200) {
            $code           = 1;
            $originalStatus = $response['state'];
            $companyCode    = $response['com'];
            $trackingNumber = $response['nu'];
            foreach ($response['data'] as $item) {
                $list[] = [
                    'context'   => $item['context'],
                    'date_time' => $item['ftime'],
                ];
            }
        }
        else {
            $code           = 0;
            $originalStatus = 99;
            $companyCode    = '';
            $trackingNumber = '';
        }

        $status = $this->formatStatus($originalStatus);
        return [
            'code'            => $code,
            'status'          => $status,
            'status_name'     => $this->getStatusName($status),
            'company_code'    => $companyCode,
            'company_name'    => $this->companyName ?: $companyCode,
            'tracking_number' => $trackingNumber,
            'list'            => $list,
            'original_data'   => \json_encode($response)
        ];
    }

    /**
     * 统一格式化物流状态code
     *
     * Author ShuQingZai
     * DateTime 2020/7/30 11:28
     *
     * @param int|string $originalStatus 原始返回的状态
     * @return int
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