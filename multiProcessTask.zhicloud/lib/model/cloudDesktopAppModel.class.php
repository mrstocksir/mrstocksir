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
class cloudDesktopAppModel {

    private $table = 'cloud_desktop_apps';
    private $model = NULL;
    private $useDb = 'zhiCloudApp';

    public function __construct($uq = NULL) {
        $this->model = Factory::NewDB(
                        SERVER_CONFIG::$dbs[$this->useDb], $uq
        );
    }

    public function save($data = NULL) {
        $sql = createSqlComponent::Insert($this->table,$data);
        print $sql;
        print PHP_EOL;

        $use = "use ". $this->useDb;
        $this->model->query($use);
        print $sql.PHP_EOL;
        return $this->model->query($sql);

    }

    public function del($condition = NULL) {
        $sql = createSqlComponent::Delete($this->table,$condition);
        print $sql;
        print PHP_EOL;
        $use = "use ". $this->useDb;
        $this->model->query($use);
        return $this->model->query($sql);
    }
}
