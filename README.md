# DbPoll
基于多线程的数据库连接池
1. 需要安装pthreads扩展
2. php >= 7.2

## 特性
1. 数据库连接采用medoo，在client可直调用medoo中的方法。
2. 链式调用  
3. 就像写本地代码一样写查询
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

4.服务端两行代码搞定
```$xslt
$server = new \DbPool\Server\DbPoolServer('127.0.0.1', AF_INET, 1122);

//$server = new \DbPool\Server\DbPoolServer('/tmp/skl.sock', AF_UNIX);

$server->loop();
```
5.支持事务,事务与普通查询分开配置


## TODO
1.~~数据库事务~~  
2.服务端可以自定义事件，onmessage，onconnect，onclose  
3.多库配置  
4.读写分离配置  