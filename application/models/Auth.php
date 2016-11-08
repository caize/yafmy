<?php

use Db\Driver;

class AuthModel extends Driver{

	protected $_table = "yf_auth_rule";

	/**
	 * 显示所有用户信息
	 */
	public function showUsers($where)
	{
		//$sql = "SELECT * FROM $this->_table WHERE is_del='0'";
		//return $this->_db->query($sql);
		$usrinfo = $this->_db->select($this->_table,$where);
		return $usrinfo;
	}

	/**
	 * 关联查询
	 */

	public function getAuthGroups($sql)
	{
		$usrinfo = $this->_db->query($sql);
		if(!empty($usrinfo)){
			return $usrinfo;
		}else{
			return false;
		}

	}


	/**
	 *根据id获取用户信息
	 * @param [type] $id [description]
	 */
	public function GetUsrInfo($where)
	{
		$usrinfo = $this->_db->selectFirst($this->_table,$where);
		return $usrinfo;
	}


	protected function afterAdd() {
		//do something after delete object
	}

	protected function afterUpdate() {
		//do something after update object
	}
}
