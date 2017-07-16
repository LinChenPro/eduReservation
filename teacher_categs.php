<?php
$page_name = "teacher_categs";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();


$categ = dbFindByKey("Categ", 1);

$teacherCategs = Categ::getCategs();
foreach ($teacherCategs as $key => $value) {
	echo $value->categ_name."<br>";
}

?>



<?php
include_once('defines/environnement_foot.php');

