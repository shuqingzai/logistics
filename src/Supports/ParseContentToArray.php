<?php
declare(strict_types=1);

namespace Sqz\Logistics\Supports;


use Sqz\Logistics\Exceptions\InvalidArgumentException;

/**
 * 解析内容为数组并返回
 *
 * Class companyFiles
 * Author ShuQingZai
 * DateTime 2020/8/1 16:34
 *
 * @package Sqz\Logistics\Supports
 */
class ParseContentToArray
{

    /**
     * 解析php文件
     *
     * Author ShuQingZai
     * DateTime 2020/8/1 16:41
     *
     * @param string $file
     * @return array
     */
    public static function parsePhp(string $file): array
    {
        return \is_file($file) ? (include $file) : [];
    }

    /**
     * 解析json
     *
     * Author ShuQingZai
     * DateTime 2020/8/1 16:41
     *
     * @param string $file
     * @return array
     */
    public static function parseJson(string $file): array
    {
        if (\is_file($file)) {
            $file = \file_get_contents($file);
        }
        return \json_decode($file, true);
    }

    /**
     * 解析xml
     *
     * Author ShuQingZai
     * DateTime 2020/8/1 16:41
     *
     * @param string $file
     * @return array
     */
    public static function parseXml(string $file): array
    {
        $result = (array)(\is_file($file) ? \simplexml_load_file($file) : \simplexml_load_string($file));
        foreach ($result as $key => $val) {
            if (\is_object($val)) {
                $result[$key] = (array)$val;
            }
        }
        return $result;
    }


    /**
     * 解析ini
     *
     * Author ShuQingZai
     * DateTime 2020/8/1 16:51
     *
     * @param string $file
     * @return array
     */
    public static function parseIni(string $file): array
    {
        return \is_file($file) ? \parse_ini_file($file, true) : \parse_ini_string($file, true);
    }

    /**
     * 解析内容
     *
     * Author ShuQingZai
     * DateTime 2020/8/1 17:00
     *
     * @param string $content 需要解析的内容 可以是一个文件
     * @param string $type    内容类型
     * @return array
     * @throws InvalidArgumentException
     */
    public static function parseContent(string $content, string $type = 'php'): array
    {
        switch (\strtolower($type)) {
            case 'php':
                $res = self::parsePhp($content);
                break;
            case 'json':
                $res = self::parseJson($content);
                break;
            case 'xml':
                $res = self::parseXml($content);
                break;
            case 'ini':
                $res = self::parseIni($content);
                break;
            default:
                throw new InvalidArgumentException('This type of content analysis is not currently supported.');
        }

        return $res;
    }
}