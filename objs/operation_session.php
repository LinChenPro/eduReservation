<?php
/********** session functions **********/
TODO("add session statut : on_selection, waiting_payment_result, archive, user can change only when 'on_selection'");

define("SESSION_DURATION", "20 MINUTE");
define("SESSION_DEMANDE_ID_PARAM", "sm_demande_session_id");

define("SESSION_STATUT_INOPERATION", 1);	// begin statut
define("SESSION_STATUT_PAY_RESUME", 2);
define("SESSION_STATUT_PAY_START", 3);
define("SESSION_STATUT_PAY_WAITING", 4);
define("SESSION_STATUT_PAY_ERROR", 5);
define("SESSION_STATUT_PAY_FAILED", 6);
define("SESSION_STATUT_PAY_SUCCES", 7); 	// final statut
define("SESSION_STATUT_EXPIRED", 8);		// final statut
define("SESSION_STATUT_CANCELLED", 9);		// final statut

$sm_demande_session = null;
$sm_last_user_session = null;

$SESSION_FINAL_STATUS = array(SESSION_STATUT_PAY_SUCCES, SESSION_STATUT_EXPIRED, SESSION_STATUT_CANCELLED);
$SESSION_EXPIRABLE_STATUS = array(
	SESSION_STATUT_INOPERATION,SESSION_STATUT_PAY_RESUME,SESSION_STATUT_PAY_FAILED,SESSION_STATUT_PAY_ERROR
);

$page_session_infos = array(
	"student_booking" => array(
		'initSessionFun' => function(){
			global $sm_demande_session;
			global $sm_last_user_session;

			// 1 : if no session demande, use exited available last session
			if($sm_demande_session==null){
				if(!in_array($sm_last_user_session->session_statut, $SESSION_FINAL_STATUS)){
					$sm_demande_session = $sm_last_user_session; 
				}
			}

			// create new session only when student_booking page is demanded
			if($sm_demande_session==null || in_array($sm_demande_session->session_statut, $SESSION_FINAL_STATUS)){
				$sm_demande_session = createSession(getCurrentUid());
			}

			// 2 : different treatement due to de demande session's current statut
			if(in_array(
				$sm_demande_session->session_statut, 
				array(SESSION_STATUT_INOPERATION, SESSION_STATUT_PAY_RESUME)
			)){
				changeSessionStatut($sm_demande_session, SESSION_STATUT_INOPERATION);
			}else{
				loadPageBySession($sm_demande_session);
			}

		}
	),
	"student_booking_treate" => array(
		'initSessionFun' => function(){
		}
	),
	"booking_resume" => array(
		'initSessionFun' => function(){
			global $sm_demande_session;
			global $sm_last_user_session;

			// if no session demande, use exited available last session
			if($sm_demande_session==null){
				if(!in_array($sm_last_user_session->session_statut, $SESSION_FINAL_STATUS)){
					$sm_demande_session = $sm_last_user_session; 
				}
			}

			// return to home page if no available demande session
			if($sm_demande_session==null){
				$page_file = $page_infos["index"]["file"]."?uid=$uid";
				header("Location: $page_file");	
			}

			// 2 : different treatement due to de demande session's current statut
			if(in_array(
				$sm_demande_session->session_statut, 
				array(SESSION_STATUT_INOPERATION, SESSION_STATUT_PAY_RESUME)
			)){
				changeSessionStatut($sm_demande_session, SESSION_STATUT_RESUME);
			}else{
				loadPageBySession($sm_demande_session);
			}
		}
	),
	"booking_payment" => array(
		'initSessionFun' => function(){
			// if no session demande, use exited available last session
			if($sm_demande_session==null){
				if(!in_array($sm_last_user_session->session_statut, $SESSION_FINAL_STATUS)){
					$sm_demande_session = $sm_last_user_session; 
				}
			}

			// return to home page if no available demande session
			if($sm_demande_session==null){
				$page_file = $page_infos["index"]["file"]."?uid=$uid";
				header("Location: $page_file");	
			}

			// 2 : different treatement due to de demande session's current statut
			if(in_array(
				$sm_demande_session->session_statut, 
				array(SESSION_STATUT_PAY_RESUME)
			)){
				changeSessionStatut($sm_demande_session, SESSION_STATUT_PAY_START);
			}else{
				loadPageBySession($sm_demande_session);
			}

		}
	),
	"booking_payment_result" => array(
		'initSessionFun' => function(){
			// if no session demande, use exited available last session
			if($sm_demande_session==null){
				if(!in_array($sm_last_user_session->session_statut, $SESSION_FINAL_STATUS)){
					$sm_demande_session = $sm_last_user_session; 
				}
			}

			// return to home page if no available demande session
			if($sm_demande_session==null){
				$page_file = $page_infos["index"]["file"]."?uid=$uid";
				header("Location: $page_file");	
			}

			// 2 : different treatement due to de demande session's current statut
			if(!in_array(
				$sm_demande_session->session_statut, 
				array(SESSION_STATUT_PAY_START, SESSION_STATUT_PAY_SUCCES, SESSION_STATUT_PAY_ERROR, SESSION_STATUT_PAY_FAILED)
			)){
				loadPageBySession($sm_demande_session);
			}
		}
	),
	"booking_confirm" => array(
		'initSessionFun' => function(){
			// if no session demande, use exited available last session
			if($sm_demande_session==null){
				if(!in_array($sm_last_user_session->session_statut, $SESSION_FINAL_STATUS)){
					$sm_demande_session = $sm_last_user_session; 
				}
			}

			// return to home page if no available demande session
			if($sm_demande_session==null){
				$page_file = $page_infos["index"]["file"]."?uid=$uid";
				header("Location: $page_file");	
			}

		}
	)
);

