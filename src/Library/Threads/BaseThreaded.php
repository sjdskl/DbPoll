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
use DbPool\Config;

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

    public function sendMsg($msg, $code = Config::SUCCESS_CODE)
    {
        $msg = [
            'msg' => $msg,
            'code' => $code,
        ];
        $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
        if(!isset($this->_connections->connections[$this->_id])) {
            Log::log("socket={$this->_id}已经被关闭");
            return false;
        }
        $f = @socket_write($this->_socket, $msg, strlen($msg));
        if($f === false || $f === NULL) {
            Log::log("Thread:[{$this->_id}]发送消息时连接已经断开");
        } else {
            Log::log("Thread:[{$this->_id}]发送数据成功--[{$msg}]");
        }

        if($f === false) {
            Log::log("[{$this->_id}]发送消息失败");
            //其实这里并不是必须进行关闭操作，因为当连接断开时会调用onClose操作来释放资源.
            //同步代码
            $this->_connections->synchronized(function () {
                Log::log("[{$this->_id}]执行同步代码，关闭连接");
                if(isset($this->_connections->connections[$this->_id])) {
                    unset($this->_connections->connections[$this->_id]);
                    @socket_close($this->_socket);
                } else {
                    Log::log("socket={$this->_id}已经被关闭");
                }
            });
        }

        return $f;
    }

    public function sendDbConnectionError($msg)
    {
        $this->sendMsg($msg, Config::ERROR_CODE);
    }

    public function __destruct()
    {
        Log::log("[" . $this->worker->getCreatorId() . "]线程关闭");
    }


}