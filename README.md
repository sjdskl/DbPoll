# DbPoll
Based on multi threads Database link pools
1. pthreads extension
2. php >= 7.2

## Featrues
1. DB Driver used catfan/medoo, you can call its method at client just like local call
2. Chain Call
3. very easy to use
```$xslt
$client = DbPool\Client\DbPoolClient::getInstance('127.0.0.1', AF_INET, 1122);
for($i = 0; $i < 10; $i ++) {
    $res = $client->query('select * from test.bairong where id=' . ($i + 1) . ' limit 1;')->fetchAll(\PDO::FETCH_ASSOC)->excute();
    print_r($res);
}
```
```
$res = $client->select('bairong', ['id', 'realname', 'phone'], ['id[<=]' => 10])->excute();
```
4. Two link code you can run up server
```$xslt
$server = new \DbPool\Server\DbPoolServer('127.0.0.1', AF_INET, 1122);

//$server = new \DbPool\Server\DbPoolServer('/tmp/skl.sock', AF_UNIX);

$server->loop();
```
5.Support Transcation
```$xslt
$client->action(function() use ($client) {
    $client->update('bairong', ['realname' => '你大爷xxxx'], [
        'id' => 1,
    ])->excute();
    //true -> ommit，false -> rollback, just like medoo
    return true;
});
```
6.Support DB Link Heartbeat
```$xslt
public static $HeartBeatTime = 600;
public static $HeartBeatCheckTime = 60;
```
7.Custom Message Event
8.Support AES And RSA Encrypt
    