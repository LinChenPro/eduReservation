<?php
$folder_root = "/";

$page_infos = array(
	"index" => array(
		'title' => "main page", 
		'file' => "index.php",
		'type' => "html"
	),
	"teacher_categs" => array(
		'title' => "teacher's categs", 
		'file' => "teacher_categs.php",
		'type' => "html"
	),
	"teacher_categs_update" => array(
		'title' => "teacher's categs", 
		'file' => "teacher_categs_update.php",
		'type' => "data"
	),
	"teacher_calendar" => array(
		'title' => "teacher's calendar", 
		'file' => "teacher_calendar.php",
		'css' => array("/css/main.css", "/css/calendar.css"),
		'type' => "html"
	),
	"teacher_calendar_treate" => array(
		'title' => "teacher's calendar", 
		'file' => "teacher_calendar_treate.php",
		'type' => "ajax"
	),
	"student_booking" => array(
		'title' => "select your lessons", 
		'file' => "student_booking.php",
		'css' => array("/css/main.css", "/css/booking.css"),
		'type' => "html"
	),
	"student_booking_treate" => array(
		'title' => "select your lessons", 
		'file' => "student_booking_treate.php",
		'type' => "ajax"
	),
	"booking_resume" => array(
		'title' => "resume of booking selections", 
		'file' => "booking_resume.php",
		'css' => array("/css/main.css"),
		'type' => "html"
	),
	"booking_payment" => array(
		'title' => "entry of booking payment", 
		'file' => "booking_payment.php",
		'css' => array("/css/main.css"),
		'type' => "html"
	),
	"booking_payment_result" => array(
		'title' => "result of booking payment", 
		'file' => "booking_payment_result.php",
		'css' => array("/css/main.css"),
		'type' => "html"
	),
	"booking_confirm" => array(
		'title' => "confirm_booking_payment", 
		'file' => "booking_confirm.php",
		'type' => "data"
	)
);

function getCrtPageTitle(){
	global $page_infos;
	global $page_name;
	return $page_infos[$page_name]["title"];
}

function getCrtPageCss(){
	global $page_infos;
	global $page_name;
	return $page_infos[$page_name]["css"];
}

function showAllPageLinks(){
	global $page_infos;
	global $folder_root;

	foreach ($page_infos as $name => $page) {
		if("html"==$page['type']){
			$link = $folder_root.$page['file'];
			
			$uid = getCurrentUid();
			$link .= concat("?", "&", empty($uid)?null:"uid=".$uid);

			echo '<a href="'.$link.'">'.$page['title'].'</a><br>';
		}
	}	
}

function getCurrentUid(){
	return $_REQUEST["uid"];
}

function getPageType(){
	global $page_infos;
	global $page_name;
	return $page_infos[$page_name]["type"];
}