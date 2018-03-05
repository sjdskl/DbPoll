<?php
/**
 * 连接存储类.
 * User: kailishen
 * Date: 2018/2/24
 * Time: 上午11:24
 */

namespace DbPool\Server;

use DbPool\Exception\ParamsErrorException;

class Connections extends \Threaded
{
    public $connections = [];

    public $mutex = 0;

    public function toArray()
    {
        return (array) $this->connections;
    }

    public function count()
    {
        return count($this->connections);
    }

    public function getConnections()
    {
        return $this->connections;
    }

    public function addConnection($socket, $id)
    {
        if(isset($this->connections[$id])) {
            throw  new ParamsErrorException("参数错误,已存在对应ID的连接");
        }

        $this->connections[$id] = $socket;
    }

    public function delConnection($id)
    {
        if(isset($this->connections[$id])) {
            unset($this->connections[$id]);
        }
    }

}