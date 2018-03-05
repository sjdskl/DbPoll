<?php
/**
 * 客户端.
 * User: kailishen
 * Date: 2018/3/5
 * Time: 下午4:21
 */
namespace DbPool\Client;

use DbPool\Library\Log;
use DbPool\Exception\ParamsErrorException;

class DbPoolClient
{
    private static $_instance;

    private $_socket;

    private $_stack;

    private $_deepest_stack;

    const DELIMITER = '\r\n\r\n';

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
        $msg .= self::DELIMITER;
        $f = socket_write($this->_socket, $msg, strlen($msg));
        //TODO 重试次数
        $this->_stack = [];
        $this->_deepest_stack = [];
        if(!$f) {
            Log::log('发送失败:' . socket_strerror(socket_last_error($this->_socket)));
        }
        $data = socket_read($this->_socket, 65535);
        return json_decode($data, true);
    }

    public function __call($name, $arguments)
    {
        $stack['method'] = $name;
        $stack['params'] = $arguments;
        $stack['results'] = '';
        if(!$this->_stack) {
            $this->_stack = $stack;
            $this->_deepest_stack = &$this->_stack;
        } else {
            $this->_deepest_stack['results'] = $stack;
            $this->_deepest_stack = &$this->_deepest_stack['results'];
        }

        return $this;
    }

    public function __destruct()
    {
        @socket_close($this->_socket);
    }

}