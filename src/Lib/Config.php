<?php

namespace Payment\Lib;

class Config
{
    public $apiHost;
    public $apiKeyId;
    public $merchant;
    public $merPrivateKey;
    public $merPublicKey;
    public $aesKey;
    public $publicKey;

    public $language;

    /**
     * 记录日志
     * @param string $desc 描述
     * @param mixed $data 记录数据
     * @return void
     * @author dbnuo
     * @date 2023/4/17 18:47:37
     */
    public function log(string $desc, $data)
    {
        // TODO: 记录日志
        echo "[{$desc}] " . json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}
