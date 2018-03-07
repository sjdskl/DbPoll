<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/2/24
 * Time: 上午10:27
 */

namespace DbPool\Library\Threads\Pool;

class ThreadsPool extends \Pool
{
    public function workerCount()
    {
        if($this->workers) {
            return count($this->workers);
        }

        return 0;
    }
}