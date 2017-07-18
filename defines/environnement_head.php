<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

// require_once('outside_includes/libs.php');

// datas
require_once('defines/tools.php');
require_once('defines/pages.php');

require_once('database/dbtools.php');
require_once('objs/categ.php');
require_once('objs/user.php');
require_once('objs/teacher_categ.php');


// views

if("html"==getPageType()){
	include_once('views/header.php');
}

