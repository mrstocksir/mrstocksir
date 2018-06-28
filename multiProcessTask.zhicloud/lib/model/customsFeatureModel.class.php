<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *   所用数据库相关模型
 * @author  v.r
 * @package task.services 
 * 
 * 注: 辅助方法为私有方法 命名以下滑线开头
 * 
 */


class customsFeatureModel 
{
	private $table = 'customs_feature';
	private $model = NULL;
	private $useDb = 'zhiCloudCustoms';

	public function __construct($uq = NULL) {
	    $this->model = Factory::NewDB(
			SERVER_CONFIG::$dbs[$this->useDb],
			$uq
		);
	}

/*	public function restart($uq = NULL) {
	    $this->model = Factory::NewDB(
			SERVER_CONFIG::$dbs[$this->useDb],
			$uq
		);
	}*/
	
	/**
     * 
     * 通过条件获取用户集
     * @param   mixed $increment default true, else full crawler
     *  getWriteQueueFailedItems
     */
	public function getSendUserMapByCondition($condition = NULL,$p = NULL) {

        $ps = ($p-1) * SERVER_CONFIG::$num;  
        $limit = $ps.','.SERVER_CONFIG::$num;
        $sql = "SELECT uid,company_id  FROM ".$this->table;
        if (!empty($condition)) 
        	$sql .= " WHERE ".$this->makeSqlWhere($condition);
        $sql .= " LIMIT {$limit}";
        $result = $this->model->query($sql);
        return $result->fetchall();
	}
	
	public function  makeSqlWhere($condition) {
		$sql = '';
		$len = count($condition);
		$num = 0;
		foreach ($condition as $key => $value) {
			$num +=1;
			$sql.= " {$key} = {$value}" ;
			if ($len != $num) 
				$sql .=" AND ";
		}
		return $sql;
	}
}
