<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *   
 *   创建SQL语句组件
 *   
 * @author  v.r
 * @package task.services 
 * 
 * 注: 辅助方法为私有方法 命名以下滑线开头
 *
 *  Set生成
 *  where生成
 *  filed生成
 *  order生成
 *  inseter生成
 *
 *
 *
 
  INSERT INTO `zhicloud_compan_src_t` (`tax`,`type`,`company_name`,`tb_code`,`_as`,`_md`) VALUES ('92620200MA7453CM5R','3','嘉峪关市大自然苟子烧烤店','162000000','gshx','{"user":{"sex":3,"phone":"13830792228","true_name":"\u82df\u91d1\u6ce2","nick_name":"\u82df\u91d1\u6ce2"},"company":{"tel_phone":"","address":"\u5609\u5cea\u5173\u6587\u5316\u4e2d\u8def\u4e2d\u9e4f\u5609\u5e74\u534e","province_id":"3194","city_id":"3205","zone_id":0,"level":0,"tax_number":"92620200MA7453CM5R","master_slave":null,"legal_person_name":"\u82df\u91d1\u6ce2","legal_person_phone":"13830792228","company_name":"\u5609\u5cea\u5173\u5e02\u5927\u81ea\u7136\u82df\u5b50\u70e7\u70e4\u5e97","charge_end_date":"2019-05-22T00:00:00"}}')
 *  
 */

class createSqlComponent
{

  /**
   * 
   * 插入语句
   *  
   * @param array  fields  字段
   * @return void
   * 
   */
  public static function Insert($table,$items = array()) {
    	$fields = array_keys($items);
    	$values = array_values($items);


      $fields = array_map(function($field) {
           return "`" . $field . "`";
      }, $fields);
      
      $values = array_map(function($value) {
        $values = addslashes(stripslashes($value));
        return "'" . $value . "'";
      }, $values);
      
      return  'INSERT INTO `' . $table . '` (' . implode(',', $fields) . ') VALUES (' . implode(',', $values) .')';
  }


  public static function Delete($table,$items) {
    return 'DELETE FROM `'  . $table . '`  WHERE ' . createSqlComponent::filedToSqlStr($items);
  }

  /**
   * 
   *  SET SQL语句生成
   *  
   * @param array  fields  字段
   * @return void
   * 
   */
  public static function Set($fields = array()) {
	   return createSqlComponent::filedToSqlStr($fields,",");
  }

  /**
   * 
   *  AND SQL语句生成
   *  
   * @param array  fields 字段
   * @return void
   * 
   */
  public static function And($fields = array()) {
   	 return createSqlComponent::filedToSqlStr($fields,"AND");
  }

  /**
   * 
   *  字段生成器
   *  
   * @param array  fields    字段
   * @param string condition 条件  
   * @return void
   * 
   */
  public static function filedToSqlStr($fields = array(),$condition = "AND") {
  	$sql = '';
  	$len = count($fields);
  	$num = 0;
  	foreach ($fields as $key => $value) {
  		$num +=1;
  		$sql.= " `{$key}` = '{$value}'" ;
  		if ($len != $num) 
  			$sql .=" {$condition} ";
  	}
  	return $sql;
  } 

}