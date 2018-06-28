<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 基于swoole多进程任务服务端
 * 
 * @author  v.r
 * @package Common
 * 
 */

if(!defined('ASYS_TASK_LIB_PATH'))
    define('ASYS_TASK_LIB_PATH', dirname(__FILE__));

require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.class.php';
require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'util.class.php';
require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'gateWay.class.php';
require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'pushClient.class.php';
require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'pdo.class.php';
require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'mysql.class.php';

require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'interface'.DIRECTORY_SEPARATOR.'Itask.class.php';
require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'Factory.class.php';
require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'hashTable'.DIRECTORY_SEPARATOR.'Iterator.php';
require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'hashTable'.DIRECTORY_SEPARATOR.'Set.php';


date_default_timezone_set('Asia/Shanghai');
spl_autoload_register("Util::_loadOfClassPhp"); 

/**
 * 服务管理
 */
class serviceManage 
{
	
  public static $processName = 'swoole-task-Serv';

    /**
     * 启动 
     * @return mixed $data default array, else Exception 
     */
	public static function start($srv = NULL) {
      $msg = '任务服务启动...'.PHP_EOL;
      Console::setProcessName(serviceManage::$processName . ': master process');
      Util::_writePid(SERVER_CONFIG::$set['master_pid'],$srv->master_pid);
      Util::_writePid(SERVER_CONFIG::$set['manager_pid'],$srv->manager_pid);
      Util::_writelog($msg);

	}  
    
    /**
     * 重载 
     * @return mixed $data default array, else Exception 
     */
	public static function reload() {
		return false;
	}

    /**
     * 停止 
     * @return mixed $data default array, else Exception 
     */
	public static function stop() {
		return false;
	}

	/**
     * 重新启动 
     * @return mixed $data default array, else Exception 
     */
	public static function restart() {
		return false;
	}

    /**
     * 重载 
     * @return mixed $data default array, else Exception 
     */
	public static function help() {
 		$help=<<<'EOF'
NAME
      run.php - manage daemons
SYNOPSIS
      run.php command [options]
          Manage multi process daemons.

WORKFLOWS
      help [command]
      Show this help, or workflow help for command.
      restart
      Stop, then start the standard daemon loadout.
      start
      Start the standard configured collection of Phabricator daemons. This
      is appropriate for most installs. Use phd launch to customize which
      daemons are launched.
      stop
      Stop all running daemons, or specific daemons identified by PIDs. Use
      run.php status to find PIDs.
      reload
      Gracefully restart daemon processes in-place to pick up changes to
      source. This will not disrupt running jobs. This is an advanced
      workflow; most publishing should use run.php reload
EOF;
   print $help.PHP_EOL;

   
	}

}


/**
 * 任务服务
 */
class Worker {

  /**
   * 开始
   * @return mixed $data default array, else Exception 
   */
  public static function run($srv, $fd, $from_id, $data,&$hashTable) {

/*
      $msg = '接收客户端'.PHP_EOL;
      $msg .='fd'.$fd.'from_id'.$from_id.PHP_EOL;
      $msg .='数据:'.PHP_EOL;  
      Util::_writelog($msg);
*/




      if ($hashTable->get($data['_uq'])) 
          return false;

      $hashTable->set($data['_uq'],
         array(
            'num'=>0,
            'len'=>$data['tp'],
            'success'=>0,
            'fail'=>0,
            'uq'=>$data['_uq'],
         )
      );

      if (empty($data['tp'])) {
         Util::_writelog("任务分配参数为空".PHP_EOL);
         return false;
      }

      print  '开始时间：'.date('Y-m-d H:i:s',time()).PHP_EOL;
      for ($i = 0;$i < $data['tp']; $i++) {
          $data['num'] = $i;
          $srv->task(Util::jsonEncode($data));
      }
  }

