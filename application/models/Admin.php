<?php

use Db\Driver;

class AdminModel extends Driver{

	protected $_table = "yf_admin";

	/**
	 * 用户登录判断
	 */
	public function loginUsr($username)
	{
		$usrinfo = $this->_db->selectFirst($this->_table,array('admin_username'=>$username));

		//使用select
		//$usrinfo = $this->_db->select($this->_table,array('username'=>$usr,'password'=>$passwd),1);

		//使用query
		//$sql = "SELECT * FROM $this->_table WHERE username='{$usr}' AND password='{$passwd}' AND is_del='0'";
		//$usrinfo = $this->_db->query($sql);

		if(!empty($usrinfo) && is_array($usrinfo)){
			return $usrinfo;
		}else{
			return false;
		}
	}

	//获取后台登陆个人信息
	public function profileUser($profile_id){

		if($profile_id){

			$sql = "SELECT * FROM {$this->_table} a INNER JOIN yf_auth_group_access as b on a.admin_id =b.uid
					INNER JOIN yf_auth_group as c on b.group_id = c.id WHERE a.admin_id = '{$profile_id}' LIMIT 1";
			$profile = $this->_db->query($sql);
			if(is_array($profile) && !empty($profile)){
				return $profile[0];
			}
		}
	}


	/**
	 * 自增
	 */

	public function setInc($wheres,$params)
	{

		$admin = $this->_db->select($this->_table,$wheres,1);
		if(!empty($admin) && is_array($admin)){
			try{
				$nparams = array();
				foreach($params as $key => $value){
					$nparams[$key] = intval($admin[$key])+$value;
				}
				$ret = $this->_db->update($this->_table, $nparams, $wheres);
				var_dump($ret);
				if($ret===false){
					return false;
				}
				return true;
			}catch(Exception $e){
				return false;
			}
		}

	}


	/**
	 *保存数据
	 */
	public function save($params,$wheres)
	{
		try{
			$ret = $this->_db->update($this->_table, $params, $wheres);
			if($ret===false){
				return false;
			}
			return true;
		}catch(Exception $e){
			return false;
		}

	}


	protected function afterAdd() {
		//do something after delete object
	}

	protected function afterUpdate() {
		//do something after update object
	}
}
