<?php

//是否长驻服务 (swoole模式)
define('IS_LONG_SERVER',1);
class Factory
{
    /**
     * @var Pdo
     */
    private static $instance = [];

    /**
     * db驱动工厂
     * @param null $config
     * @param null $className
     * @param null $dbName
     * @return Pdo
     * @throws \Exception
     */
    public static function DB($config=null,$uq=null,$className=null, $dbName=null)
    {

        $_as = md5($config['dsn'].$uq);
        if(empty(self::$instance[$_as])) 
            self::$instance[$_as] = new PdoDrive($config);

        if (!empty(self::$instance[$_as]))
            self::$instance[$_as]->ping();

        
        if ($className) 
           self::$instance[$_as]->setClassName($className);

        if ($dbName) 
            self::$instance[$_as]->setDBName($dbName);

        return self::$instance[$_as];
    }

     public static function NewDB($config=null,$uq=null,$className=null, $dbName=null)
    {
        $db = new MySqlDrive($config);
        $db->connect();
        return $db;

        $_as = md5($config['dsn'].$uq);
        if (empty(self::$instance[$_as])) {
            self::$instance[$_as] = new MySqlDrive($config);
            self::$instance[$_as]->connect();
        }
        
/*        if (!empty(self::$instance[$_as]))
            self::$instance[$_as]->ping();*/

        
        if ($className) 
           self::$instance[$_as]->setClassName($className);

        if ($dbName) 
            self::$instance[$_as]->setDBName($dbName);

        return self::$instance[$_as];
    }


    /**
     * 模型工厂
     * @param null $config
     * @param null $className
     * @param null $dbName
     * @return Pdo
     * @throws \Exception
     */
    public static function model($className = NULL,$uq = NULL) {
       $file = ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'model'.DIRECTORY_SEPARATOR.$className.'.class.php';

       require_once $file;
       return new $className($uq);

        if (empty($className)) 
            throw new \Exception("Model name cannot be empty", 1);

        $_as = md5($className.$uq);

        if (!empty(self::$instance[$_as])) {
        /*    self::$instance[$_as]->restart($uq);*/
            return self::$instance[$_as];
        }

        $file = ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'model'.DIRECTORY_SEPARATOR.$className.'.class.php';

        if (!is_file($file)) 
            throw new \Exception("The model class does not exist. Check the file name", 1);

       require_once $file;
        self::$instance[$_as] = new $className($uq);

   //     return new $className;
        return  self::$instance[$_as];
    }

}