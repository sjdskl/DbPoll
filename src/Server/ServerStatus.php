<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/3/14
 * Time: 下午5:52
 */

namespace DbPool\Server;


use DbPool\Library\Log;
use DbPool\Library\Threads\Pool\ThreadsPool;

class ServerStatus extends \Threaded
{
    protected $_status = [];

    /**
     * @param ThreadsPool $pool
     * @param ThreadsPool $transPool
     */
    public function setServerStatus($pool, $transPool)
    {
        $this->_status['pool'] = $pool->getPoolInfo();
        $this->_status['transPool'] = $transPool->getPoolInfo();
        Log::log("设置status:" . json_encode($this->_status));
    }

    public function getServerStatus()
    {
        return $this->_status;
    }
}