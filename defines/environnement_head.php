<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

$debug = /** /1/*/0/**/;

ini_set("display_errors", $debug ? "on" : "off");
ini_set('max_execution_time', 80);

// require_once('outside_includes/libs.php');

// datas
require_once('tools/string_tools.php');
require_once('tools/date_tools.php');
require_once('defines/pages.php');

require_once('database/dbtools.php');
require_once('objs/categ.php');
require_once('objs/user.php');
require_once('objs/teacher_categ.php');
require_once('objs/operation_session.php');
require_once('objs/teacher_calendar.php'); //?
require_once('objs/student_booking.php');  //?


// views

if("html"==getPageType()){
	include_once('views/header.php');
}