  /**
   * 任务 
   * @return mixed $data default array, else Exception 
   */
  public static function onTask($srv, $task_id, $from_id, $data,&$hashTable) {
      $class = Util::getTaskClass(Util::jsonDecode($data)['type']);
      print "任务开始时间：".$task_id.$from_id.date('Y-m-d H:i:s',time()).PHP_EOL;
      return $class::run($srv,$task_id,$from_id,$data,function($data)use(&$hashTable) {
          $element = $hashTable->get($data['_uq']);
          $element['num'] += 1;
          $element['success'] += $data['data']['success'];
          $element['fail'] += $data['data']['fail'];
          $hashTable->set($data['_uq'],$element);
          print "任务结束时间：".date('Y-m-d H:i:s',time()).PHP_EOL;
          return Util::jsonEncode($data);
      });
  } 



  /**
   * 任务汇总 
   * @return mixed $data default array, else Exception 
   */
  public static function onFinish($srv, $task_id, $data,&$hashTable) {
      $class = Util::getTaskClass(Util::jsonDecode($data)['type']);
      $element = $hashTable->get(Util::jsonDecode($data)['_uq']);
      $msg = '';
      $msg .="任务ID:\033[32;40m [".$task_id."] \033[0m".PHP_EOL;
      $msg .="来自进程编号:"."\033[32;40m [".$srv->worker_id."] \033[0m".PHP_EOL;
      $msg .='任务数'.$element['num'].'总数'.$element['len'].PHP_EOL;
      
      Util::_writelog($msg);

      if ($element['num'] == $element['len']) {
          return $class::Finish($srv,$task_id,$element,$data,function($data)use(&$hashTable) {
              $_uq = Util::jsonDecode($data)['_uq'];
              $element = $hashTable->get($_uq);
              $hashTable->del($_uq);
              print  '结束时间：'.date('Y-m-d H:i:s',time()).PHP_EOL;

          });
      } 
  }
  
}



/**
 * 任务服务
 */
class asysTaskSrv 
{
   
  
    private $serivce;
    private $worker;
    public  $hashTable;

    public  $setting;
    public  $host;
    protected $port = 9701;
    protected $listen;
    protected $mode = SWOOLE_PROCESS;
    public  $processName = 'multiProcessTask';

    
    private $preSysCmd = '%+-swoole%+-';
    private $requireFile = '';



    public function __construct() {
     // $this->serivce->start();
    }

    public function initServer() {
        $this->serivce = new swoole_server("0.0.0.0", 9601);
        $this->worker = new Worker;
        $this->hashTable = new swoole_table(20240);
        $this->hashTable->column('num', swoole_table::TYPE_INT, 4);       
        $this->hashTable->column('len', swoole_table::TYPE_INT, 4);
        $this->hashTable->column('success',swoole_table::TYPE_INT, 4);
        $this->hashTable->column('fail',swoole_table::TYPE_INT, 4);
        $this->hashTable->column('uq',swoole_table::TYPE_STRING, 33);
        $this->hashTable->create();
        $this->serivce->set(SERVER_CONFIG::$set);
        $this->serivce->on('Start',array($this,'onMasterStart'));//swoole启动主进程主线程回调
        $this->serivce->on('ManagerStart', array($this, 'onManagerStart')); //当管理进程启动时候触发
        $this->serivce->on('WorkerStart', array($this, 'onWorkerStart'));  //任务进程启动进行触发
        $this->serivce->on('Shutdown',array($this,'onShutdown'));//服务关闭回调
        $this->serivce->on("Connect",array($this,'onConnect'));       //新连接进入回调
        $this->serivce->on("Receive",array($this,'onReceive'));       //接收数据回调
        $this->serivce->on("Close",array($this,'onClose'));           //客户端关闭回调
        $this->serivce->on("Task",array($this,'onTask'));     //task进程回调
        $this->serivce->on("Finish",array($this,'onFinish')); //进程投递的任务在task_worker中完成时回调 exit("服务已经在运行!");
    }

