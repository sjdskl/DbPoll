<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/2/24
 * Time: 上午10:27
 */

namespace DbPool\Library\Threads\Pool;

use DbPool\Config;
use DbPool\Library\Log;
use DbPool\Library\Threads\ThreadWorker;
use DbPool\Library\Threads\OnCheckDbConnection;

class ThreadsPool extends \Pool
{
    public function workerCount()
    {
        if($this->workers) {
            return count($this->workers);
        }

        return 0;
    }

    public function heartBeatCheck()
    {
        if($this->workers) {
            $now = time();
            /** @var ThreadWorker $worker */
            foreach($this->workers as $idx => $worker) {
                Log::log("最后查询时间：" . $worker->getLastQueryTime());
                $lastQueryTime = $worker->getLastQueryTime();
                if($now - intval(strtotime($lastQueryTime)) >= Config::$HeartBeatTime) {
                    //发送心跳监测请求任务
                    Log::log("投递数据库连接检查任务");
                    $this->submitTo($idx, new OnCheckDbConnection());
                } else {
                    Log::log("[$idx]worker时间未到，不投递任务,last=" . $lastQueryTime . "--now=" . date('Y-m-d H:i:s'));
                }
            }
        }
    }

    public function getPoolInfo()
    {
        $result = [
            'used_count' => 0,
            'max_count'   => $this->size,
            'last_use_index' => $this->last,
            'works' => [],
        ];
        if($this->workers) {
            $result['used_count'] = count($this->workers);
            foreach($this->workers as $idx => $worker) {
                $result[$idx] = [
                    'status' => $worker->getStacked() > 0 ? true:false,
                    'queue' => $worker->getStacked(),
                ];
            }
        }

        return $result;
    }

}