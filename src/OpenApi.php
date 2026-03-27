<?php

namespace Payment;

use Payment\Lib\Auth;
use Payment\Lib\Config;
use Payment\Lib\Order;

class OpenApi
{
    public $config;
    public $auth;
    public $order;

    public function __construct(
        string $apiHost,
        string $apiKeyId,
        string $merchant,
        string $merPrivateKey,
        string $merPublicKey,
        string $aesKey,
        string $publicKey,
        string $language = "en-US"
    ) {
        $this->config = new Config();

        $this->config->apiHost       = $apiHost;
        $this->config->apiKeyId      = $apiKeyId;
        $this->config->merchant      = $merchant;
        $this->config->merPrivateKey = $merPrivateKey;
        $this->config->merPublicKey  = $merPublicKey;
        $this->config->aesKey        = $aesKey;
        $this->config->publicKey     = $publicKey;
        $this->config->language      = $language;

        $this->auth = new Auth($this);
        $this->order = new Order($this);
    }
}
