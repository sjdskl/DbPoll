<?php
/**
 * 连接池服务端
 * User: kailishen
 * Date: 2018/2/24
 * Time: 上午9:50
 */

namespace DbPool\Server;

use DbPool\Config;
use DbPool\Exception\ParamsErrorException;
use DbPool\Library\Log;
use DbPool\Library\Threads\OnClose;
use DbPool\Library\Threads\OnConnect;
use DbPool\Library\Threads\OnMessage;
use DbPool\Library\Threads\Pool\ThreadsPool;
use DbPool\Db\DbConnection;
use DbPool\Library\Protocol\SqlProtocol;

class DbPoolServer
{
    protected $_socket;

    protected $_address;

    protected $_port;

    protected $_connections;

    protected $_pool;

    protected $_used_connections;

    protected $_queue;

    protected $_db;

    public function __construct($address, $domain = AF_INET, $port = 1122)
    {
        $this->_socket = @socket_create($domain, SOCK_STREAM, $domain == AF_INET ? SOL_TCP:0);
        if($domain == AF_INET) {
            if(!$port) {
                throw new ParamsErrorException("参数错误, 缺少端口");
            }
            if(!socket_bind($this->_socket, $address, $port)) {
                throw new \Exception('绑定失败');
            }
        } else if($domain == AF_UNIX) {
            if(!socket_bind($this->_socket, $address)) {
                throw new \Exception('绑定失败');
            }
        }

        if(!socket_listen($this->_socket, 0)) {
            throw new \Exception("监听失败");
        }

        $this->_db = new DbConnection(Config::$DbInfo);
        $this->_connections = new Connections();
        $this->_pool = new ThreadsPool(Config::$PoolSize, '\DbPool\Library\Threads\ThreadWorker');
    }

    protected function _close($socket)
    {
        $id = (int)$socket;
        if(is_resource($socket)) {
            //同步关闭
            $this->_connections->synchronized(function() use ($id, $socket) {
                unset($this->_connections->connections[$id]);
                @socket_close($socket);
            });
            Log::log("{$id}连接已断开");
        }
    }

    public function loop()
    {
        $protocol = new SqlProtocol();
        while(true) {
            $read = array_merge($this->_connections->toArray(), [$this->_socket]);
            $write = $except = null;
            $ret = socket_select($read, $write, $except, 1, 0);
            if($ret < 1) {
                continue;
            }
            //判断是否有新连接进来
            //onConnect
            if(in_array($this->_socket, $read)) {
                $nfd = socket_accept($this->_socket);
                $this->_connections->connections[(int)$nfd] = $nfd;
                socket_getpeername($nfd, $ip);
                Log::log( "new client ip:" . $ip);
                $protocol->initMsg((int)$nfd);
                $this->_pool->submit(new OnConnect($this->_connections, $nfd, (int)$nfd, ''));
            }

            if($read) {
                foreach($read as $rfd) {
                    if($rfd === $this->_socket) {
                        continue;
                    }
                    $f = socket_recv($rfd, $msg, 65535, MSG_DONTWAIT);
                    if($f === false || $f === NULL || $f === 0) {
                        $this->_close($rfd);
                        $this->_pool->submit(new OnClose($this->_connections, $rfd, (int)$rfd, ''));
                        continue;
                    }
                    if($msg) {
                        $protocol->setMsg($msg, (int)$rfd);
                        $row = $protocol->get((int)$rfd);
                        $this->_pool->submit(new OnMessage($this->_connections, $rfd, (int)$rfd, $row));
                    }
                }
            }

        }
    }

}