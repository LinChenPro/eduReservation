<?php
$page_name = "teacher_categs_update";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();


$user = dbFindByKey("User", $uid);
$teacherCategs = TeacherCateg::getTeacherCategs($uid);

$selected_array = $_REQUEST["categ_id"];

/* ------  put in TeacherCateg  ------ */
$not_selected_condition = empty($selected_array)? "" : " and tc_categ_id not in(".concat("-1,", ",", ...$selected_array).")";
query("update teacher_categ set tc_expire_time=CURRENT_TIMESTAMP where tc_expire_time is null and tc_tid=$uid".$not_selected_condition);
foreach ($selected_array as $categ_id) {
	query("INSERT INTO teacher_categ(tc_tid, tc_categ_id) VALUES($uid, $categ_id) ON DUPLICATE KEY UPDATE tc_expire_time=NULL");
}

foreach ($teacherCategs as $tc) {
	$categ_id = $tc->categ->categ_id;
	$old_prise = $tc->tp_prise;
	$new_prise = $_REQUEST["categ_prise_$categ_id"];
	if(!empty($new_prise)){
		$new_prise *= 100;
	}

	if($old_prise != $new_prise && !(empty($old_prise) && empty($new_prise))){
		if(empty($new_prise)){
			$new_prise = "NULL";
		}
		query("insert into teacher_prise(tp_tid, tp_categ_id, tp_prise) values($uid, $categ_id, $new_prise)");
	}
}
/* --------------  */

//$teacherCategs = TeacherCateg::getTeacherCategs($uid);

header("Location: teacher_categs.php?uid=$uid");