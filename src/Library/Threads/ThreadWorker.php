<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/2/27
 * Time: 下午5:14
 */

namespace DbPool\Library\Threads;

use DbPool\Db\DbConnection;
use DbPool\Config;
use DbPool\Library\Encrypt\RSA;
use DbPool\Library\Log;


class ThreadWorker extends \Worker
{
    protected static $link;

    protected $_err;

    protected $_lastQueryTime;

    public function __construct()
    {

    }

    public function getConnection()
    {
        if(!self::$link) {
            try {
                self::$link = new DbConnection(Config::$DbInfo);
            } catch (\Exception $e) {
                Log::log("创建数据库连接出错:" . $e->getMessage());
                $this->_err = $e->getMessage();
            }
        }

        return self::$link;
    }

    public function reConnect()
    {
        try {
            self::$link = new DbConnection(Config::$DbInfo);
        } catch (\Exception $e) {
            Log::log("创建数据库连接出错:" . $e->getMessage());
            $this->_err = $e->getMessage();
        }

        return self::$link;
    }

    public function getLastError()
    {
        return $this->_err;
    }

    public function setLastError($msg)
    {
        $this->_err = $msg;
    }

    public function getLastQueryTime()
    {
        return $this->_lastQueryTime;
    }

    public function updateLastQueryTime()
    {
        $this->_lastQueryTime = date('Y-m-d H:i:s');
    }

    public function getRsa()
    {
        return $this->_rsa;
    }

    public function run()
    {

    }



}