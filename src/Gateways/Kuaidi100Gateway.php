<?php
/**
 * 快递100
 *
 * FileName Kuaidi100.php
 * Created By PhpStorm.
 * Author ShuQingZai
 * DateTime 2020/7/28 16:57
 */
declare(strict_types=1);

namespace Sqz\Logistics\Gateways;


use Sqz\Logistics\Exceptions\GatewayAvailableException;
use Sqz\Logistics\Exceptions\InvalidArgumentException;


class Kuaidi100Gateway extends Gateway
{
    const API_QUERY_URL = 'https://poll.kuaidi100.com/poll/query.do';

    const API_COM_CODE_URL = 'http://www.kuaidi100.com/autonumber/auto';

    public function query(string $trackingNumber, ?string $company = null)
    {
        $companyCode = \is_null($company) ? $this->getCompanyCode($trackingNumber) : $this->getCompanyCodeByFile($company);
    }

    /**
     * 获取快递公司编号
     *
     * Author ShuQingZai
     * DateTime 2020/7/29 17:19
     *
     * @param string $trackingNumber
     * @return string
     * @throws GatewayAvailableException
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
            throw new GatewayAvailableException((array)$response, 404);
        }
        return \current($response)['comCode'];
    }

    protected function getCompanyCodeByFile(string $company)
    {
        $companyList        = [include '../config/company.php'];
        $configCompanyFiles = $this->config->get('company_file', []);
        foreach ($configCompanyFiles as $filePath) {
            $companyList;
        }
        $index = \array_search($company, \array_column($companyList, 'name'));

        if (false !== $index) {
            return $companyList[$index]['code'][$this->getGatewayName()];
        }

        throw new InvalidArgumentException();
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
}