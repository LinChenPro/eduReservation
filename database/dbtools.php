<?php
$servername = "localhost";
$dbname = "reservation_manage";
$username = "bg_connector";
$password = "bg_connector_pwd";

/* grant all on reservation_manage.* to bg_connector@localhost identified by 'bg_connector_pwd'; */

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
function query($sql){
	global $conn;
	$res = $conn->query($sql);

	if(!$res===TRUE){
		echo  $conn->error;
	}
	return $res;
}

function getClauseStr($whereClauses){
	return concat(" where ", " and ", ...$whereClauses);
}

/********** operations due tu objects *********/
function getObjsFromTable($table_names, $convert_func, ...$whereClauses){
	$res = query("select * from ".$table_names.getClauseStr($whereClauses));
	if ($res->num_rows > 0) {
		$objList = array();
	    while($row = $res->fetch_assoc()) {
	        array_push($objList, call_user_func_array($convert_func, array($row)));
	    }
	    return $objList;
	} else {
	    return null;
	}
}

function getObjFromTable($table_names, $convert_func, ...$whereClauses){
	$res = query("select * from ".$table_names.getClauseStr($whereClauses));
	if ($res->num_rows > 0) {
	    if($row = $res->fetch_assoc()) {
	        return call_user_func_array($convert_func, array($row));
	    }
	}
	
	return null;
}


function getObjectKey($obj){
	return get_object_vars($obj)[$obj::$TABLE_KEY];
}

function dbInsert($obj){
	$sql = "insert into ".$obj::$TABLE_NAME."(".$obj::$TABLE_FIELDS.") values(".$obj->get_insert_values().")";
	return query($sql);
}

function dbUpdate($obj){
	$sql = "update ".$obj::$TABLE_NAME." set ".$obj->get_update_values()." where ".$obj::$TABLE_KEY."=".getObjectKey($obj);
	return query($sql);
}

function dbDelete($obj){
	return dbDeleteByKey(get_class($obj), getObjectKey($obj));
}

function dbDeleteObjs($className, ... $whereClauses){
	$sql = "delete from ".$className::$TABLE_NAME.getClauseStr($whereClauses);
	return query($sql);
}

function dbDeleteByKey($className, $key, ...$whereClauses){
	if(!empty($key)){
		array_push($whereClauses, $className::$TABLE_KEY."=$key");
	}
	return dbDeleteObjs($className, ...$whereClauses);
}

function dbFindObjs($className, ... $whereClauses){
	return getObjsFromTable($className::$TABLE_NAME, "$className::dbLineToObj", ...$whereClauses);
}

function dbFindObj($className, ... $whereClauses){
	return getObjFromTable($className::$TABLE_NAME, "$className::dbLineToObj", ...$whereClauses);
}

function dbFindByKey($className, $key, ...$whereClauses){
	if(!empty($key)){
		array_push($whereClauses, $className::$TABLE_KEY."=$key");
	}
	return dbFindObj($className, ... $whereClauses);
}
