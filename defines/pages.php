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
		'file' => "teacher_categs.php",
		'type' => "data"
	)
);

function getCrtPageTitle(){
	global $page_infos;
	global $page_name;
	return $page_infos[$page_name]["title"];
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