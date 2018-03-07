<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/2/24
 * Time: 下午3:50
 */

namespace DbPool\Library\Threads;
use DbPool\Library\Log;


class OnClose extends BaseThreaded
{
    public function run()
    {
        Log::log("{$this->_id}线程onclose执行中...");

        if($this->_msg === true) {
            $this->worker->getConnection()->getPdo()->rollBack();
            Log::log("未正确提交事务，回滚:");
        }

    }
}