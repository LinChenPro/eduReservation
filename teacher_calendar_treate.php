<?php
$page_name = "teacher_calendar_treate";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();
$user = dbFindByKey("User", $uid);

$demand_action = $_REQUEST["action"];
$demand_uid = $_REQUEST["uid"];
$demand_week_nb = $_REQUEST["week_nb"];
$demand_timestamp = $_REQUEST["timestamp"];

/*
$demand_action = $_POST["action"];
$demand_uid = $_POST["uid"];
$demand_week_nb = $_POST["week_nb"];
$demand_timestamp = $_POST["timestamp"];
*/

if($demand_action==TCA_TYPE_LOAD){
	if(empty($demand_week_nb) || $demand_week_nb<=0){
		$demand_week_nb = dateToWeekNb();
	}
	$responseObj = loadTeacherCalendar($uid, $demand_week_nb);
	echo json_encode($responseObj);
}else if($demand_action==TCA_TYPE_UPDATE){
		$demand_from_day = $_REQUEST["from_day"];
		$demand_from_h = $_REQUEST["from_h"];
		$demand_to_day = $_REQUEST["to_day"];
		$demand_to_h = $_REQUEST["to_h"];
		$demand_to_statut = $_REQUEST["to_statut"]-0;

		$responseObj = updateTeacherCalendar(
			$uid, 
			$demand_week_nb, 
			$demand_from_day,
			$demand_from_h,
			$demand_to_day,
			$demand_to_h,
			$demand_to_statut
		);

		$responseObj->action = TCA_TYPE_UPDATE;
		echo json_encode($responseObj);
}else if($demand_action==TCA_TYPE_REFRESH){
	$has_change = false;
	for($i=0; $i<10; $i++){
		$has_change = checkChange($uid, $demand_week_nb, $demand_timestamp);
		if($has_change){
			break;
		}
		sleep(2);
	}

	if($has_change){
		$responseObj = loadTeacherCalendar($uid, $demand_week_nb);
		$responseObj->action = TCA_TYPE_REFRESH;
		echo json_encode($responseObj);
	}else{
		$responseObj = TeacherCalendarResponse::newInstance(
			$demand_uid, 
			TCA_TYPE_REFRESH, 
			$demand_week_nb, 
			$demand_timestamp, 
			true
		);
		$responseObj->infos = "not refreshed"; 
		echo json_encode($responseObj);	
	}
}


