<?php
/********** session functions **********/
TODO("add session statut : on_selection, waiting_payment_result, archive, user can change only when 'on_selection'");

define("SESSION_DURATION", "20 MINUTE");
define("SESSION_STATUT_INOPERATION", 1);
define("SESSION_STATUT_INPAYMENT", 2);
define("SESSION_STATUT_SUCCES", 3);
define("SESSION_STATUT_ERROR", 4);
define("SESSION_STATUT_EXPIRED", 5);
define("SESSION_STATUT_CANCELLED", 6);

class StudentSession{
	public $session_id;
	public $session_sid;
	public $session_create_time;
	public $session_expire_time;
	public $session_statut;

	public static $TABLE_NAME = "student_session";

	static public function dbLineToObj($line){
		$obj = new StudentSession();
		$obj->session_id = $line["session_id"];
		$obj->session_sid = $line["session_sid"];
		$obj->session_create_time = $line["session_create_time"];
		$obj->session_expire_time = $line["session_expire_time"];
		$obj->session_statut = $line["session_statut"];

		return $obj;
	}

}

function getExistSessionId($sid){
	$sql = "select session_id from student_session where session_sid=$sid and session_expire_time>CURRENT_TIMESTAMP";
	return dbGetObjByQuery($sql, function($line){
		return $line["session_id"];
	});

}

function getExistSession($sid, ...$whereClauses){
	array_push($whereClauses, "session_sid=$sid");
	return dbFindObj("StudentSession", ...$whereClauses);
}

/*
return no expired session of sid. if this not exist, create a new inoperation session for sid.	
*/
function getOrCreateSession($sid){
	$currentSession = getExistSession(
		$sid, 
		"session_expire_time>CURRENT_TIMESTAMP",
		"session_statut not in(".concat("", ",", SESSION_STATUT_EXPIRED, SESSION_STATUT_CANCELLED).")"
	);
	if($currentSession == null){
		$session_id = insertQuery(
			"insert into student_session(session_sid, session_expire_time, session_statut) value($sid, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL ".SESSION_DURATION."), ".SESSION_STATUT_INOPERATION.")"
		);

		if(!empty($session_id)){
			$currentSession = dbFindObj("StudentSession", "session_id=$session_id");
		}
	}

	return $currentSession;
}

function addOperation($tid, $sid, $categ_id, $tp_id, $week_nb, $day_nb, $begin_nb, $end_nb, $statut){
	$session_id = getExistSessionId($sid);
	$expire_time = "'2017-07-30'"; // for test
	if($session_id == null){
		$session_id = insertQuery(
			"insert into student_session(session_sid, session_expire_time) value($sid, $expire_time)"
		);
	}

	$res_id = 'NULL';
	if($statut==OPE_STATUT_TODELETE){ // insert test reservation
		$sql_insert_reservation = "insert into reservation(res_tid,res_sid,res_categ_id,res_tp_id,res_week_nb,res_day_nb,res_begin_nb,res_end_nb,res_statut,res_create_time) "
		."values($tid, $sid, $categ_id, $tp_id, $week_nb, $day_nb, $begin_nb, $end_nb, ".RES_STATUT_DELETING.", CURRENT_TIMESTAMP)";
		$res_id = insertQuery($sql_insert_reservation);	
	}

	$sql_insert_operation = "insert into student_operation(ope_session_id, ope_res_id, ope_tid, ope_categ_id,ope_tp_id,ope_week_nb,ope_day_nb,ope_begin_nb,ope_end_nb,ope_statut)"
		." values($session_id, $res_id, $tid, $categ_id, $tp_id, $week_nb, $day_nb, $begin_nb, $end_nb, $statut)";
	query($sql_insert_operation);

}


