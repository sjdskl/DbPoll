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