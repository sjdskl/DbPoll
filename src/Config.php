<?php
/**
 * 我们配置文件，用户的配置文件需要继承此类
 * User: kailishen
 * Date: 2018/2/24
 * Time: 上午10:53
 */

namespace DbPool;

class Config
{
    const SUCCESS_CODE = 1000;

    const ERROR_CODE = 2001;

    /**
     * 日志类型
     * console 输出
     * @var string
     */
    public static $LogType = 'console';

    /**
     * 心跳校测时间
     * @var int
     */
    public static $HeartBeatTime = 900;

    /**
     * 普通连接池大小
     * @var int
     */
    public static $PoolSize = 5;

    /**
     * 事务连接池大小
     * @var int
     */
    public static $TransPoolSize = 5;

    /**
     * 事务开始标记
     * @var int
     */
    public static $TransStart = 1;

    /**
     * 事务结束标记
     * @var int
     */
    public static $TransEnd = 2;

    /**
     * 数据库连接信息
     * @var array
     */
    public static $DbInfo = [
        'dbname' => 'test',
        'host' => '127.0.0.1',
        'charset' =>'utf8',
        'username' => 'root',
        'password' => 'sklmac123',
    ];

}