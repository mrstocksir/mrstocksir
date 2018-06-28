<?php

include_once './asysTaskCli.class.php';



try {
    

    $len = 100;
    for ($i=0; $i <$len ; $i++) {
	      $key = md5(uniqid().$i);
	      print "task-key:".$key.PHP_EOL;
		  $asysTaskCli = new asysTaskCli;
	      $asysTaskCli->connect();
	      $asysTaskCli->create(10001,array('pro_id'=>'3393','city_id'=>'3394'),3,$key);
	      sleep(1); 
    }
    
} catch (Exception $e) {
    print $e->getCode();
    print $e->getMessage();
}