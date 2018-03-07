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
use function foo\func;

ini_set("memory_limit", '128M');
//或者执行 export USE_ZEND_ALLOC=0
//防止gc导致进程挂掉
gc_disable();

class DbPoolServer
{
    protected $_socket;

    protected $_address;

    protected $_port;

    protected $_connections;

    protected $_pool;

    protected $_transPool;

    protected $_used_connections;

    protected $_queue;

    protected $_db;

    protected $_sqlProtocol;

    protected $_threadQueueManager;

    protected $_waitTransPool= [];

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
        $this->_transPool = new ThreadsPool(Config::$TransPoolSize, '\DbPool\Library\Threads\ThreadWorker');
        Log::log("worker队列数量:" .$this->_transPool->workerCount());
        $this->_threadQueueManager = new TransPoolManager(Config::$TransPoolSize);
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
            $this->_sqlProtocol->remove($id);
            //如果存在等待队列，则关闭
            if(isset($this->_waitTransPool[$id])) {
                $this->_waitTransPool[$id];
            }

        }
    }

    protected function submitToThreadPool($obj, $socket, $trans = false, $step = '')
    {
        //事务独占一个work
        if($trans) {
            //同步执行代码
            $this->_threadQueueManager->synchronized(function() use ($obj, $socket, $step, $trans) {
                $idx = $this->_threadQueueManager->poop($socket);
                Log::log("分配到[$idx]编号worker");
                if($idx === false) {
                    //当连接还存在
                    if($socket) {
                        $this->_waitTransPool[(int) $socket] = [$obj, $socket, $trans, $step,];
                    }
                    Log::log('线程池不够用,放入队列中');
                    return false;
                }
                if($step == Config::$TransStart) {
                    if(!$this->_threadQueueManager->isInited($idx)) {
                        $this->_transPool->submit($obj);
                        $this->_threadQueueManager->inited($idx);
                    } else {
                        $this->_transPool->submitTo($idx, $obj);
                    }
                } else if($step == Config::$TransEnd) {
                    $this->_transPool->submitTo($idx, $obj);
                    $this->_threadQueueManager->push($socket);
                    Log::log("将连接放回连接池:" . $idx);
                }
                return true;
            });
        } else {
            $this->_pool->submit($obj);
        }
    }

    public function pushWaitTransThread()
    {
        $c = count($this->_waitTransPool);
        //将等待中的事务尝试推送到线程池中执行
        if($c) {
            for($i = 0; $i < $c; $i ++) {
                $item = array_shift($this->_waitTransPool);
                $f = $this->submitToThreadPool(...$item);
                if(!$f) {
                    Log::log("暂时事务线程池还没有空余位置");
                } else {
                    Log::log("投递成功");
                }
            }
        }
    }

    public function loop()
    {
        $this->_sqlProtocol = new SqlProtocol();
        while(true) {
            $read = array_merge($this->_connections->toArray(), [$this->_socket]);
            $write = $except = null;
            $ret = socket_select($read, $write, $except, 1, 0);
            $this->pushWaitTransThread();
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
                $this->_sqlProtocol->initMsg((int)$nfd);
                $this->submitToThreadPool(new OnConnect($this->_connections, $nfd, (int)$nfd, ''), $nfd);
//                $this->_pool->submit(new OnConnect($this->_connections, $nfd, (int)$nfd, ''));
            }

            if($read) {
                foreach($read as $rfd) {
                    if($rfd === $this->_socket) {
                        continue;
                    }
                    $f = @socket_recv($rfd, $msg, 65535, MSG_DONTWAIT);
                    Log::log("内存占用:" . floor(memory_get_usage() / 1024 /1024) . "M");
                    if($f === false || $f === NULL || $f === 0) {
                        $inTrans = $this->_threadQueueManager->inTrans($rfd);
                        $this->_close($rfd);
                        $this->submitToThreadPool(new OnClose($this->_connections, $rfd, (int)$rfd, $inTrans), $rfd, $inTrans, Config::$TransEnd);
//                        $this->_pool->submit(new OnClose($this->_connections, $rfd, (int)$rfd, ''));
                        continue;
                    }
                    if($msg) {
                        $this->_sqlProtocol->setMsg($msg, (int)$rfd);
                        Log::log("消息队列数量:" . $this->_sqlProtocol->count((int)$rfd));
                        //一次都推送过去
                        for($i = 0; $i < $this->_sqlProtocol->count((int)$rfd); $i ++) {
                            $row = $this->_sqlProtocol->get((int)$rfd);
                            $trans = false;
                            if($row['trans'])  {
                                $trans = true;
                            }
                            $this->submitToThreadPool(new OnMessage($this->_connections, $rfd, (int)$rfd, $row), $rfd, $trans, $row['trans']);
//                            $this->_pool->submit(new OnMessage($this->_connections, $rfd, (int)$rfd, $row));
                        }
                    }
                }
            }

            while ($this->_pool->collect());

        }

        $this->_pool->shutdown();
    }

}