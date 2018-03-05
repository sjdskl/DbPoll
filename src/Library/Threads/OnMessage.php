<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/2/24
 * Time: 上午11:11
 */

namespace DbPool\Library\Threads;

use DbPool\Library\Log;
use Medoo\Medoo;

class OnMessage extends BaseThreaded
{
    public function run()
    {
        Log::log("{$this->_id}线程onmessage执行中...");
        Log::log("[{$this->_id}]收到数据:" . json_encode($this->_msg, JSON_UNESCAPED_UNICODE));

        if(!$this->_msg) {
            $this->sendMsg('发送数据不正确');
            return;
        }

        $db = $this->worker->getConnection();

        $ret = json_decode(json_encode($this->_msg), true);;
        $obj = $db;
        do {
            $method = $ret['method'];
            $params = (array)$ret['params'];
            if(is_array($params)) {
                $obj = $obj->$method(...$params);
            } else if($params) {
                $obj = $obj->$method($params);
            } else {
                $obj = $obj->$method();
            }
            $ret = $ret['results'];
        } while($ret);

        Log::log("数据库ID=" . $db->id);

        $data = $obj;

        $f = $this->sendMsg($data);

    }
}