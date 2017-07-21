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
var data_stamp = null;
var queryLoad = null;
var queryUpdate = null;
var queryRefresh = null;

function checkMultyAjax(){
	return (queryLoad == null && queryUpdate == null);
}

function abortRefreshAjax(){
	if(queryRefresh != null){
		queryRefresh.abort();
	}
}

function sentTCDemand(d1, t1, d2, t2){
	if(checkMultyAjax()){
		var demande = { 'action' : "<?=TCA_TYPE_UPDATE?>", 'uid' : uid, 'week_nb' : week_nb, 'd1': d1,'t1' : t1,'d2' : d2,'t2' : t2 };
		queryUpdate = $.post(demandeUrl, demande, treateUpdateResponse, "json");
	}
}

function loadTCData(demande_week_nb){
	if(checkMultyAjax()){
		if(demande_week_nb!=null){
			week_nb = demande_week_nb;
		}
		send_week_nb = demande_week_nb==null?week_nb:demande_week_nb;
		var demande = { 'action' : "<?=TCA_TYPE_LOAD?>", 'uid' : uid, 'week_nb' : send_week_nb};

		queryLoad = $.post(demandeUrl, demande, treateLoadResponse, "json");
	}	
}

function autoTCRefresh(){
	if(checkMultyAjax()){
		var demande = { 'action' : "<?=TCA_TYPE_REFRESH?>", 'uid' : uid, 'week_nb' : week_nb, 'timestamp' : data_stamp};
		queryRefresh = $.post(demandeUrl, demande, treateRefreshResponse, "json");
	}
}

function treateUpdateResponse(responseData){
	showScheduleData(responseData);
	queryUpdate = null;
	autoTCRefresh();
}

function treateLoadResponse(responseData){
	showScheduleData(responseData);
	queryLoad = null;
	autoTCRefresh();
}

function treateRefreshResponse(responseData){
	if(responseData["week_nb"] == week_nb && responseData["timestamp"]>data_stamp){
		showScheduleData(responseData);
	}
	queryRefresh = null;
	autoTCRefresh();
}

function showScheduleData(responseData){
	week_nb = responseData["week_nb"];
	data_stamp = responseData["timestamp"];

	$("#week_title").html(responseData.week_nb);
	$("#showData").html(JSON.stringify(responseData));
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

</script>

<?php
include_once('defines/environnement_foot.php');

