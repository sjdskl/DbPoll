<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/2/24
 * Time: 上午10:53
 */

namespace DbPool;

class Config
{
    public static $LogType = 'console';

    public static $PoolSize = 50;

    public static $DbPoolSize = 5;

    public static $DbInfo = [
        'dbname' => 'test',
        'host' => '127.0.0.1',
        'charset' =>'utf8',
        'username' => 'root',
        'password' => 'sklmac123',
    ];

}