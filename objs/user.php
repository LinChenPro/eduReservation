<?php
class User{
	public $user_id;
	public $user_name;
	public $user_pwd; // tmp
	
	/* --- definitions for db functions --- */
	public static $TABLE_NAME = "users";
	public static $TABLE_KEY = "user_id";
	public static $TABLE_FIELDS = "user_name, user_pwd";

	public function get_insert_values(){
		return "'$this->user_name', '$this->user_pwd'";
	}

	public function get_update_values(){
		return "user_name='$this->user_name', user_pwd='$this->user_pwd'";
	}

	static public function dbLineToObj($line){
		$obj = new User();
		$obj->user_id = $line["user_id"];
		$obj->user_name = $line["user_name"];
		$obj->user_pwd = $line["user_pwd"];
		return $obj;
	}


	/* --- constructors --- */
	public function __construct($user_id=null, $user_name=null, $user_pwd=null){
		$this->user_id = $user_id;
		$this->user_name = $user_name;
		$this->user_pwd = $user_pwd;
	}
	
	static public function getInstance($user_name=null, $user_pwd=null){
		return new User(null, $user_name, $user_pwd);
	}

	/* --- functions logics --- */
	static public function login($user_name, $user_pwd){
		$res = dbFindObjs('User', "user_name='$user_name' and user_pwd='$user_pwd'");
		if(empty($res)){
			return null;
		}else{
			return $res[0];
		}
	}

}


