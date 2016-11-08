<?php
namespace Db;
use Db\Error as ERR;//载入错误码配置
use Yaf;
class Driver
{

    protected $_config;
    protected $_db;


    public function __construct() {
        //获取配置文件
        $this->_config = Yaf\Registry::get("config");
        $this->_db = new Drivers\Mysql ($this->_config->database->toArray());

		//实例化数据库模型
		//$this->_db = new Db_Mysql ($this->_config['database']['config']);

		//配置数据表（表前缀+表名）
		//$this->_table = $this->_config['database']['config']['prefix'].$this->_table;
    }
	

	
}