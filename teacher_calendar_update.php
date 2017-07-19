<?php
$page_name = "teacher_categs_update";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();
$user = dbFindByKey("User", $uid);

header("Location: teacher_categs.php?uid=$uid");