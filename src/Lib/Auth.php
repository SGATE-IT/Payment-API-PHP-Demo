<?php

namespace Payment\Lib;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Payment\OpenApi;
use Payment\Utils\AESUtils;
use Payment\Utils\RSAUtils;
use Psr\Http\Message\ResponseInterface;

class Auth
{
    private $pi;
    private Client $client;

    public function __construct(OpenApi $pi)
    {
        $this->pi     = $pi;
        $this->client = new Client(['http_errors' => false]);
    }

    /**
     * 业务接口请求
     * @param string $funName   调用方法名称
     * @param string $uri       请求URI
     * @param array  $params    请求参数
     * @param string $reqMethod 请求方法 POST、GET、PUT
     * @param int    $retry     当前重试次数
     * @return ResponseInterface|null
     */
    public function restApiHttp(string $funName, string $uri, array $params, string $reqMethod, int $retry = 1): ?ResponseInterface
    {
        if ($retry > 3) {
            return null;
        }

        $paramsJson    = $this->params2json($params);
        $sign          = $this->sign($paramsJson);
        $headers       = $this->withHeaders($sign);
        $url           = $this->pi->config->apiHost . $uri;
        $requestBody   = '';
        $response      = null;
        $status        = 0;

        try {
            switch ($reqMethod) {
                case 'POST':
                case 'PUT':
                    $requestBody = json_encode(['aesBuffer' => $this->encryptData($paramsJson)]);
                    $response    = $this->client->request($reqMethod, $url, [
                        'headers' => array_merge($headers, ['Content-Type' => 'application/json']),
                        'body'    => $requestBody,
                    ]);
                    break;

                case 'GET':
                default:
                    $queryStr = $params ? '?' . http_build_query($params) : '';
                    $response = $this->client->get($url . $queryStr, ['headers' => $headers]);
                    break;
            }

            $status = $response ? $response->getStatusCode() : 0;
        } catch (GuzzleException $e) {
            $this->pi->config->log($funName, $e->getMessage());
        }

        $this->pi->config->log($funName, [
            'request_url'     => $url,
            'request_headers' => $headers,
            'request_retry'   => $retry,
            'request_params'  => $params,
            'request_body'    => $requestBody,
            'response_status' => $status,
        ]);

        if ($status < 200 || $status >= 300) {
            return $this->restApiHttp($funName, $uri, $params, $reqMethod, $retry + 1);
        }

        return $response;
    }

    /**
     * 下载文件请求（响应为文件流，不走 JSON 解析流程）
     * @param string $funName  调用方法名称
     * @param string $uri      请求URI
     * @param array  $params   请求参数
     * @param string $savePath 文件保存目录
     * @return string|null 保存成功返回文件完整路径，失败返回 null
     */
    public function restApiHttpDownload(string $funName, string $uri, array $params, string $savePath): ?string
    {
        $paramsJson = $this->params2json($params);
        $sign       = $this->sign($paramsJson);
        $headers    = $this->withHeaders($sign);
        $url        = $this->pi->config->apiHost . $uri;

        try {
            $body     = json_encode(['aesBuffer' => $this->encryptData($paramsJson)]);
            $response = $this->client->post($url, [
                'headers' => array_merge($headers, ['Content-Type' => 'application/json']),
                'body'    => $body,
            ]);
            $status   = $response->getStatusCode();

            $this->pi->config->log($funName, ['request_url' => $url, 'response_status' => $status]);

            if ($status < 200 || $status >= 300) {
                return null;
            }

            $rawBody = $response->getBody()->getContents();

            // 若响应为 JSON，说明接口返回了业务错误
            $decoded = json_decode($rawBody, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['code'])) {
                $this->pi->config->log($funName, $decoded);
                return null;
            }

            // 从 Content-Disposition 解析文件名
            $fileName           = null;
            $contentDisposition = $response->getHeaderLine('Content-Disposition');
            if ($contentDisposition && preg_match('/filename=([^;\s"]+)/', $contentDisposition, $m)) {
                $fileName = $m[1];
            }
            $fileName = $fileName ?: ('download_' . time() . '.xlsx');

            if (!is_dir($savePath)) {
                mkdir($savePath, 0755, true);
            }

            $filePath = rtrim($savePath, '/') . '/' . $fileName;
            file_put_contents($filePath, $rawBody);

            $this->pi->config->log($funName, ['saved' => $filePath]);
            return $filePath;

        } catch (GuzzleException $e) {
            $this->pi->config->log($funName, $e->getMessage());
            return null;
        }
    }

    /**
     * 解析响应数据（解密 + 验签）
     * @param ResponseInterface|null $response
     * @return array
     */
    public function responseData(?ResponseInterface $response): array
    {
        $responseData = [];

        do {
            if (!$response) {
                break;
            }

            $status = $response->getStatusCode();

            if ($status < 200 || $status >= 300) {
                $this->pi->config->log(__FUNCTION__, 'http error: ' . $status);
                break;
            }

            $result = json_decode($response->getBody()->getContents(), true);

            if (empty($result)) {
                $this->pi->config->log(__FUNCTION__, 'response data is empty');
                break;
            }

            $code = $result['code'] ?? 0;
            $data = $result['data'] ?? null;

            if ($code != 10000) {
                $this->pi->config->log(__FUNCTION__, $result);
                break;
            }

            if (empty($data)) {
                break;
            }

            // 解密数据
            $decryptData = AESUtils::decryptData($data, $this->pi->config->aesKey, '0000000000000000');

            if (empty($decryptData)) {
                $this->pi->config->log(__FUNCTION__, 'decrypt data is empty');
                break;
            }

            // 验证响应签名
            $respSign = $response->getHeaderLine('signature');

            if (empty($respSign)) {
                $this->pi->config->log(__FUNCTION__, 'signature is empty');
                break;
            }

            $verifySign = RSAUtils::verifySign(
                $decryptData,
                RSAUtils::str2key($this->pi->config->publicKey, RSAUtils::PUBLIC_TYPE),
                $respSign
            );

            if (!$verifySign) {
                $this->pi->config->log(__FUNCTION__, 'verify sign failed');
                break;
            }

            $result       = json_decode($decryptData, true);
            $responseData = $result ?: [];

            $this->pi->config->log('responseData', $result);

        } while (false);

        return $responseData;
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    private function params2json(array $params): string
    {
        return json_encode($params, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    private function sign(string $paramsJson): string
    {
        return RSAUtils::sign($paramsJson, RSAUtils::str2key($this->pi->config->merPrivateKey, RSAUtils::PRIVATE_TYPE));
    }

    private function withHeaders(string $sign): array
    {
        return [
            'merchantKeyId'    => $this->pi->config->apiKeyId,
            'version'          => '1.0',
            'timeStamp'        => (string) time(),
            'signature'        => $sign,
            'content-language' => $this->pi->config->language,
        ];
    }

    private function encryptData(string $data): string
    {
        return AESUtils::encryptData($data, $this->pi->config->aesKey, '0000000000000000');
    }
}
