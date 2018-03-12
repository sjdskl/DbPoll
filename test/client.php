<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/2/22
 * Time: 上午11:23
 */
include_once '../vendor/autoload.php';

//$a = new \DbPool\Library\Encrypt\RSA(\DbPool\Config::$ServerPrivateKey, \DbPool\Config::$ClientPublicKey);
//$b =  new \DbPool\Library\Encrypt\RSA(\DbPool\Config::$ClientPrivateKey, \DbPool\Config::$ServerPublicKey);
//
//$c = $b->rsaEncrypt('111');
//\DbPool\Library\Log::log("c=" . $c);
//$d = $a->rsaDecrypt($c);
//\DbPool\Library\Log::log('d=' . $d);
//
//exit;



//$pool1 = new \Pool(5);
//$pool = new \Pool(5);
//
//for($i = 0; $i < 5; $i ++) {
//    //用空线程方法来初始化事务线程池，pool对象自己不会初始化
//    $pool->submit(new \Threaded());
//}
//
//$pool->submitTo(0, new \Threaded());exit;

//$a = new SplQueue();
//
//$a->push(1);
//$a->push(2);
//$a->push(3);
//foreach($a as $item) {
//    echo $item;
//}
//echo $a->count() . "---\n";
//$a->shift();
//echo $a->count() . "---\n";
//exit;

$client = DbPool\Client\DbPoolClient::getInstance('127.0.0.1', AF_INET, 1122);

//事务测试
$client->action(function() use ($client) {
    $client->update('bairong', ['realname' => '你大爷222'], [
        'id' => 1,
    ])->excute();

    return true;
});

for($i = 0; $i < 10000; $i ++) {
    $res = $client->query('select * from test.bairong where id=' . ($i + 1) . ' limit 1;')->fetchAll(\PDO::FETCH_ASSOC)->excute();
    if($res === false) {
        echo $client->getLastError() . "\n";
    } else {
        print_r($res);
    }
}
//sleep(5);

$res = $client->select('bairong', ['id', 'realname', 'phone'], ['id[<=]' => 10])->excute();
print_r($res);





exit;


$arr = [];
for($i = 0; $i < 10; $i ++) {
    $r = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

    $f = socket_connect($r, '127.0.0.1', '1122');

    if(!$f) {
        die('连接失败:' . socket_strerror(socket_last_error($r)));
    }

//    $str = '你好中国' . mt_rand(1, 10000);
    $str = json_encode([
        'method' => 'query',
        'params' => 'select * from test.bairong limit 1;',
        'results'=> [
            'method' => 'fetchAll',
            'params' => '',
            'results'=> ''
        ]
    ]) . '\r\n\r\n';

    echo "发送消息:" . $str . "\n";

    $f = socket_write($r, $str, strlen($str));
    if(!$f) {
        die('发送失败:' . socket_strerror(socket_last_error($r)));
    }
    $data = socket_read($r, 1024);
    echo $data . "\n";

//    while($data = socket_read($r, 1024)) {
//        echo $data;
//        if(stripos($data, "\n") !== false) {
//            break;
//        }
//    }

    $arr[] = $r;
}

//sleep(20);


//$str .= "第二次";

foreach($arr as $r) {

//    socket_write($r, $str, strlen($str));
//
////    socket_close($r);
//
//    $data = socket_read($r, 1024);
//    echo $data . "\n";

    socket_close($r);
}

