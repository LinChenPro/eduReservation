<?php
class Categ{
	public $categ_id;
	public $categ_name;
	
	/* --- definitions for db functions --- */
	public static $TABLE_NAME = "categories";
	public static $TABLE_KEY = "categ_id";
	public static $TABLE_FIELDS = "categ_name";

	public function get_insert_values(){
		return "'$this->categ_name'";
	}

	public function get_update_values(){
		return "categ_name='$this->categ_name'";
	}

	static public function dbLineToObj($line){
		$obj = new Categ();
		$obj->categ_id = $line["categ_id"];
		$obj->categ_name = $line["categ_name"];
		return $obj;
	}

	/* --- constructors --- */
	public function __construct($categ_id=null, $categ_name=null){
		$this->categ_id = $categ_id;
		$this->categ_name = $categ_name;
	}
	
	static public function getInstance($categ_name){
		return new Categ(null, $categ_name);
	}
	
	/* --- functions logics --- */
	static public function getCategs(... $whereClauses){
		return dbFindObjs('Categ', ...$whereClauses);
	}

}


