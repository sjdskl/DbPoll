<?php
/**
 * 客户端.
 * User: kailishen
 * Date: 2018/3/5
 * Time: 下午4:21
 */
namespace DbPool\Client;

use DbPool\Library\Encrypt\RSA;
use DbPool\Library\Log;
use DbPool\Exception\ParamsErrorException;
use DbPool\Config;

class DbPoolClient
{
    private static $_instance;

    private $_socket;

    private $_stack;

    private $_deepest_stack;

    private $_err;

    private $_trans = '';

    const DELIMITER = '\r\n\r\n';

    private $_rsa;

    private function __construct($address, $domain = AF_INET, $port = 1122)
    {
        $this->_socket = socket_create($domain, SOCK_STREAM, SOL_TCP);
        if($domain == AF_INET) {
            if(!$port) {
                throw new ParamsErrorException("参数错误, 缺少端口");
            }
            if(!socket_connect($this->_socket, $address, $port)) {
                throw new \Exception('绑定失败');
            }
        } else if($domain == AF_UNIX) {
            if(!socket_connect($this->_socket, $address)) {
                throw new \Exception('绑定失败');
            }
        }
        $this->_rsa = new RSA(Config::$ClientPrivateKey, Config::$ServerPublicKey);
    }

    private function __clone()
    {

    }

    public static function getInstance($address, $domain = AF_INET, $port = 1122)
    {
        if(!self::$_instance instanceof self) {
            self::$_instance = new self($address, $domain, $port);
        }

        return self::$_instance;
    }

    public function excute()
    {
        if(!$this->_stack) {
            return false;
        }
        $msg = json_encode($this->_stack);
        $msg = $this->_rsa->encrypt($msg) . self::DELIMITER;
        $f = socket_write($this->_socket, $msg, strlen($msg));
        //TODO 重试次数
        $this->_stack = [];
        $this->_deepest_stack = [];
        if(!$f) {
            Log::log('发送失败:' . socket_strerror(socket_last_error($this->_socket)));
        }
        $data = '';
        while($tmp = socket_read($this->_socket, 65535)) {
            $data .= $tmp;
            if(stripos($tmp, self::DELIMITER) !== false) {
                break;
            }
        }
        $data = trim($data, self::DELIMITER);
        $data = $this->_rsa->decrypt($data);
        $data = json_decode($data, true);
        if($data) {
            if($data['code'] != Config::SUCCESS_CODE) {
                $this->_err = $data['msg'];
                return false;
            } else {
                return $data['msg'];
            }
        } else {
            return false;
        }
    }

    public function getLastError()
    {
        return $this->_err;
    }

    public function __call($name, $arguments)
    {
        $stack['method'] = $name;
        $stack['params'] = $arguments;
        $stack['results'] = '';
        $stack['trans'] = $this->_trans;
        if(!$this->_stack) {
            $this->_stack = $stack;
            $this->_deepest_stack = &$this->_stack;
        } else {
            $this->_deepest_stack['results'] = $stack;
            $this->_deepest_stack = &$this->_deepest_stack['results'];
        }

        return $this;
    }

    protected function beginTrans()
    {
        $this->_trans = Config::$TransStart;
        $this->getPdo()->beginTransaction()->excute();
    }

    protected function endTrans()
    {
        $this->_trans = Config::$TransEnd;
        $this->getPdo()->commit()->excute();
    }

    protected function roll()
    {
        $this->_trans = Config::$TransEnd;
        $this->getPdo()->rollBack()->excute();
    }

    public function action(callable $function, $params = [])
    {
        try {
            $this->beginTrans();
            $f = $function(...$params);
            if(!$f) {
                $this->roll();
            } else {
                $this->endTrans();
            }
        } catch (\Exception $e) {
            $this->roll();
        }
        $this->_trans = '';
    }

    public function __destruct()
    {
        @socket_close($this->_socket);
    }

    protected function _getProtocol($name, $arguments, $transStep = null)
    {

    }

}