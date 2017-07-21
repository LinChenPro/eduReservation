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
if($demand_action=="load"){
	if(empty($demand_week_nb)){
		$demand_week_nb = dateToWeekNb();
	}
	$reponseObj = loadTeacherCalendar($uid, $demand_week_nb);
	echo json_encode($reponseObj);
}else if($demand_action=="update"){
	$reponseObj->action = $demand_action;
	$reponseObj->week_nb = $demand_week_nb;
	$reponseObj->timestamp = "";
	$reponseObj->schedule_data = array(1,2,3,4);
	$reponseObj->succes = true;
	$reponseObj->error = null;
	echo json_encode($reponseObj);	
}else if($demand_action=="refresh"){
	$has_change = false;
	for($i=0; $i<10; $i++){
		$has_change = false;//checkChange($demand_timestamp);
		if($has_change){
			break;
		}
		sleep(2);
	}

	if($has_change){
		$reponseObj->action = $demand_action;
		$reponseObj->week_nb = $demand_week_nb;
		$reponseObj->timestamp = "";
		$reponseObj->schedule_data = array(1,2,3,4);
		$reponseObj->succes = true;
		$reponseObj->error = null;
		echo json_encode($reponseObj);	
	}else{
		$reponseObj->action = $demand_action;
		$reponseObj->week_nb = $demand_week_nb;
		$reponseObj->timestamp = $demand_week_nb;
		$reponseObj->succes = true;
		$reponseObj->error = null;
		echo json_encode($reponseObj);	
	}
}


