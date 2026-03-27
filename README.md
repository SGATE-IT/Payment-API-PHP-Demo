# Payment API PHP Demo

API 文档：[https://docs.sgate.sa/zh/v2/payinApi/](https://docs.sgate.sa/zh/v2/payinApi/)

## 项目结构

```
src/
├── OpenApi.php              # 入口类（初始化配置与各子模块）
├── Test.php                 # 调用示例
├── Lib/
│   ├── Config.php           # 配置数据类
│   ├── Auth.php             # 公共请求客户端（签名、加密、发送、解密、验签）
│   └── Order.php            # 订单相关接口
└── Utils/
    ├── AESUtils.php         # AES 加解密（AES-256-CBC）
    └── RSAUtils.php         # RSA 签名/验签（SHA256withRSA）
```

## 快速开始

**1. 安装依赖**

```bash
composer install
```

**2. 配置密钥**

在 `src/Test.php` 或您的业务代码中，初始化 `OpenApi` 时填入您的商户信息：

| 参数 | 说明 |
|---|---|
| `$apiHost` | API 地址，如 `https://api-dev.gccpay.cn` |
| `$apiKeyId` | 密钥ID，由平台分配 |
| `$merchantId` | 商户号，由平台分配 |
| `$merPrivateKey` | 商户RSA私钥（PKCS8，不含 PEM 头尾） |
| `$merPublicKey` | 商户RSA公钥（X509，不含 PEM 头尾） |
| `$aesKey` | AES密钥（32位十六进制字符串），由平台提供 |
| `$publicKey` | 平台RSA公钥（X509，不含 PEM 头尾），用于验证响应签名 |

**3. 运行 Demo**

```bash
php src/Test.php
```

## 请求流程

每次 API 调用均经过以下安全处理（由 `Auth` 统一处理）：

```
请求体 JSON
  → 商户私钥 RSA 签名 → 写入请求头 signature
  → AES 加密请求体    → 以 {"aesBuffer":"..."} 格式发送

响应体 data 字段
  → AES 解密
  → 平台公钥验证响应头 signature
```

## 接口列表

| 方法 | 接口路径 | 说明 |
|---|---|---|
| `$api->order->createOrder()` | `/pay/merchant/createOrder` | 创建支付订单 |
| `$api->order->inquiryTransactions()` | `/pay/merchant/inquiryTransactions` | 查询交易订单 |
| `$api->order->orderRefunds()` | `/pay/merchant/refund` | 申请退款 |
| `$api->order->inquiryRefund()` | `/pay/merchant/inquiryRefund` | 查询退款结果 |
| `$api->order->downloadFile()` | `/pay/merchant/downloadFile` | 下载对账文件 |

## 使用示例

```php
use Payment\OpenApi;

$api = new OpenApi($apiHost, $apiKeyId, $merchantId, $merPrivateKey, $merPublicKey, $aesKey, $publicKey);

// 创建订单
$result = $api->order->createOrder('ORD-001', '130', 'SAR', 'Test order', 'https://example.com/callback');

// 查询订单（平台订单号与商户订单号二选一）
$result = $api->order->inquiryTransactions(orderId: 'ORD260327093843194491930');

// 申请退款
$result = $api->order->orderRefunds('ORD260327093843194491930', '130', 'SAR', 'Customer request', 'REF-001');

// 查询退款（平台退款单号与商户退款单号二选一）
$result = $api->order->inquiryRefund(refundOrderId: 'REF250306123725194030261');

// 下载对账文件
$filePath = $api->order->downloadFile('20250310', 'settle', './download/');
```
