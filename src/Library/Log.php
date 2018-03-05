<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/2/24
 * Time: 上午10:52
 */

namespace DbPool\Library;

use DbPool\Config;

class Log
{
    public static function log($msg)
    {
        if(Config::$LogType == 'console') {
            echo $msg . "\n";
        } else {
            $filename = 'log.' . date('Ymd') . '.log';
            file_put_contents($filename, "[" . date('Y-m-d H:i:s') . "]:" . $msg . "\n", FILE_APPEND);
        }
    }
}