<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *  多进程任务配置项
 * @author v.r
 * @package         多进程任务配置项
 * @subpackage      config.class.php
 */

define('ZHI_CLOUD_PUSH_NOTICE_ALIAS','zhiCloudPushNotice'); 
define('ZHI_CLOUD_PUSH_APP_ALIAS','zhiCloudPushApp'); 
define('ZHI_CLOUD_DESKTOP_CHANGE_ALIAS','zhiCloudCloudDeskTopChangeApp'); 
define('ZHI_CLOUD_PUSH_ZQT_NOTICE_ALIAS','zhiCloudCloudPushZqtNotice'); 
class SERVER_CONFIG 
{   

    /**
     * swoole 配置设置
     * @var array
     */
	public static $set = array(
      'worker_num' =>40,      //设置启动的worker进程数
      'daemonize' => false,   //是否守护进程运行
      'max_request' => 10000,  //设置worker进程的最大任务数，默认为0
      'dispatch_mode' => 2,
      'debug_mode'=> 1,       
      'task_worker_num' => 80, //配置task进程的数量
      'task_ipc_mode'         =>'1',      //设置task进程与worker进程之间通信的方式1unix socket、2消息队列、3消息队列通信，并设置为争抢模式
      'task_tmpdir'           =>'/tmp/multiProcessTask.zhicloud/task_data_ver_hzx567', //task数据临时存放目录
      'log_file'              =>'/tmp/multiProcessTask.zhicloud/swoole.log',   //swoole错误日志存放路径
      'master_pid'            =>'/tmp/multiProcessTask.zhicloud/master_pid.log',      //master进程号
      'manager_pid'           =>'/tmp/multiProcessTask.zhicloud/manager_pid.log'         //管理进程*/
	); 


  /**
   * 任务类型配置
   * @var array
   */
  public static $types = array(
     'changeDesktopApp'=>'changeDesktopAppTask',
  );

  /**
   * 数据库配置
   * @var array
   */
  public static $dbs = array(
	
  ); 
  public static $num = 10000;
}
