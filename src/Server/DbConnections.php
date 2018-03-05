<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/2/26
 * Time: 上午10:23
 */

namespace DbPool\Server;

use DbPool\Db\DbConnection;
use DbPool\Config;
use DbPool\Library\Log;

class DbConnections extends \Threaded
{
    public $connections = [];

    public $mutex = 0;

    public function toArray()
    {
        return (array)$this->connections;
    }

    public function count()
    {
        return count($this->connections);
    }

    public function getConnections()
    {
        return $this->connections;
    }


    public function delConnection($idx)
    {
        if (isset($this->connections[$idx])) {
            unset($this->connections[$idx]);
        }
    }

    public function createDbConnections($count = 1)
    {
        for($i = 0; $i < $count; $i ++) {
            try {
                $conn = new DbConnection(Config::$DbInfo);
                $this->connections[] = $conn;
            } catch (\Exception $e) {
                Log::log("创建数据库连接出错:" . $e->getMessage());
            }
        }
    }
}