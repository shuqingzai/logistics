<?php
declare(strict_types=1);

namespace Sqz\Logistics\Traits;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;


/**
 * Trait HasHttpRequest.
 */
trait HasHttpRequest
{
    /**
     * Make a get request.
     *
     * @param string $endpoint
     * @param array  $query
     * @param array  $headers
     *
     * @return array
     */
    protected function get(string $endpoint, array $query = [], array $headers = [])
    {
        return $this->request('get', $endpoint, [
            'headers' => $headers,
            'query'   => $query,
        ]);
    }

    /**
     * Make a post request.
     *
     * @param string $endpoint
     * @param array  $params
     * @param array  $headers
     *
     * @return array
     */
    protected function post(string $endpoint, array $params = [], array $headers = [])
    {
        return $this->request('post', $endpoint, [
            'headers'     => $headers,
            'form_params' => $params,
        ]);
    }

    /**
     * Make a post request with json params.
     *
     * @param string $endpoint
     * @param array  $params
     * @param array  $headers
     *
     * @return array
     */
    protected function postJson(string $endpoint, array $params = [], array $headers = [])
    {
        return $this->request('post', $endpoint, [
            'headers' => $headers,
            'json'    => $params,
        ]);
    }

    /**
     * Make a http request.
     *
     * @param string $method
     * @param string $endpoint
     * @param array  $options http://docs.guzzlephp.org/en/latest/request-options.html
     *
     * @return array
     */
    protected function request(string $method, string $endpoint, array $options = [])
    {
        return $this->unwrapResponse($this->getHttpClient($this->getBaseOptions())->{$method}($endpoint, $options));
    }

    /**
     * Return base Guzzle options.
     *
     * @return array
     */
    protected function getBaseOptions()
    {
        $options = \method_exists($this, 'getGuzzleOptions') ? $this->getGuzzleOptions() : [];

        return \array_merge($options, [
            'base_uri'        => \method_exists($this, 'getBaseUri') ? $this->getBaseUri() : '',
            'timeout'         => \method_exists($this, 'getTimeout') ? $this->getTimeout() : 5.0,
            'connect_timeout' => \method_exists($this, 'getConnectTimeout') ? $this->getConnectTimeout() : 5.0,
        ]);
    }

    /**
     * Return http client.
     *
     * @param array $options
     *
     * @return \GuzzleHttp\Client
     *
     * @codeCoverageIgnore
     */
    protected function getHttpClient(array $options = [])
    {
        return new Client($options);
    }

    /**
     * Convert response contents to json.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return ResponseInterface|array|string
     */
    protected function unwrapResponse(ResponseInterface $response)
    {
        $contentType = $response->getHeaderLine('Content-Type');
        $contents    = $response->getBody()->getContents();

        if (false !== \stripos($contentType, 'json') || \stripos($contentType, 'javascript')) {
            return \json_decode($contents, true);
        }
        elseif (false !== stripos($contentType, 'xml')) {
            return \json_decode(\json_encode(\simplexml_load_string($contents)), true);
        }

        return $contents;
    }
}