class StudentSession{
	public $session_id;
	public $session_sid;
	public $session_create_time;
	public $session_expire_time;
	public $session_statut;

	public static $TABLE_NAME = "student_session";
	public static $TABLE_KEY = "session_id";

	static public function dbLineToObj($line){
		$obj = new StudentSession();
		$obj->session_id = $line["session_id"];
		$obj->session_sid = $line["session_sid"];
		$obj->session_create_time = $line["session_create_time"];
		$obj->session_expire_time = $line["session_expire_time"];
		$obj->session_statut = $line["session_statut"];

		return $obj;
	}

	public function isExpireTimePassed(){
		global $CURRENT_DATETIME;
		return getDbDateTime($this->session_expire_time)<$CURRENT_DATETIME; 
	}

	public function needBeExpired(){
		global $SESSION_EXPIRABLE_STATUS;
		return isExpireTimePassed && in_array($this->session_statut, $SESSION_EXPIRABLE_STATUS); 	
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

/* not use this function
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

function createSession($sid){
	$session_id = insertQuery(
		"insert into student_session(session_sid, session_expire_time, session_statut) value($sid, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL ".SESSION_DURATION."), ".SESSION_STATUT_INOPERATION.")"
	);

	if(!empty($session_id)){
		return dbFindObj("StudentSession", "session_id=$session_id");
	}else{
		return null;
	}
}


// no use of this function
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

function getDemandeSessionId(){
	return $demande_session_id = $_REQUEST[SESSION_DEMANDE_ID_PARAM];
}

function getDemandeSession(){
	$demande_session_id = getDemandeSessionId();

	if(empty($demande_session_id)){
		return null;
	}

	return dbFindByKey("StudentSession", $demande_session_id);
}

function getCrtUserLastSession(){
	$uid = getCurrentUid();
	if(empty($uid)){
		return null;
	}
	return getStudentLastSession($uid);
}

function getStudentLastSession($sid){
	return dbFindObj("StudentSession", "session_sid=$sid", "session_id in(select max(session_id from student_session where session_sid=$sid))");
}

function changeSessionStatut(&$session, $statut){
	query("update student_session set session_statut=$statut where session_id=".$session->session_sid);
	$session->session_statut = $statut;
}

function loadPageBySession($session){
	$page_file = null;
	global $page_infos;
	if($session->session_statut==SESSION_STATUT_INOPERATION){
		$page_file = $page_infos["student_booking"]["file"];
	}else if($session->session_statut==SESSION_STATUT_PAY_RESUME){
		$page_file = $page_infos["booking_resume"]["file"];
	}else if($session->session_statut==SESSION_STATUT_PAY_START){
		$page_file = $page_infos["booking_payment"]["file"];
	}else if($session->session_statut==SESSION_STATUT_PAY_WAITING){
		$page_file = $page_infos["booking_payment_waiting"]["file"];
	}else if($session->session_statut==SESSION_STATUT_PAY_ERROR){
		$page_file = $page_infos["booking_payment_result"]["file"];
	}else if($session->session_statut==SESSION_STATUT_PAY_FAILED){
		$page_file = $page_infos["booking_payment_result"]["file"];
	}else if($session->session_statut==SESSION_STATUT_PAY_SUCCES){
		$page_file = $page_infos["booking_payment_result"]["file"];
	}

	if($page_file==null){
		$page_file = $page_infos["index"]["file"]."?uid=$uid";
	}else{
		$page_file .= "?uid=$uid&".SESSION_DEMANDE_ID_PARAM."=".$session->session_id;
	}

	header("Location: $page_file");	
}

function initSessionSituation(){
	global $sm_demande_session;
	global $sm_last_user_session;
	global $sm_current_alive_session;
	global $page_session_infos;
	global $page_name;

	$sm_last_user_session = getCrtUserLastSession();
	if($sm_last_user_session != null && $sm_last_user_session->needBeExpired()){
		changeSessionStatut($sm_last_user_session, SESSION_STATUT_EXPIRED);
	}
	$sm_demande_session = getDemandeSession();
	$page_session_infos[$page_name]["initSessionFun"]();
}



