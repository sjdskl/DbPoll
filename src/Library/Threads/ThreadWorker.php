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


class ThreadWorker extends \Worker
{
    protected static $link;

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
            }
        }

        return self::$link;
    }

    public function run()
    {

    }



}