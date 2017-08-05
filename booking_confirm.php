<?php
$page_name = "booking_confirm";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();
$user = dbFindByKey("User", $uid);
$session = getExistSession($uid);

$payment_succes = $_REQUEST["succes"];
$do_delete = $_REQUEST["do_delete"];
$do_move = $_REQUEST["do_move"];

if($payment_succes==0){
	$arrApply = array();
	$arrAbandon = array(OPE_STATUT_TOCREATE);

	if($do_delete==1){
		array_push($arrApply, OPE_STATUT_TODELETE);
	}else{
		array_push($arrAbandon, OPE_STATUT_TODELETE);
	}

	if($do_move==1){
		array_push($arrApply, OPE_STATUT_TOMOVE);		
	}else{
		array_push($arrAbandon, OPE_STATUT_TOMOVE);
	}

	applyOperations($session->session_id, ...$arrApply);
	abandonOperations($session->session_id, ...$arrAbandon);
}


TODO("change session to finish statut (or delete session ?)");

header("Location: student_booking.php?uid=$uid&mode=read");