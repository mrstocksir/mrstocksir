<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *   用户桌面
 * @author  v.r
 * @package task.services 
 * 
 * 注: 辅助方法为私有方法 命名以下滑线开头
 * 
 */

define('PUSH_DESKTOP_APP',5);
define('DELETE_DESKTOP_APP',6);

class changeDesktopAppTask implements Itask {
    /**
     *  运行
     * @return mixed 
     */
    public static function run($srv, $task_id, $from_id, $data, $callback) {
        try {
            
            print $data.PHP_EOL;
            $ptl = $data;
            $data = Util::jsonDecode($data);
            $p = $data['num'] + 1;
            $condition = (array) $data['condition'];
            $customsFeatureModel = Factory::model('customsFeatureModel');
            $apps = $condition['app_id'];
            $action_code = $condition['mq_type'];
            unset($condition['app_id']);
            unset($condition['mq_type']);

            $asyzeQueue = $customsFeatureModel->getSendUserMapByCondition($condition,$p,1000);
            $cloudDesktopAppModel = Factory::model('cloudDesktopAppModel');
            unset($customsFeatureModel);

            if (empty($asyzeQueue))
                return $callback(array('data' => 
                    array('success' => $len, 'fail' => 0), 
                    'type' => $data['type'],'action'=>$action_code,
                    'ptl'=>$ptl, '_uq' => $data['_uq']));
            
            $asyzeQueue = Util::manyDimensionsArrayUnique($asyzeQueue);
            $len = count($asyzeQueue);
            $num = 0;
            $success = 0;
            $fail = 0;
            
            do {

                $item = $asyzeQueue[$num];  
                $uid = $item[0];
                $compan_id = $item[1];

                /**
                 * 桌面应用发布
                 * 基于协议遍历插入数据
                 * 
                 */
                if (PUSH_DESKTOP_APP == $action_code) {

                    $flag = desktopAppComponent::addDesktopApps(
                        $cloudDesktopAppModel,
                        array(
                            'uid'=>$item[0],
                            'compan_id'=>$item[1],
                        ),
                        $apps
                    );

                    if ($flag) 
                      $success +=1;
                    else
                      $fail +=1;
                }

                /**
                 * 桌面应用删除
                 * 基于协议遍历删除用户桌面
                 * 
                 */
                if (DELETE_DESKTOP_APP == $action_code) {
                    $flag =desktopAppComponent::delDesktopApps( 
                        $cloudDesktopAppModel,
                        array(
                            'uid'=>$item[0],
                            'compan_id'=>$item[1],
                        ),
                        $apps
                    );

                    if ($flag) 
                      $success +=1;
                    else
                      $fail +=1;
                }

                $num++;
            } while ($len > $num);

            unset($cloudDesktopAppModel);            
            
            return $callback(
                    array(
                        'data' => array(
                            'success' => $success,
                            'fail' => $fail,
                        ),
                        'action'=>$action_code,
                        'ptl'=>$ptl,
                        'type' => $data['type'],
                        '_uq' => $data['_uq']
                    )
            );
        
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            var_dump($e->getCode());
        }
    }

    /**
     *  汇总
     * @return mixed 
     */
    public static function Finish($srv, $task_id, $element, $data, $callback) {
        print PHP_EOL;
        print '-------------------桌面应用-----------------------------------';
        print PHP_EOL;
        print "\033[32;40m [编号] \033[0m".':'.$element['uq'];
        print PHP_EOL;
        print "\033[32;40m [结果] \033[0m".':'.Util::jsonEncode($element);
        print PHP_EOL;
        print "\033[32;40m [时间] \033[0m".':'.date("Y-m-d H:i:s",time());
        print PHP_EOL;
        print '-------------------桌面应用------------------------------------';
        print PHP_EOL;
        //固定写法
        return $callback($data);
    }

}
