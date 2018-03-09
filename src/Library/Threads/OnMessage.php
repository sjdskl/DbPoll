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
        if(!$db) {
            $this->sendDbConnectionError($this->worker->getLastError());
            return;
        }

        $ret = json_decode(json_encode($this->_msg), true);;
        $obj = $db;
        try {
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

                //查询失败异常处理
                if($obj === false) {
                    $error_info = $db->error();
                    Log::log("执行出错，error=" . $error_info);
                    //如果是服务器断开连接，则尝试重新连接服务器
                    if(isset($error_info[1]) && $error_info[1] = 2006) {
                        //断线重新连接
                        $db = $this->worker->reConnect();
                        $data = $db->query('select 1;');
                        if($data === false) {
                            $error_info = $db->error();
                            Log::log("重连服务器失败，msg=" . $error_info);
                        } else {
                            //如果重连成功，则重新执行
                            continue;
                        }
                    }
                    $this->sendDbConnectionError($error_info);
                    return;
                }

                $ret = $ret['results'];
            } while($ret);
        } catch (\Exception $e) {

        }

        $this->worker->updateLastQueryTime();

        Log::log("数据库ID=" . $db->id);

        $data = $obj;

        $f = $this->sendMsg($data);

    }
}