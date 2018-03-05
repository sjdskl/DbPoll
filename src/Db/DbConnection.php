<?php

namespace DbPool\Db;

use Medoo\Medoo;

class DbConnection
{
    protected $_connection;

    public $id;

    public function __construct($db_config, $prefix = '')
    {
        $_db_host = $db_config['host'];
        $_db_name = $db_config['dbname'];
        $_db_charset = $db_config['charset'];
        $_db_usr = $db_config['username'];
        $_db_password = $db_config['password'];

        $this->_connection = new Medoo([
            // required
            'database_type' => 'mysql',
            'database_name' => $_db_name,
            'server' => $_db_host,
            'username' => $_db_usr,
            'password' => $_db_password,
            'charset' => $_db_charset,
            'port' => isset($db_config['port']) ? $db_config['port'] : 3306,
            'prefix' => $prefix,
            'option' => array(\PDO::ATTR_PERSISTENT => false)
        ]);

        $this->id = mt_rand(1, 10000000);
    }

    public function getPdo()
    {
        return $this->_connection->pdo;
    }

    public function getConnection()
    {
        return $this->_connection;
    }

    public function __call($name, $arguments)
    {
        return $this->_connection->$name(...$arguments);
    }
}
