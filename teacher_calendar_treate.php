<?php
$page_name = "teacher_calendar_treate";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();
$user = dbFindByKey("User", $uid);

$demand_action = $_POST["action"];
$demand_uid = $_POST["uid"];
$demand_week_nb = $_POST["week_nb"];

$reponseObj = new stdClass();
if($demand_action=="load"){
	if(empty($demand_week_nb)){
		$demand_week_nb = 1;
	}

	$reponseObj->action = $demand_action;
	$reponseObj->week_nb = $demand_week_nb;
	$reponseObj->schedule_data = array(1,2,3,4);

	echo json_encode($reponseObj);
}else if($demand_action=="update"){
	$reponseObj->action = $demand_action;
	$reponseObj->week_nb = $demand_week_nb;
	$reponseObj->schedule_data = array(1,2,3,4);
	$reponseObj->succes = true;
	$reponseObj->error = null;
	echo json_encode($reponseObj);	
}else if($demand_action=="refresh"){

	sleep(5);//sleep(rand(3,8));
	$reponseObj->action = $demand_action;
	$reponseObj->week_nb = $demand_week_nb;
	$reponseObj->schedule_data = array(1,2,3,4);
	$reponseObj->succes = true;
	$reponseObj->error = null;
	echo json_encode($reponseObj);	
}


