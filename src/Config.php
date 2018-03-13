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
    public static $HeartBeatTime = 600;

    /**
     * 心跳监测时间投递时间间隔
     * @var int
     */
    public static $HeartBeatCheckTime = 60;

    /**
     * 是否加密传输数据
     * @var bool
     */
    public static $Encrypt = true;

    /**
     * 加密类型
     * @var string
     */
    public static $EncryptType = 'AES';//RSA

    /**
     * RSA加密公私钥地址
     * @var string
     */
    public static $ServerPrivateKey = '/Users/kailishen/PhpstormProjects/DbPoll/src/Keys/server_private.pem';
    public static $ServerPublicKey = '/Users/kailishen/PhpstormProjects/DbPoll/src/Keys/server_public.pem';
    public static $ClientPrivateKey = '/Users/kailishen/PhpstormProjects/DbPoll/src/Keys/client_private.pem';
    public static $ClientPublicKey = '/Users/kailishen/PhpstormProjects/DbPoll/src/Keys/client_public.pem';

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