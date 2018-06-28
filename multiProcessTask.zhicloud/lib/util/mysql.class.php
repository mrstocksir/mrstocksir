<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *   常驻内存服务中基于Pdo连接数据库
 * @author  v.r
 * @package util.pdo 
 * 
 * 注: 辅助方法为私有方法 命名以下滑线开头
 * 
 */



class MySqlDrive
{
    public $debug = false;
    public $conn = null;
    public $config;
    const DEFAULT_PORT = 3306;

    function __construct($db_config)
    {
        if (empty($db_config['port']))
        {
            $db_config['port'] = self::DEFAULT_PORT;
        }
        $this->config = $db_config;
    }

    /**
     * 连接数据库
     *
     * @see Swoole.IDatabase::connect()
     */
    function connect()
    {
        $db_config = $this->config;
        if (empty($db_config['persistent']))
        {
            $this->conn = mysql_connect($db_config['host'] . ':' . $db_config['port'],
                $db_config['user'],
                $db_config['pass']);
        }

        if (!$this->conn) {
            print __CLASS__."[#45]SQL Error". mysql_error($this->conn);
            return false;
        }

        if (!mysql_select_db($db_config['dbname'], $this->conn)) {
            print __CLASS__." [#46]SQL Error". mysql_error($this->conn);
        } 
        
        if ($db_config['charset'])
        {
            if (!mysql_query('set names ' . $db_config['charset'], $this->conn)) {
                print __CLASS__." [#46]SQL Error". mysql_error($this->conn);
            } 
        }
        return true;
    }

    function errorMessage($sql)
    {
        return mysql_error($this->conn) . "<hr />$sql<hr />MySQL Server: {$this->config['host']}:{$this->config['port']}";
    }

    /**
     * 执行一个SQL语句
     *
     * @param string $sql 执行的SQL语句
     *
     * @return MySQLRecord | false
     */
    function query($sql)
    {
        $res = false;
        error_reporting(0);
        for ($i = 0; $i < 4; $i++)
        {

            $res = mysql_query($sql,$this->conn);
            if ($res === false)
            {
                if (mysql_errno($this->conn) == 2006 or mysql_errno($this->conn) == 2013 or (mysql_errno($this->conn) == 0 and !$this->ping()))
                {

                    print "数据库重新连接啦"."\r\n";
                    $r = $this->checkConnection();
                    if ($r === true)
                    {
                        continue;
                    }
                }
               print __CLASS__." [#48]SQL Error". mysql_error($this->conn);
                return false;
            }
            break;
        }

        if (!$res)
        {
            print __CLASS__." [#49]SQL Error". mysql_error($this->conn);
            return false;
        }
        if (is_bool($res))
        {
            return $res;
        }
        return new MySQLRecord($res);
    }

    /**
     * 返回上一个Insert语句的自增主键ID
     * @return int
     */
    function lastInsertId()
    {
        return mysql_insert_id($this->conn);
    }

    function quote($value)
    {
        return mysql_real_escape_string($value, $this->conn);
    }

    /**
     * 检查数据库连接,是否有效，无效则重新建立
     */
    protected function checkConnection()
    {
        if (!@$this->ping())
        {
            $this->close();
            return $this->connect();
        }
        return true;
    }

    function ping()
    {
        if (!mysql_ping($this->conn))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * 获取上一次操作影响的行数
     *
     * @return int
     */
    function affected_rows()
    {
        return mysql_affected_rows($this->conn);
    }

    /**
     * 关闭连接
     *
     * @see libs/system/IDatabase#close()
     */
    function close()
    {
        mysql_close($this->conn);
    }

    /**
     * 获取受影响的行数
     * @return int
     */
    function getAffectedRows()
    {
        return mysql_affected_rows($this->conn);
    }

    /**
     * 获取错误码
     * @return int
     */
    function errno()
    {
        return mysql_errno($this->conn);
    }
}

class MySQLRecord 
{
    public $result;

    function __construct($result)
    {
        $this->result = $result;
    }

    function fetch()
    {
        return mysql_fetch_assoc($this->result);
    }

    function fetchall()
    {
        $data = array();
        while ($record = mysql_fetch_assoc($this->result))
        {
            $data[] = $record;
        }
        return $data;
    }

    function free()
    {
        mysql_free_result($this->result);
    }
}

/*
$db = array (

    'zhiCloudCustoms'=> array(
        'dsn'=>'mysql:dbname=zhiCloudCustoms;host=172.18.10.168',
        'port'=>3306,
        'host'=>'172.18.10.168',
        'user'=>'guest',
        'pass'=>'guest123456',
        'pingtime'=>3600,
        'dbname'=>'zhiCloudCustoms',
        'pconnect'=>0,
        'charset'=>'utf8',
    )
);


$obj = new MySqlDrive($db['zhiCloudCustoms']);
$obj->connect();
$sql ="SELECT uid FROM customs_feature WHERE  pro_id = 3393 LIMIT 30000,10000";
$result = $obj->query();
$result->fetchall();*/


