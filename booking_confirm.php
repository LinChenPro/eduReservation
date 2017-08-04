<?php
$page_name = "booking_confirm";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();
$user = dbFindByKey("User", $uid);

$payment_succes = $_REQUEST["succes"];
$do_cancel = $_REQUEST["do_cancel"];
$do_move = $_REQUEST["do_move"];

if($payment_succes==0){
	if($do_cancel==1){
		TODO("do cancel options");
	}

	if($do_move==1){
		TODO("do move options");
	}
}

TODO("change session to finish statut (or delete session ?)");

header("Location: student_booking.php?uid=$uid&mode=read");