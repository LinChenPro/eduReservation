<?php
$page_name = "student_booking_treate";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();
$user = dbFindByKey("User", $uid);
$currentSession = getOrCreateSession($uid);
if($currentSession->session_statut!=SESSION_STATUT_INOPERATION){
	TODO("booking : forbidden normal operations to session in special statut");
}

$demand_action = $_REQUEST["action"];
$demand_tid = $_REQUEST["tid"];
$demand_sid = $_REQUEST["sid"];
$demand_categ_id = $_REQUEST["categ_id"];
$demand_week_nb = $_REQUEST["week_nb"];
$demand_timestamp = $_REQUEST["timestamp"];

/*
$demand_action = $_POST["action"];
$demand_uid = $_POST["uid"];
$demand_week_nb = $_POST["week_nb"];
$demand_timestamp = $_POST["timestamp"];
*/

if($demand_action==SBK_TYPE_TEACHERLIST){
	if(empty($demand_categ_id)){
		echo json_encode(array());
	}else{
		// no need to block, but maybe need a time stamp to read the update.
		$responseObj = getCurrentCategTeachers($demand_categ_id, $demand_sid, $currentSession->session_create_time);
		if($responseObj==null){
			$responseObj = array();
		}
		echo json_encode($responseObj);
	}

}else if($demand_action==SBK_TYPE_LOAD){
	if(empty($demand_week_nb) || $demand_week_nb<=0){
		$demand_week_nb = dateToWeekNb();
	}
	$responseObj = loadStudentCalendar($demand_tid, $demand_sid, $demand_categ_id, $demand_week_nb);

	// no need to block reading.
//	$locks = getStampLocks($demand_sid, $demand_week_nb, $demand_tid);
//	$responseObj = doTransaction($locks, "loadStudentCalendar", array($demand_tid, $demand_sid, $demand_categ_id, $demand_week_nb));

	echo json_encode($responseObj);
}else if($demand_action==SBK_TYPE_REFRESH){
	$has_change = false;
	for($i=0; $i<10; $i++){
		$has_change = checkChange($demand_sid, $demand_week_nb, $demand_timestamp);
		if(!$has_change && $demand_tid != null){
			$has_change = checkChange($demand_tid, $demand_week_nb, $demand_timestamp);
		} 
		if($has_change){
			break;
		}
		sleep(2);
	}

	if($has_change){
		// no need to block
		$responseObj = loadStudentCalendar($demand_tid, $demand_sid, $demand_categ_id, $demand_week_nb);
		$responseObj->action = SBK_TYPE_REFRESH;
		echo json_encode($responseObj);
	}else{
		$responseObj = StudentCalendarResponse::newInstance(
			$demand_tid, 
			$demand_sid, 
			$demand_categ_id, 
			SBK_TYPE_REFRESH, 
			$demand_week_nb, 
			$demand_timestamp, 
			true
		);
		$responseObj->infos = "not refreshed"; 
		echo json_encode($responseObj);	
	}
}else if($demand_action==SBK_TYPE_RESTORE){
		$demand_ope_id = $_REQUEST["ope_id"];
		$demand_res_id = $_REQUEST["res_id"];

		//missing params : $demand_week_ope, $demand_lesson_tid, $demand_week_res
		$locks = getStampLocks($demand_sid, $demand_week_ope, $demand_tid, $demand_week_res);
		$restoreResult = doTransaction($locks, "restoreReservation", array($demand_ope_id, $demand_res_id));

		$responseObj = loadStudentCalendar($demand_tid, $demand_sid, $demand_categ_id, $demand_week_nb);
		$responseObj->action = SBK_TYPE_RESTORE;
		$responseObj->succes = $restoreResult->succes;
		$responseObj->error = $restoreResult->error;
		$responseObj->infos = $restoreResult->infos;

		echo json_encode($responseObj);
}else if($demand_action==SBK_TYPE_DELETERES){
		$demand_res_id = $_REQUEST["res_id"];

		//missing params : $demand_week_res, $demand_lesson_tid
		$locks = getStampLocks($demand_sid, $demand_week_res, $demand_tid);
		$deleteResResult = doTransaction($locks, "deleteReservation", array($demand_res_id));

		$responseObj = loadStudentCalendar($demand_tid, $demand_sid, $demand_categ_id, $demand_week_nb);
		$responseObj->action = SBK_TYPE_DELETERES;
		$responseObj->succes = $deleteResResult->succes;
		$responseObj->error = $deleteResResult->error;
		$responseObj->infos = $deleteResResult->infos;

		echo json_encode($responseObj);
}else if($demand_action==SBK_TYPE_CANCELOPE){
		$demand_ope_id = $_REQUEST["ope_id"];

		//missing params : $demand_week_ope, $demand_lesson_tid
		$locks = getStampLocks($demand_sid, $demand_week_ope, $demand_lesson_tid);
		$cancelOpeResult = doTransaction($locks, "cancelOperation", array($demand_ope_id));

		$responseObj = loadStudentCalendar($demand_tid, $demand_sid, $demand_categ_id, $demand_week_nb);
		$responseObj->action = SBK_TYPE_CANCELOPE;
		$responseObj->succes = $cancelOpeResult->succes;
		$responseObj->error = $cancelOpeResult->error;
		$responseObj->infos = $cancelOpeResult->infos;

		echo json_encode($responseObj);
}else if($demand_action==SBK_TYPE_CREATE){
		$demand_day_nb = $_REQUEST["day_nb"];
		$demand_from_h = $_REQUEST["from_h"];
		$demand_to_h = $_REQUEST["to_h"];
		$demand_tp_id = $_REQUEST["tp_id"];

		$locks = getStampLocks($demand_sid, $demand_week_nb, $demand_tid);
		$createOpeResult = doTransaction($locks, "createOperation", array($demand_categ_id, $demand_tid, $demand_sid, $demand_day_nb, $demand_from_h, $demand_to_h, $demand_tp_id));

		$responseObj = loadStudentCalendar($demand_tid, $demand_sid, $demand_categ_id, $demand_week_nb);
		$responseObj->action = SBK_TYPE_CREATE;
		$responseObj->succes = $createOpeResult->succes;
		$responseObj->error = $createOpeResult->error;
		$responseObj->infos = $createOpeResult->infos;

		echo json_encode($responseObj);
}

