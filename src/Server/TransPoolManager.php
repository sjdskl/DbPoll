<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/3/7
 * Time: 下午2:10
 */

namespace DbPool\Server;

use DbPool\Library\Log;


class TransPoolManager extends \Threaded
{
    protected $_threadQueue = [];

    protected $_map;

    protected $_initInfo = [];

    public function __construct($size)
    {
        for($i = 0; $i < $size; $i ++) {
            $this->_threadQueue[] = $i;
        }
        $this->_map = [];
    }

    public function push($socket) {
        $id = (int)$socket;
        $idx = isset($this->_map[$id]) ? $this->_map[$id] : '';
        if($idx !== '') {
            unset($this->_map[$id]);
            $this->_threadQueue[] = $idx;
        }
    }

    public function isInited($idx)
    {
        return isset($this->_initInfo[$idx]);
    }

    public function inited($idx)
    {
        $this->_initInfo[$idx] = true;
    }

    public function poop($socket)
    {
        $id = (int)$socket;
        if(isset($this->_map[$id])) {
            return $this->_map[$id];
        }
        if($this->_threadQueue->count()) {
            $idx = $this->_threadQueue->shift();
            $this->_map[$id] = $idx;
            return $idx;
        }

        return false;
    }

    public function leave()
    {
        return $this->_threadQueue->count();
    }

    public function used()
    {
        return count($this->_map);
    }
}