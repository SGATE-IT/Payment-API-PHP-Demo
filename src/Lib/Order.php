<?php

namespace Payment\Lib;

use Payment\OpenApi;

class Order
{
    private $pi;

    public function __construct(OpenApi $pi)
    {
        $this->pi = $pi;
    }

    /**
     * 创建订单
     * @param string $merchantOrderId
     * @param string $amount
     * @param string $currency
     * @param string $name
     * @return array
     * @author dbnuo
     * @date 2025/2/11 17:32:20
     */
    public function createOrder(
        string $merchantOrderId,
        string $amount,
        string $currency,
        string $name,
        string $callbackFrontUrl
    ) {
        $uri = '/pay/merchant/createOrder';

        $params = [
            "merchantId"          => $this->pi->config->merchant,
            "merOrderId"       => $merchantOrderId,
            "orderType"         => "online_payin",
            "orderAmt"         => $amount,
            "orderCurrency"    => $currency,
            "expireTime"       => "60",
            "frontRedirectUrl" => $callbackFrontUrl,
            "orderDesc"        => $name,
            "productInfo"      => [
                [
                    "productType"      => "virtual",
                    "productName"      => "Risk",
                    "productQuantity"  => "1",
                    "productUnitPrice" => $amount,
                    "productCurrency"  => $currency,
                    "productAvatarUrl" => "_",
                ]
            ],
            "customerInfo" => [
                "customerUid"      => uniqid(),
                "customerName"     => "Demo",
                "customerPlatform" => "Demo",
                "customerEmail"    => "demo@sgate.com",
            ]
        ];

        $response = $this->pi->auth->restApiHttp(
            __FUNCTION__,
            $uri,
            $params,
            'POST'
        );

        return $this->pi->auth->responseData($response);
    }

    /**
     * 订单退款
     * @param string $orderId 平台订单号
     * @param string $amount 退款金额，字符串格式，小数点后最多两位
     * @param string $currency 退款币种
     * @param string $reason 退款原因
     * @param string $merchantRefundId 商户退款单号
     * @return array
     */
    public function orderRefunds(
        string $orderId,
        string $amount,
        string $currency,
        string $reason,
        string $merchantRefundId
    ): array {
        $uri = '/pay/merchant/refund';

        $params = [
            "merchantId"       => $this->pi->config->merchant,
            "merRefundOrderId" => $merchantRefundId,
            "origOrderId"      => $orderId,
            "refundAmt"        => $amount,
            "refundCurrency"   => $currency,
            "refundDesc"       => $reason,
        ];

        $response = $this->pi->auth->restApiHttp(__FUNCTION__, $uri, $params, 'POST');
        return $this->pi->auth->responseData($response);
    }

    /**
     * 查询交易订单
     * @param string $orderId 平台订单号（与 $merOrderId 二选一）
     * @param string $merOrderId 商户订单号（与 $orderId 二选一）
     * @return array
     */
    public function inquiryTransactions(string $orderId = '', string $merOrderId = ''): array
    {
        $uri = '/pay/merchant/inquiryTransactions';

        $params = [
            "merchantId" => $this->pi->config->merchant,
            "orderId"    => $orderId,
            "merOrderId" => $merOrderId,
        ];

        $response = $this->pi->auth->restApiHttp(__FUNCTION__, $uri, $params, 'POST');
        return $this->pi->auth->responseData($response);
    }

    /**
     * 查询退款订单
     * @param string $refundOrderId 平台退款单号（与 $merRefundOrderId 二选一）
     * @param string $merRefundOrderId 商户退款单号（与 $refundOrderId 二选一）
     * @return array
     */
    public function inquiryRefund(string $refundOrderId = '', string $merRefundOrderId = ''): array
    {
        $uri = '/pay/merchant/inquiryRefund';

        $params = [
            "merchantId"       => $this->pi->config->merchant,
            "refundOrderId"    => $refundOrderId,
            "merRefundOrderId" => $merRefundOrderId,
        ];

        $response = $this->pi->auth->restApiHttp(__FUNCTION__, $uri, $params, 'POST');
        return $this->pi->auth->responseData($response);
    }

    /**
     * 下载对账文件
     * @param string $orderDate 交易日期，格式：yyyyMMdd
     * @param string $fileType 文件类型：settle=对账文件
     * @param string $savePath 文件保存目录
     * @return string|null 保存成功返回文件完整路径，失败返回 null
     */
    public function downloadFile(string $orderDate, string $fileType = 'settle', string $savePath = './download/'): ?string
    {
        $uri = '/pay/merchant/downloadFile';

        $params = [
            "merchantId" => $this->pi->config->merchant,
            "orderDate"  => $orderDate,
            "fileType"   => $fileType,
        ];

        return $this->pi->auth->restApiHttpDownload(__FUNCTION__, $uri, $params, $savePath);
    }

}
