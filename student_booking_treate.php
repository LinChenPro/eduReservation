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
}else if($demand_action==SBK_TYPE_MOVE){
		$lesson_tid = $_REQUEST["lesson_tid"];
		$lesson_sid = $_REQUEST["lesson_sid"];
		$lesson_ope_id = $_REQUEST["lesson_ope_id"];
		$lesson_res_id = $_REQUEST["lesson_res_id"];
		$orig_week = $_REQUEST["orig_week"];
		$dest_week = $_REQUEST["dest_week"];
		$dest_day_nb = $_REQUEST["dest_day_nb"];
		$dest_begin_h = $_REQUEST["dest_begin_h"];
		$dest_end_h = $_REQUEST["dest_end_h"];



		//missing params : $demand_week_ope, $demand_lesson_tid, $demand_week_res
		$locks = getStampLocks($lesson_tid, $orig_week, $lesson_sid, $dest_week);
		$restoreResult = doTransaction($locks, "moveLessonTo", array($lesson_ope_id, $lesson_res_id, $lesson_tid, $lesson_sid, $dest_week, $dest_day_nb, $dest_begin_h, $dest_end_h));

		$responseObj = loadStudentCalendar($demand_tid, $demand_sid, $demand_categ_id, $demand_week_nb);
		$responseObj->action = SBK_TYPE_MOVE;
		$responseObj->succes = $restoreResult->succes;
		$responseObj->error = $restoreResult->error;
		$responseObj->infos = $restoreResult->infos;

		echo json_encode($responseObj);
}else if($demand_action==SBK_TYPE_RESTORE){
		$lesson_tid = $_REQUEST["lesson_tid"];
		$lesson_sid = $_REQUEST["lesson_sid"];
		$lesson_ope_id = $_REQUEST["lesson_ope_id"];
		$lesson_res_id = $_REQUEST["lesson_res_id"];
		$lesson_ope_week = $_REQUEST["lesson_ope_week"];
		$lesson_res_week = $_REQUEST["lesson_res_week"];

		//missing params : $demand_week_ope, $demand_lesson_tid, $demand_week_res
		$locks = getStampLocks($lesson_tid, $lesson_ope_week, $lesson_sid, $lesson_res_week);
		$restoreResult = doTransaction($locks, "restoreReservation", array($lesson_ope_id, $lesson_res_id));

		$responseObj = loadStudentCalendar($demand_tid, $demand_sid, $demand_categ_id, $demand_week_nb);
		$responseObj->action = SBK_TYPE_RESTORE;
		$responseObj->succes = $restoreResult->succes;
		$responseObj->error = $restoreResult->error;
		$responseObj->infos = $restoreResult->infos;

		echo json_encode($responseObj);
}else if($demand_action==SBK_TYPE_DELETERES){
		$lesson_tid = $_REQUEST["lesson_tid"];
		$lesson_sid = $_REQUEST["lesson_sid"];
		$lesson_res_id = $_REQUEST["lesson_res_id"];
		$lesson_res_week = $_REQUEST["lesson_res_week"];

		//missing params : $demand_week_res, $demand_lesson_tid
		$locks = getStampLocks($lesson_sid, $lesson_res_week, $lesson_tid);
		$deleteResResult = doTransaction($locks, "deleteReservation", array($lesson_res_id));

		$responseObj = loadStudentCalendar($demand_tid, $demand_sid, $demand_categ_id, $demand_week_nb);
		$responseObj->action = SBK_TYPE_DELETERES;
		$responseObj->succes = $deleteResResult->succes;
		$responseObj->error = $deleteResResult->error;
		$responseObj->infos = $deleteResResult->infos;

		echo json_encode($responseObj);
}else if($demand_action==SBK_TYPE_CANCELOPE){
		$lesson_tid = $_REQUEST["lesson_tid"];
		$lesson_sid = $_REQUEST["lesson_sid"];
		$lesson_ope_id = $_REQUEST["lesson_ope_id"];
		$lesson_ope_week = $_REQUEST["lesson_ope_week"];

		//missing params : $demand_week_ope, $demand_lesson_tid
		$locks = getStampLocks($lesson_sid, $lesson_ope_week, $lesson_tid);
		$cancelOpeResult = doTransaction($locks, "cancelOperation", array($lesson_ope_id));

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