    public function start() {
        Util::_writelog($this->processName . ": start\033[32;40m [OK] \033[0m");
        $this->serivce->start();
    }


    /**
     * 
     * 主进程服务启动 
     * @return mixed $data default array, else Exception 
     */
  	public function onMasterStart($srv = NULL) {
      $msg = '任务服务启动...'.PHP_EOL;
      Console::setProcessName(serviceManage::$processName . ': master process');
      Util::_writePid(SERVER_CONFIG::$set['master_pid'],$srv->master_pid);
      Util::_writePid(SERVER_CONFIG::$set['manager_pid'],$srv->manager_pid);
      Util::_writelog($msg);

      // serviceManage::start($srv);
  	}

    public function onManagerStart() {
      $msg = '管理进程开启'.PHP_EOL;
      Util::_writelog($msg);
    }

    public function onWorkerStart($srv, $workerId) {
      $msg = '工作进程开启'.PHP_EOL;
      Util::_writelog($msg);

      if ($workerId >= SERVER_CONFIG::$set['worker_num']) {
          Console::setProcessName($this->processName . ': task worker process');
      } else {
          Console::setProcessName($this->processName . ': event worker process');
      }
    }

    /**
     * 客户端连接 
     * @return mixed $data default array, else Exception 
     */
    public function onConnect($srv, $fd, $from_id){
      $msg = '客户端连接成功'.PHP_EOL;
      $msg .= "fd:$fd,from_id:$from_id".PHP_EOL;
      Util::_writelog($msg);
    }

    
    /**
     * 接受消息
     * @param  obj $srv      swoole对象
     * @param  resource $fd  客户端连接
     * @param  int  $from_id 不同进程的id(workerid)
     * @param  string  $data 数据
     * @return string
     *
     */
  	public function onReceive($srv, $fd, $from_id, $data) {
      return Worker::run($srv,$fd, $from_id,Util::jsonDecode($data),$this->hashTable);
  	}

    /**
     * 任务 
     * @return mixed $data default array, else Exception 
     */
    public function onTask($srv, $task_id, $from_id, $data) {
    		return Worker::onTask($srv, $task_id, $from_id,$data,$this->hashTable);
    }

    /**
     * 汇总 

     * @return mixed $data default array, else Exception 
     */
    public function onFinish($srv, $task_id, $data) {
        return Worker::onFinish($srv, $task_id,$data,$this->hashTable);
    }

    /**
     * 服务关闭 
     * @return mixed $data default array, else Exception 
     */
    public function onShutdown(){
    	  $msg = '任务服务关闭成功'.PHP_EOL;
    //	  $msg .= "fd:$fd,from_id:$from_id".PHP_EOL;
        Util::_writelog($msg);
    }

    /**
     * 连接关闭 
     * @return mixed $data default array, else Exception 
     */
    public function onClose($serv, $fd, $reactorId){
    	$msg = '客户端'.$fd.'关闭成功'.PHP_EOL;
      Util::_writelog($msg);
    }



     /**
      * 服务运行 
      * @return mixed $data default array, else Exception 
      */
    public function run() {
        echo __METHOD__ . PHP_EOL;
        $cmd = isset($_SERVER['argv'][1]) ? strtolower($_SERVER['argv'][1]) : 'help';

        switch ($cmd) {
            //stop
            case 'stop':
                $this->shutdown();
                break;
            //start
            case 'start':
                $this->initServer();
                $this->start();
                break;
            //reload worker
            case 'reload':
                $this->reload();
                break;
            case 'restart':
                $this->shutdown();
                sleep(2);
                $this->initServer();
                $this->start();
                break;
            case 'status':
                $this->status();
                break;
            case 'help':
               serviceManage::help();
               break;

            default:
                echo 'Usage:php swoole.php start | stop | reload | restart | status | help' . PHP_EOL;
                break;
        }
    }

