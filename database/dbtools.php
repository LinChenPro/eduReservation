<?php
$servername = "localhost";
$dbname = "reservation_manage";
$username = "bg_connector";
$password = "bg_connector_pwd";

/* grant all on reservation_manage.* to bg_connector@localhost identified by 'bg_connector_pwd'; */

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
//$conn->options(MYSQL_OPT_READ_TIMEOUT, 5);

function query($sql){
	global $conn;

	try {
		$res = $conn->query($sql);
		if(!$res){
			return false;
		}
		return $res;
	} catch (Exception $e) {
		return false;
	}
}

function insertQuery($sql){
	global $conn;
	$res = $conn->query($sql);

	if(!$res===TRUE){
		echo  $conn->error;
	}
	return $conn->insert_id;
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

function dbGetObjsByQuery($query_sql, $convert_func){
	$res = query($query_sql);
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

function dbGetObjByQuery($query_sql, $convert_func){
	$res = query($query_sql);
	if ($res->num_rows > 0) {
	    if($row = $res->fetch_assoc()) {
	        return call_user_func_array($convert_func, array($row));
	    }
	} 

	return null;
}

function fieldQuery($query_sql, $field_name, $def=null){
	$res = query($query_sql);
	if ($res->num_rows > 0) {
	    if($row = $res->fetch_assoc()) {
	        return $row[$field_name];
	    }
	} 

	return $def;
}

/**** function week stamp *****/
function getUsersWeekStamp($week_nb, ...$uid_arr){
	$sql = "select max(stamp_time) as stamp_time from weekly_action_stamp where stamp_uid in(".concat("", ",", ...$uid_arr).") and stamp_week_nb=$week_nb";

	$stamp_time = dbGetObjByQuery($sql, function($row){
		return $row["stamp_time"];
	});

	if($stamp_time==null){
		$stamp_time = getDateZeroStr();
	}
	return $stamp_time;

}

function getUserWeekStamp($uid, $week_nb){
	$sql = "select * from weekly_action_stamp where stamp_uid=$uid and stamp_week_nb=$week_nb";

	$stamp_time = dbGetObjByQuery($sql, function($row){
		return $row["stamp_time"];
	});

	if($stamp_time==null){
		$stamp_time = getDateZeroStr();
	}
	return $stamp_time;
}

function checkChange($uid, $week_nb, $demand_timestamp){
	return getUserWeekStamp($uid, $week_nb)>$demand_timestamp;
}

function updateUserWeekStamp($uid, $week_nb){
	$sql = "insert into weekly_action_stamp(stamp_uid, stamp_week_nb) value($uid, $week_nb) ON DUPLICATE KEY UPDATE stamp_time=CURRENT_TIMESTAMP";
	query($sql);
}

function getStampLocks($uid_1, $week_nb_1, $uid_2=null, $week_nb_2=null){
	$hasUid2 = ($uid_2 != null && $uid_2 != $uid_1);
	$hasWeekNb2 = ($week_nb_2 != null && $week_nb_2 != $week_nb_1);

	$locks = array(new UserWeekLock($uid_1, $week_nb_1));

	if($hasUid2){
		array_push($locks, new UserWeekLock($uid_2, $week_nb_1));
	}

	if($hasWeekNb2){
		array_push($locks, new UserWeekLock($uid_1, $week_nb_2));
		if($hasUid2){
			array_push($locks, new UserWeekLock($uid_2, $week_nb_2));
		}		
	}

	return $locks;
}

function blockStamps($locks, $read_only=false){
	for($i=0; $i<2; $i++){
		if(blockUserWeeks($read_only, ...$locks)){
			return true;
		}

		sleep(1);
	}

	return false;
}

/************************/

// locks due to weekly_action_stamp

class UserWeekLock{
	public $uid;
	public $week_nb;

	public function __construct($uid, $week_nb){
		$this->uid = $uid;
		$this->week_nb = $week_nb;
	}

	public static function compare($la, $lb){
		return $la->uid < $lb->uid? -1 :(
			$la->uid > $lb->uid ? 1 : (
				$la->week_nb < $lb->week_nb ? -1 : (
					$la->week_nb > $lb->week_nb ? 1 : 0
				)
			) 
		);
	}

	public static function sortLock(&$locks){
		uasort($locks, "UserWeekLock::compare");
		return $locks;
	}
}

function doTransaction($locks, $execFun, $params=array()){
	if(blockStamps($locks)){sleep(10);
		$funResult = call_user_func_array($execFun, $params);
		releaseUerWeeks($funResult->succes, ...$locks);
		return $funResult;
	}else{
		return new ActionResult(false, null, "Other user are doing the operation. please try later.");
	}

}

function blockUserWeeks($is_read_only, ...$locks){
	global $conn;
	$lock_type = $is_read_only ? MYSQLI_TRANS_START_READ_ONLY : MYSQLI_TRANS_START_READ_WRITE;
	$queryCondition = $is_read_only ? "" : " FOR UPDATE";

	$conn->begin_transaction($lock_type);

	$locks = UserWeekLock::sortLock($locks);

	foreach ($locks as $lock) {
		$rs = query("select stamp_time from weekly_action_stamp where stamp_uid=$lock->uid and stamp_week_nb=$lock->week_nb".$queryCondition);
		if($rs == false || mysqli_errno($conn)){
			$conn->rollback();
			return false;
		}
	}

	return true;
}

function releaseUerWeeks($succes, ...$locks){
	global $conn;
	if($succes){
		foreach ($locks as $lock) {
			updateUserWeekStamp($lock->uid, $lock->week_nb);
		}

		$conn->commit();		
	}else{
		$conn->rollback();
	}
}