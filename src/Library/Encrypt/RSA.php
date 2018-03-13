<?php
/**
 * Ecrypt decrypt data with openssl
 * User: kailishen
 * Date: 2018/3/12
 * Time: 下午4:00
 */

namespace DbPool\Library\Encrypt;


use DbPool\Config;
use DbPool\Library\Log;

class RSA
{

    private $_public_key;

    private $_private_key;

    private $_key_len;

    public function __construct($private_key, $public_key)
    {
        $this->_public_key = openssl_pkey_get_public(file_get_contents($public_key));
        $this->_private_key = openssl_pkey_get_private(file_get_contents($private_key));
        $this->_key_len = openssl_pkey_get_details($this->_public_key)['bits'];
    }

    public function rsaEncrypt($data){
        $crypto = '';
        if(Config::$Encrypt) {
            $part_len = $this->_key_len / 8 - 66;
            $parts = str_split($data, $part_len);
            foreach ($parts as $chunk) {
                $encrypted = '';
                openssl_public_encrypt ( $chunk, $encrypted, $this->_public_key, OPENSSL_PKCS1_OAEP_PADDING); // 公钥加密
                $crypto .= $encrypted;
            }
        } else {
            $crypto = $data;
        }
        return base64_encode ( $crypto );
    }
    public function rsaDecrypt($data){
        $crypto = '';
        if(Config::$Encrypt) {
            $part_len = $this->_key_len / 8;
            foreach (str_split(base64_decode($data), $part_len) as $chunk) {
                $decrypted = '';
                openssl_private_decrypt($chunk, $decrypted, $this->_private_key, OPENSSL_PKCS1_OAEP_PADDING); // 私钥解密
                $crypto .= $decrypted;
            }
        } else {
            $crypto = base64_decode($data);
        }
        return $crypto;
    }

    public function aesEncrypt($data,$key = '&3lsd_&%$', $cipher = 'AES-256-CTR', $iv = "1A6bfyMuCvrE4SE08C1G4g==")
    {
        if(Config::$Encrypt) {
            return base64_encode(openssl_encrypt($data, $cipher, $key, $options=0, base64_decode($iv)));
        } else {
            return base64_encode($data);
        }
    }

    public function aesDecrypt($data,$key = '&3lsd_&%$',  $cipher = 'AES-256-CTR', $iv = "1A6bfyMuCvrE4SE08C1G4g==")
    {
        if(Config::$Encrypt) {
            return openssl_decrypt(base64_decode($data), $cipher, $key, $options = 0, base64_decode($iv));
        } else {
            return base64_decode($data);
        }
    }

    public function encrypt($data)
    {
        $method = strtolower(Config::$EncryptType) . "Encrypt";
        Log::log("调用[{$method}]");
        return $this->$method($data);
    }

    public function decrypt($data)
    {
        $method = strtolower(Config::$EncryptType) . "Decrypt";
        Log::log("调用[{$method}]");
        return $this->$method($data);
    }

}