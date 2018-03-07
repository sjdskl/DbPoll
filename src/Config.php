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
    const SUCCESS_CODE = 1000;

    const ERROR_CODE = 2001;

    public static $LogType = 'console';

    public static $PoolSize = 5;

    public static $TransPoolSize = 5;

    public static $TransStart = 1;

    public static $TransEnd = 2;

    public static $DbInfo = [
        'dbname' => 'test',
        'host' => '127.0.0.1',
        'charset' =>'utf8',
        'username' => 'root',
        'password' => 'sklmac123',
    ];

}