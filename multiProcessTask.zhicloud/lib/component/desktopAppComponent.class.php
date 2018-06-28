<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *   
 *   客户桌面组件
 *   
 * @author  v.r
 * @package task.services 
 * 
 * 注: 辅助方法为私有方法 命名以下滑线开头
 *

 * 
 */

class desktopAppComponent
{
    /**
     * 
     *  桌面应用发布
     * @return void
     * 
     */
    public static function addDesktopApps($model = NULL,$u = NULL,$apps = NULL) {
      $flags = array();
      foreach ($apps as $app_id) {
      	$flags[] = $model->save(
           array(
              'uid'=>$u['uid'],
              'compan_id'=>$u['compan_id'],
              'app_id'=>$app_id
            )
      	);
      }
      if (in_array(true, $flags)) 
         return true;
      else
         return false;
    }

    /**
     * 
     *  桌面应用删除
     * @return void
     * 
     */
    public static function delDesktopApps($model = NULL,$u = NULL,$apps = NULL) {
  		$flags = array();
  		foreach ($apps as $app_id) {
  			$flags[] = $model->del(
  			   array(
  			      'uid'=>$u['uid'],
  			      'compan_id'=>$u['compan_id'],
  			      'app_id'=>$app_id
  			    )
  			);
  		}

      if (in_array(true, $flags)) 
         return true;
      else
         return false;
    }

}