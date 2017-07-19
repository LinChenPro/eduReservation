<?php
$page_name = "teacher_calendar";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();
$user = dbFindByKey("User", $uid);

?>

<script src="/js/jquery-3.2.1.min.js"></script>

<div> teacher : <?=$user->user_name?></div>


<div id="teacher_calendar_div">
	<div id="week_head">
		<a id="week_pre"> < </a>
		<span id="week_title"></span>
		<a id="week_next"> > </a>
	</div>	

</div>

<script type="text/javascript">
var crt_calendar_data;

alert($("#week_head"));
/*
loadTCData();
autoTCRefresh();
sentTCDemand();
*/

</script>

<?php
include_once('defines/environnement_foot.php');

