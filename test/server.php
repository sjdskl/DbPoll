<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/2/24
 * Time: 下午4:09
 */

include "../vendor/autoload.php";

$server = new \DbPool\Server\DbPoolServer('127.0.0.1', AF_INET, 1122);

//$server = new \DbPool\Server\DbPoolServer('/tmp/skl.sock', AF_UNIX);

$server->loop();