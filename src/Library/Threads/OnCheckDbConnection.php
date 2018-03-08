<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/3/8
 * Time: 下午1:56
 */

namespace DbPool\Library\Threads;

use DbPool\Library\Log;
use DbPool\Server\Connections;

class OnCheckDbConnection extends BaseThreaded
{
    public function __construct()
    {

    }

    public function run()
    {
        Log::log("开始监测数据库连接");

        $db = $this->worker->getConnection();

        try {
            $data = $db->query('select 1;');
            Log::log("心跳监测结果:" . var_export($data, true));
            if($data === false) {
                $error_info = $db->error();
                Log::log("执行心跳监测出错，msg=" . $error_info);
                if(isset($error_info[1]) && $error_info[1] = 2006) {
                    //断线重新连接
                    $db = $this->worker->reConnect();
                    $data = $db->query('select 1;');
                    if($data === false) {
                        $error_info = $db->error();
                        Log::log("执行心跳监测出错,并且重连失败，msg=" . $error_info);
                    }
                }
                $this->worker->setLastError($error_info);
            } else {
                //TODO onconnect 是没有查询数据库的，onclose也不一定会查询，可以直接在onmessage中添加
                //成功情况下更新query时间
                $this->worker->updateLastQueryTime();
            }
        } catch (\Exception $e) {
            Log::log("执行心跳监测出错，msg=" . $e->getMessage());
        }
    }
}