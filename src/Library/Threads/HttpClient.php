<?php
/**
 * http消息处理线程
 * User: kailishen
 * Date: 2018/3/13
 * Time: 下午9:37
 */

namespace DbPool\Library\Threads;


use DbPool\Config;
use DbPool\Library\Log;
use DbPool\Server\Connections;
use DbPool\Library\Protocol\HttpProtocol;
use DbPool\Server\ServerStatus;

class HttpClient extends \Threaded
{
    protected $_socket;

    protected $_connections;

    /** @var ServerStatus $_serverStatus */
    protected $_serverStatus;

    public function __construct($serverStatus)
    {
        $this->_serverStatus = $serverStatus;
    }

    public function _close($socket)
    {
        $id = (int)$socket;
        if(is_resource($socket)) {
            if(isset($this->_connections->connections[$id])) {
                unset($this->_connections->connections[$id]);
                @socket_close($socket);
                Log::log("关闭HTTP连接");
            }
        }
    }

    public function _write($socket, $msg)
    {
        if(strlen($msg) > 65535) {
            $msg_arr = str_split($msg, 65535);
            foreach($msg_arr as $m) {
                @socket_write($socket, $m, strlen($m));
            }
        } else {
            @socket_write($socket, $msg, strlen($msg));
        }
    }

    public function run()
    {
        $this->_connections = new Connections();
        $this->_socket = @socket_create(AF_INET, Config::$HttpBindAddress, SOL_TCP);
        if(!socket_bind($this->_socket, Config::$HttpBindAddress, Config::$HttpPort)) {
            throw new \Exception('HTTP绑定失败');
        }
        if(!socket_listen($this->_socket, 0)) {
            throw new \Exception("HTTP端口监听失败");
        }

        while(true) {
            $read = array_merge($this->_connections->toArray(), [$this->_socket]);
            $write = $except = null;
            $ret = socket_select($read, $write, $except, 1, 0);
            if($ret < 1) {
                continue;
            }
            if(in_array($this->_socket, $read)) {
                $nfd = socket_accept($this->_socket);
                $this->_connections->connections[(int)$nfd] = $nfd;
                socket_getpeername($nfd, $ip);
                Log::log( "new http client ip:" . $ip);
            }

            if($read) {
                foreach($read as $rfd) {
                    if($rfd === $this->_socket) {
                        continue;
                    }
                    $f = @socket_recv($rfd, $msg, 65535, MSG_DONTWAIT);
                    Log::log("HTTP内存占用:" . floor(memory_get_usage() / 1024 /1024) . "M");
                    if($f === false || $f === NULL || $f === 0) {
                        $this->_close($rfd);
                        continue;
                    }
                    if($msg) {
                        HttpProtocol::decode($msg, $nfd);
                        Log::log("HTTP请求地址:" . $_SERVER['REQUEST_URI']);

                        ob_start();
                        $t = $this->_serverStatus->getServerStatus();
                        print_r($t);
                        $data = ob_get_contents();
                        ob_end_clean();

                        $content = HttpProtocol::encode($data, $nfd);
                        $this->_write($rfd, $content);
                    }

                    $this->_close($rfd);
                }
            }
        }
    }
}