<?php
/**
 * 数据库协议流解析
 * User: kailishen
 * Date: 2018/3/5
 * Time: 下午2:55
 */
namespace DbPool\Library\Protocol;

use DbPool\Library\Log;

class SqlProtocol
{
    //协议分割流
    const DELIMITER = '\r\n\r\n';

    /** @var SplQueue $_msgQueue */
    protected $_msgQueue = [];

    protected $_msg = [];

    public function __construct()
    {

    }

    public function setMsg($msg, $id) {
        Log::log('设置消息:' . $msg);
        if(!isset($this->_msg[$id])) {
            $this->_msg[$id] = '';
            $this->_msgQueue[$id] = new \SplQueue();
        }
        $this->_msg[$id] .= $msg;
        $t = explode(self::DELIMITER, $this->_msg[$id]);
        if(count($t) > 1) {
            for($i = 0; $i < count($t) - 1; $i ++) {
                $this->_msgQueue[$id]->push($t[$i]);
            }
            //边界条件检查，如果正好是最后一个数据
            $json = json_decode($t[$i], true);
            if($json !== NULL && $json !== false) {
                $this->_msgQueue[$id]->push($t[$i]);
                $this->_msg[$id] = '';
            } else {
                $this->_msg[$id] = $t[$i];
            }
        }
    }

    public function get($id)
    {
        //先进先出
        if($this->count($id)) {
            $json = $this->_analysis($this->_msgQueue[$id]->shift());
            return $json;
        }

        return false;
    }

    public function count($id)
    {
        return $this->_msgQueue[$id]->count();
    }

    protected function _analysis($msg)
    {
        $json = json_decode($msg, true);
        if(!$json) {
            Log::log("协议数据出错,msg=" . $msg);
            return false;
        }

        if(!isset($json['method']) || !isset($json['params'])) {
            Log::log("协议格式出错,msg=" . $msg);
            return false;
        }

        return $json;
    }



}