    //停止服务
    public function shutdown() {
        $masterId = Util::getMasterPid(SERVER_CONFIG::$set['master_pid']);
        if (!$masterId) {
            Util::_writelog("[warning] " . $this->processName . ": can not find master pid file");
           Util::_writelog($this->processName . ": stop\033[31;40m [FAIL] \033[0m");
            return false;
        } elseif (!posix_kill($masterId, 15)) {
           Util::_writelog("[warning] " . $this->processName . ": send signal to master failed");
           Util::_writelog($this->processName . ": stop\033[31;40m [FAIL] \033[0m");
            return false;
        }
        unlink(SERVER_CONFIG::$set['master_pid']);
        unlink(SERVER_CONFIG::$set['manager_pid']);
        usleep(50000);
        Util::_writelog($this->processName . ": stop\033[32;40m [OK] \033[0m");
        return true;
    }


    //重载服务
    public function reload()
    {
        $managerId =  Util::getManagerPid(SERVER_CONFIG::$set['manager_pid']);
        if (!$managerId) {
            Util::_writelog("[warning] " . $this->processName . ": can not find manager pid file");
            Util::_writelog($this->processName . ": reload\033[31;40m [FAIL] \033[0m");
            return false;
        } elseif (!posix_kill($managerId, 10))//USR1
        {
            Util::_writelog("[warning] " . $this->processName . ": send signal to manager failed");
            Util::_writelog($this->processName . ": stop\033[31;40m [FAIL] \033[0m");
            return false;
        }
         Util::_writelog($this->processName . ": reload\033[32;40m [OK] \033[0m");
        return true;
    }

    public function status() {
        Util::_writelog("*****************************************************************");
        Util::_writelog("Summary: ");
        Util::_writelog("Swoole Version: " . SWOOLE_VERSION);
        if (!$this->checkServerIsRunning()) {
            Util::_writelog($this->processName . ": is running \033[31;40m [FAIL] \033[0m");
            Util::_writelog("*****************************************************************");
            return false;
        }
      Util::_writelog($this->processName . ": is running \033[32;40m [OK] \033[0m");
      Util::_writelog("master pid : is " . Util::getMasterPid(SERVER_CONFIG::$set['master_pid']));
      Util::_writelog("manager pid : is " .Util::getManagerPid(SERVER_CONFIG::$set['manager_pid']));
      Util::_writelog("*****************************************************************");
    }


    public  function checkServerIsRunning()
    {
        $pid = Util::getMasterPid(SERVER_CONFIG::$set['master_pid']);
        return $pid && $this->checkPidIsRunning($pid);
    }

    public function checkPidIsRunning($pid)
    {
        return posix_kill($pid, 0);
    }
}

$asysTaskSrv = new asysTaskSrv;
$asysTaskSrv->run();

class Console
{
    static function getOpt($cmd)
    {
        $cmd = trim($cmd);
        $args = explode(' ', $cmd);
        $return = array();
        foreach ($args as &$arg) {
            $arg = trim($arg);
            if (empty($arg)) unset($arg);
            if ($arg{0} === '\\' or $arg{0} === '-') $return['opt'][] = substr($arg, 1);
            else $return['args'][] = $arg;
        }
        return $return;
    }

    /**
     * 改变进程的用户ID
     * @param $user
     */
    static function changeUser($user)
    {
        if (!function_exists('posix_getpwnam')) {
            trigger_error(__METHOD__ . ": require posix extension.");
            return;
        }
        $user = posix_getpwnam($user);
        if ($user) {
            posix_setuid($user['uid']);
            posix_setgid($user['gid']);
        }
    }

    static function setProcessName($name)
    {
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($name);
        } else if (function_exists('swoole_set_process_name')) {
            swoole_set_process_name($name);
        } else {
            trigger_error(__METHOD__ . " failed. require cli_set_process_title or swoole_set_process_name.");
        }
    }
}