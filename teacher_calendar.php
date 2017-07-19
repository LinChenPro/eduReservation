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
<button id="sendDemande">send</button>
<pre id="showData" style="width:80%;margin:10px;padding:10px;border:1px solid black"></pre>


<script type="text/javascript">
var uid=<?=$uid?>;
var demandeUrl = "/teacher_calendar_treate.php"
var week_nb = null;

function sentTCDemand(d1, t1, d2, t2){
	var demande = { 'action' : "update", 'uid' : uid, 'week_nb' : week_nb, 'd1': d1,'t1' : t1,'d2' : d2,'t2' : t2 };
	$.post(demandeUrl, demande, treateUpdateResponse, "json");
}

function loadTCData(demande_week_nb){
	if(demande_week_nb!=null){
		week_nb = demande_week_nb;
	}
	send_week_nb = demande_week_nb==null?week_nb:demande_week_nb;
	var demande = { 'action' : "load", 'uid' : uid, 'week_nb' : send_week_nb};
	$.post(demandeUrl, demande, treateLoadResponse, "json");
}

function autoTCRefresh(){
	var demande = { 'action' : "refresh", 'uid' : uid, 'week_nb' : week_nb};
	$.post(demandeUrl, demande, treateRefreshResponse, "json");
}

function treateUpdateResponse(responseData){
	showScheduleData(responseData);
	week_nb = responseData["week_nb"];
}

function treateLoadResponse(responseData){
	showScheduleData(responseData);
	week_nb = responseData["week_nb"];
}

function treateRefreshResponse(responseData){
	if(responseData["week_nb"] == week_nb){
		showScheduleData(responseData);
	}
	autoTCRefresh();
}

function showScheduleData(schedule){
	$("#showData").html(schedule.action+" week_nb="+schedule.week_nb);
}

$("#sendDemande").click(function(){
	sentTCDemand(1, 1, 2, 2);
});

$("#week_pre").click(function(){
	loadTCData(week_nb-1);
});

$("#week_next").click(function(){
	loadTCData(week_nb-(-1));
});

loadTCData();
autoTCRefresh();

</script>

<?php
include_once('defines/environnement_foot.php');

