<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/2/24
 * Time: 下午3:42
 */

namespace DbPool\Library\Threads;
use DbPool\Library\Log;


class OnConnect extends BaseThreaded
{
    public function run()
    {
        Log::log("{$this->_id}线程onconnect执行中...");

    }
}