<?php

/**
 * API Demo
 *
 * 请将下方配置替换为您的实际商户信息后运行。
 * API 文档：https://docs.sgate.sa/zh/v2/payinApi/
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Payment\OpenApi;

// -----------------------------------------------------------------------
// 配置项（替换为真实商户信息）
// -----------------------------------------------------------------------
$apiHost       = 'https://api-dev.gccpay.cn';
$apiKeyId      = '12345678901';                       // 密钥ID，由平台分配
$merchantId    = 'M96620240924161327000862';          // 商户号，由平台分配
$merPrivateKey = 'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC55Y3+nuJilZMr' .
    'pN5l9fk4neHn3BmDOhPbWtOP/OLdDBni7b0EF5Pwp12ggKXbjNyXvX46aEExnGmL' .
    'tPySHNxomH4wQqA57R0oW5fx2iY8XO8XD+2yEqoHmTdONQ2llF0nUlLmpGh/b6Ra' .
    'fKKBH0XrIV76ACLlFL6CuyQGJ1qVSN1Y4NbUPURGBcfGO8Hz+3shOLLGlppTSqPL' .
    'P0K6+x6eSWXDCbCmKZybJoMmhYzi0fdeAf3kgmROxwVCpL6SynxTN7zyujZdcDE5' .
    '+HFupiVfCPFDp68DzdYGIfMPz1YMLIh9SNOPTQqVaXmtjNx+PaSYqCiJzmxvBjPb' .
    'wezw60gfAgMBAAECggEADn7Cz37a6JSaZCw35Aifa6jyWFHGP4BEBulFikdN9uFR' .
    'rdGbcnoJx0yvNUVMXLRwIvjb1d9GB7Tj85VwnrTXaH4Z7aNt7cMto3VfTk5HVJLRv' .
    '24Dh8yCxSyCSBV4Jr6AQQFFuF9+snXyTjsq5LMBxBYKQafIqmCV6YjnUMxSxavfaL' .
    'cTZDg/txoTYEKB07niS8dA9sUDVWk23A1E1E5YioOzSeEYWfEEE17xe6su+V5KmhB' .
    'ITqHtEuSRkccxWMvptbKObXQKIV2fx1brobQt5yzJviVUqiu0moi3haOs3VBgXq32w' .
    'BO9bq72dpZTbGQ9n5qOBvfbuSfewzLbHm6VkQKBgQDcqmj8JYwJ5RNx2Ar5gO1T7m' .
    'lG7QEJUY4M5InotJ0zXuYU3eTyKMo0BFUlt77ZJ6lR2PyCW554nV6Zg6awNbHsKHO' .
    'vNBJ4jlY+KnOYFchLQ4HeOSsnA9OO5XPl3oiIZsI5TItIoD4PclU3jsUBNhUwUeQx' .
    'spzM2n0mBkvXDez2CQKBgQDXqeKabLQ3jkYaI6OCHPcHGps+isO4OHvsEie3bMYtF' .
    'zLu6JdIJDGR6KxufBkthEAaWwG8ojJ0p3oNMA+LF8pRbSts4FirwREYZNZYsI3MZM' .
    'DUO+raFPq1v1ZpNfc3PSI1Bws9bGTXAbcsOxhuJaimGX1q6qglSRI9nzJMxHCW5wK' .
    'BgGRzxvsR9KAEgkeO+9/9CwzsOUyqU5B0aeAAoa8nmXBrQP46zSBX5USsvD5BWUXt' .
    'wiyaRMjrAEcUDJ6Byf3pU6eX+qHFaKss0KHYHWscb2OjxZjuGXDXUxV36ry4Axtk' .
    '/AGtkLJtEBNkDtsNySz1+8tVXDYrgynWRKZss1Wg50BRAoGACniXJgRNI71mrfI5C' .
    'CI75D5odzrpkdI8QhQHlaJUZPARawQkBD6toXX4mUyxNEKNkjoE9ZGyfXN8O5OvzY' .
    'MUMavpRdoGtCAloleTCK9Z0yi5LBTUrE4EdjqaCXWzUR1IweZbp1nR85aDvEQKRZ7' .
    'Sd24ZZs2J6HWJyzAlkxCentUCgYEAmAOHIQI6V8aNYT0xOjKOWIAqyI3pPruQ51K4' .
    '3yoo/XhrRLygfDj7ikQdFYd+gN1W3mlsT3EN2Qjz3XhczVOqE1RCn15v3WTydikzX' .
    'n8pEFmYf9kV2GS11eMTTKYv2UoLqN7j3a74fzfv67xsm5cJlbO6Dt3goBFZMerHdH' .
    'Sf7vY=';            // 商户RSA私钥（PKCS8，不含头尾）
$merPublicKey  = 'MIIBIjANBgkqhkiG9...';            // 商户RSA公钥（X509，不含头尾，暂未使用）
$aesKey        = '1af9206a4d0bd6becdbe188379a5f65d'; // AES密钥（32位十六进制，由平台提供）
$publicKey     = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAueWN/p7iYpWTK6TeZfX5' .
    'OJ3h59wZgzoT21rTj/zi3QwZ4u29BBeT8KddoICl24zcl71+OmhBMZxpi7T8khzc' .
    'aJh+MEKgOe0dKFuX8domPFzvFw/tshKqB5k3TjUNpZRdJ1JS5qRof2+kWnyigR9F' .
    '6yFe+gAi5RS+grskBidalUjdWODW1D1ERgXHxjvB8/t7ITiyxpaaU0qjyz9Cuvse' .
    'nkllwwmwpimcmyaDJoWM4tH3XgH95IJkTscFQqS+ksp8Uze88ro2XXAxOfhxbqYlX' .
    'wjxQ6evA83WBiHzD89WDCyIfUjTj00KlWl5rYzcfj2kmKgoic5sbwYz28Hs8OtIH' .
    'wIDAQAB';             // 平台RSA公钥（X509，不含头尾），用于验签

try {
    $api = new OpenApi($apiHost, $apiKeyId, $merchantId, $merPrivateKey, $merPublicKey, $aesKey, $publicKey);

    // -----------------------------------------------------------------------
    // 示例1：创建支付订单
    // POST /pay/merchant/createOrder
    // -----------------------------------------------------------------------
    $result = $api->order->createOrder(
        merchantOrderId: uniqid('ORD-'),    // 商户订单号（全局唯一）
        amount: '130',             // 订单金额
        currency: 'SAR',             // 币种
        name: 'Test order',      // 订单描述
        callbackFrontUrl: 'https://example.com/pay/callback'
    );
    echo '[createOrder] ' . json_encode($result, JSON_UNESCAPED_UNICODE) . PHP_EOL;

    // -----------------------------------------------------------------------
    // 示例2：查询交易订单
    // POST /pay/merchant/inquiryTransactions
    // orderId 与 merOrderId 二选一
    // -----------------------------------------------------------------------
    $result = $api->order->inquiryTransactions(
        orderId: 'ORD260327093843194491930',  // 平台订单号
        merOrderId: ''                           // 商户订单号
    );
    echo '[inquiryTransactions] ' . json_encode($result, JSON_UNESCAPED_UNICODE) . PHP_EOL;

    // -----------------------------------------------------------------------
    // 示例3：申请退款
    // POST /pay/merchant/refund
    // -----------------------------------------------------------------------
    $result = $api->order->orderRefunds(
        orderId: 'ORD260327093843194491930',  // 平台订单号
        amount: '130',                       // 退款金额
        currency: 'SAR',                       // 退款币种
        reason: 'Customer request',          // 退款原因
        merchantRefundId: uniqid('REF-')               // 商户退款单号（全局唯一）
    );
    echo '[orderRefunds] ' . json_encode($result, JSON_UNESCAPED_UNICODE) . PHP_EOL;

    // -----------------------------------------------------------------------
    // 示例4：查询退款订单
    // POST /pay/merchant/inquiryRefund
    // refundOrderId 与 merRefundOrderId 二选一
    // -----------------------------------------------------------------------
    $result = $api->order->inquiryRefund(
        refundOrderId: 'REF250306123725194030261',  // 平台退款单号
        merRefundOrderId: ''                           // 商户退款单号
    );
    echo '[inquiryRefund] ' . json_encode($result, JSON_UNESCAPED_UNICODE) . PHP_EOL;

    // -----------------------------------------------------------------------
    // 示例5：下载对账文件
    // POST /pay/merchant/downloadFile
    // -----------------------------------------------------------------------
    $savedPath = $api->order->downloadFile(
        orderDate: '20250310',      // 交易日期，格式：yyyyMMdd
        fileType: 'settle',        // 文件类型：settle=对账文件
        savePath: './download/'    // 保存目录
    );
    echo '[downloadFile] ' . ($savedPath ?? 'failed') . PHP_EOL;
} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;die;
}
