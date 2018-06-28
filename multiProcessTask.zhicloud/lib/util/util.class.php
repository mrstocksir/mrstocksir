<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *  任务 -多进程任务工具库 
 * @author v.r
 * @package         
 * @subpackage      lib.util.protocol.class.php
 */ 

class Util{
    /**
     * [自动加载类]
     * @return [type] [description]
     * 
     */
    public static function _loadOfClassPhp($class) {
        if (false !== stripos($class, 'Task')) {            
            $file = ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'task'.DIRECTORY_SEPARATOR.$class.'.class.php';
         
            if (is_file($file)) { 
                 require_once $file;
            } else {
                Util::_writeLog("解析类（{$class}）__task__文件不存在"); 
                exit;
            }
        }
        
        if (false !== stripos($class, 'Component')) {            
            $file = ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'component'.DIRECTORY_SEPARATOR.$class.'.class.php';
            if (is_file($file)) { 
                require_once $file;
            } else {
                Util::_writeLog("解析类（{$class}）__component__文件不存在"); 
                exit;
            }
        }

    }
    public static function makeRandomValue($bit = 8){
        if ($bit < 6) $bit = 6; 
        $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $salt = '';
        for ($i = 0; $i < $bit; $i++) {
            $salt .= substr($string, mt_rand(0,61), 1);
        }
        return $salt;
    }

       /**
     * 
     *  生成json协议数据
     *  
     * @param  array   $_mucode 消息唯一码 
     * @param  string  $uuid    (YCSCODE+uid)
     * @param  string  $alias    别名（理解为类型）
     * @return jsonObj
     * 
     */
    public static function __makeJsonProtocol($_mucode = NULL,$uuid = NULL,$alias = NULL) {
        return json_encode(array('_mucode'=>$_mucode,'uuid'=>$uuid,'alias'=>$alias));
    }
    

    /**
     * 
     *  生成json协议数据
     *  
     * @param  array   $_mucode 消息唯一码 
     * @param  string  $uuid    (YCSCODE+uid)
     * @param  string  $alias    别名（理解为类型）
     * @param  string  $zptNoticetype    别名（理解为类型）
     * @return jsonObj
     * 
     */
    public static function __makeZqtJsonProtocol($_mucode = NULL,$uuid = NULL,$alias = NULL,$zptNoticetype = NULL) {
        return json_encode(array('_mucode'=>$_mucode,'uuid'=>$uuid,'alias'=>$alias,'zqtNoticetype'=>$zptNoticetype));
    } 

    /**
     * [makeJsonToArray json换行为数组 递归]
     * @param  [type] $json [description]
     * @return [type]       [description]
     */
    public static function makeObjToArray($json){
        $json = is_object($json) ? get_object_vars($json):$json;
        $arr = array();
        $val = '';
        foreach ($json as $key => $value) {
              if (is_array($value)|| is_string($value)) {
                  $val = $value;
              } else {
                 if(is_object($value)) {
                    $val = self::makeObjToArray($value);
                 }
              }
              $arr[$key] = $val;
        }
        $json = null;
        return $arr;
    }

    public static function  manyDimensionsArrayUnique($array) { 
        $temp = array();
        foreach ($array as $v) 
        { 
            $v = join(",",$v); 
            $temp[] = $v; 
        } 

        $temp = array_unique($temp); //去掉重复的字符串,也就是重复的一维数组 
        
        foreach ($temp as $k => $v) 
        { 
            $temp[$k] = explode(",",$v); //再将拆开的数组重新组装 
        } 
        return $temp; 
    } 
    
    /**
     *  写日志
     * @return mixed $data default array, else Exception 
     */
    public static function _writelog($msg = NULL) {
        print $msg.PHP_EOL;
    }

    /**
     *  写pid
     * @return mixed $data default array, else Exception 
     */
    public static function _writePid($path,$pid) {
        return file_put_contents($path,$pid);
    }

    /**
     *  读pid
     * @return mixed $data default array, else Exception 
     */
    public static function _readPid($path) {
        return file_get_contents($path);
    }

    public static function jsonEncode(array $data = NULL) {
        return json_encode($data);
    }
    
    public static function jsonDecode($str = NULL) {
        return (array)json_decode($str);
    }

    public static function getTaskClass($type = NULL) {
        return SERVER_CONFIG::$types[$type];
    }
    public static function getMasterPid($masterPidFile = NULL)
    {
        $pid = false;
        if (file_exists($masterPidFile)) {
            $pid = file_get_contents($masterPidFile);
        }
        return $pid;
    }
    public static function getManagerPid($managerPidFile = NULL)
    {
        $pid = false;
        if (file_exists($managerPidFile)) {
            $pid = file_get_contents($managerPidFile);
        }
        return $pid;
    }


}