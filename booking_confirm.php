<?php
$page_name = "booking_confirm";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();
$user = dbFindByKey("User", $uid);


TODO("change session to finish statut (or delete session ?)");

header("Location: index.php?uid=$uid");