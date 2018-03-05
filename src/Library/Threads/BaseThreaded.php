<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/2/24
 * Time: 上午11:11
 */

namespace DbPool\Library\Threads;
use DbPool\Server\Connections;
use DbPool\Library\Log;

class BaseThreaded extends \Threaded
{
    protected $_socket;
    protected $_id;
    protected $_connections;
    protected $_msg;

    public function __construct(Connections $connections, $socket, $id, $msg)
    {
        $this->_connections = $connections;
        $this->_socket = $socket;
        $this->_id = $id;
        $this->_msg = $msg;
    }

    public function sendMsg($msg)
    {
        if(!is_string($msg) || !is_integer($msg)) {
            $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
        }

        $f = @socket_write($this->_socket, $msg, strlen($msg));
        if($f === false || $f === NULL) {
            Log::log("Thread:[{$this->_id}]发送消息时连接已经断开");
        } else {
            Log::log("Thread:[{$this->_id}]发送数据成功--[{$msg}]");
        }

        if($f === false) {
            //同步代码
            $this->_connections->synchronized(function () {
                Log::log("[{$this->_id}]执行同步代码，关闭连接");
                unset($this->_connections->connections[$this->_id]);
                @socket_close($this->_socket);
            });
        }

        return $f;
    }


}