<?php
namespace Payment\Utils;


/**
 * AES 工具类
 * Class RSAUtils
 * @author dbn
 */
class AESUtils
{
    /**
     * 数据加密
     * @param string $data
     * @param string $aesKey
     * @param string $aesIv
     * @return string
     * @author dbnuo
     * @date 2025/2/11 18:16:28
     */
    public static function encryptData(string $data, string $aesKey, string $aesIv) {
        $encryptedData = openssl_encrypt(
            $data,
            'AES-256-CBC',
            $aesKey,
            OPENSSL_RAW_DATA,
            $aesIv
        );
        return base64_encode($encryptedData);
    }

    /**
     * 数据解密
     * @param string $data
     * @param string $aesKey
     * @param string $aesIv
     * @return string
     * @author dbnuo
     * @date 2025/2/11 18:16:28
     */
    public static function decryptData(string $data, string $aesKey, string $aesIv) {
        $encryptedData = openssl_decrypt(
            base64_decode($data),
            'AES-256-CBC',
            $aesKey,
            OPENSSL_RAW_DATA,
            $aesIv
        );
        return $encryptedData;
    }

}
