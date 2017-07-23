<?php
$page_name = "teacher_calendar";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();
$user = dbFindByKey("User", $uid);

?>

<script src="/js/jquery-3.2.1.min.js"></script>

<div> teacher : <?=$user->user_name?></div>


<div id="week_head">
	<a id="week_pre"> < </a>
	<span id="week_title"></span>
	<a id="week_next"> > </a>
</div>
<div id="teacher_calendar_div" style="position:relative;">
	<div id="res_ope_div"></div>
	<div id="selection_div" style="z-index:1"></div>
	<table id="cal_table" style="z-index:100;position:relative;">
		<tr>
			<th></th>
			<th id="cal_d_0"></th>
			<th id="cal_d_1"></th>
			<th id="cal_d_2"></th>
			<th id="cal_d_3"></th>
			<th id="cal_d_4"></th>
			<th id="cal_d_5"></th>
			<th id="cal_d_6"></th>
		</tr>
<?php
for($h=0; $h<48; $h++){
?>		
		<tr id="caltr_<?=$h?>"><td id="cal_h_<?=$h?>"><?=hNbToCreneax($h)?></td></tr>
<?php
}
?>		
	</table>
</div>
<button id="sendSelect">select</button>
<button id="sendRemove">remove</button>
<pre id="showData" style="width:80%;margin:10px;padding:10px;border:1px solid black"></pre>


<script type="text/javascript">
var uid=<?=$uid?>;
var demandeUrl = "/teacher_calendar_treate.php"
var week_nb = null;
var week_first_day = null;
var data_stamp = null;
var queryLoad = null;
var queryUpdate = null;
var queryRefresh = null;

var selection_begin = null;
var selection_end = null;
function select_begin(elm){
	var obj = $(elm);
	selection_begin = {day:obj.data("day"), h:obj.data("h")};
	selection_end = selection_begin;
	showSelection();
}

function select_end(elm){
	var obj = $(elm);
	selection_end = {day:obj.data("day"), h:obj.data("h")};
	showSelection();
}

function showSelection(){
	var selection_div = $("#selection_div");
	if(selection_begin!=null && selection_end!=null){

		var d_min = Math.min(selection_begin.day, selection_end.day);
		var d_max = Math.max(selection_begin.day, selection_end.day);
		var h_min = Math.min(selection_begin.h, selection_end.h);
		var h_max = Math.max(selection_begin.h, selection_end.h);

		var obj_lt = $("#cal_"+(d_min-week_first_day)+"_"+h_min);
		var obj_rb = $("#cal_"+(d_max-week_first_day)+"_"+h_max);
		var p_lt = obj_lt.position();
		var p_rb = obj_rb.position();

		$("#selection_div")
		.css("left",p_lt.left)
		.css("top",p_lt.top)
		.css("width", p_rb.left-p_lt.left+obj_rb.outerWidth())
		.css("height",p_rb.top-p_lt.top+obj_rb.outerHeight());

		selection_div.css("display","block");
	}else{
		selection_div.css("display","none");
	}
}

function initPresentationElements(){
	for(var h=0; h<48; h++){
		var tds = "";
		for(var d=0; d<7; d++){
			tds += '<td id="cal_'+d+'_'+h+'" class="cal_cell"></td>';
		}
		$("#caltr_"+h).append(tds);
	}

	$(".cal_cell").mousedown(function(){select_begin(this)}).mouseup(function(){select_end(this)});
}

function checkMultyAjax(){
	return (queryLoad == null && queryUpdate == null);
}

function abortRefreshAjax(){
	if(queryRefresh != null){
		queryRefresh.abort();
	}
}

function sentTCDemand(d1, t1, d2, t2, to_statut){
	if(checkMultyAjax()){
		abortRefreshAjax();

		var demande = { 'action' : "<?=TCA_TYPE_UPDATE?>", 'uid' : uid, 'week_nb' : week_nb, 'from_day': d1,'from_h' : t1,'to_day' : d2,'to_h' : t2, 'to_statut': to_statut };
		queryUpdate = $.post(demandeUrl, demande, treateUpdateResponse, "json");
	}
}

function loadTCData(demande_week_nb){
	if(checkMultyAjax()){
		abortRefreshAjax();

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
		abortRefreshAjax();

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
	week_first_day = responseData["week_first_day"];
	data_stamp = responseData["timestamp"];

	// show schedule
	var schedules = responseData["schedule_data"];
	for(var d=0; d<7; d++){
		var schedule = schedules[d];
		$("#cal_d_"+d).html(schedule.day_str);

		for(var h=0; h<48; h++){
			$("#cal_"+d+"_"+h).attr("data-statut", schedule.day_status[h])
			.attr("data-day", schedule.day_nb)
			.attr("data-h", h)
			.attr("data-type", "");
		}
	}

	// show reservations
	var reservations = responseData["reservation_data"];
	if(reservations != null){
		for(var i=0; i<reservations.length; i++){
			var reservation = reservations[i];
			var d = reservation.day_nb - week_first_day;
			var type = "res_"+(reservation.tid==uid?"t":"s");
			for(var h=reservation.begin_nb; h<=reservation.end_nb; h++){
				$("#cal_"+d+"_"+h).attr("data-type", type);
			}
		}
	}

	// show operations
	var operations = responseData["operation_data"];
	if(operations != null){
		for(var i=0; i<operations.length; i++){
			var operation = operations[i];
			var d = operation.day_nb - week_first_day;
			var type = "ope_";
			type += (operation.tid==uid?"t":"s");
			type += "_"+operation.statut;
			for(var h=operation.begin_nb; h<=operation.end_nb; h++){
				$("#cal_"+d+"_"+h).attr("data-type", type);
			}
		}
	}

	$("#week_title").html(responseData.week_nb);
	$("#showData").html(JSON.stringify(responseData));
}

$("#sendSelect").click(function(){
	if(selection_begin!=null && selection_end!=null){
		var d_min = Math.min(selection_begin.day, selection_end.day);
		var d_max = Math.max(selection_begin.day, selection_end.day);
		var h_min = Math.min(selection_begin.h, selection_end.h);
		var h_max = Math.max(selection_begin.h, selection_end.h);

		sentTCDemand(d_min, h_min, d_max, h_max, 1);
	}
});

$("#sendRemove").click(function(){
	if(selection_begin!=null && selection_end!=null){
		var d_min = Math.min(selection_begin.day, selection_end.day);
		var d_max = Math.max(selection_begin.day, selection_end.day);
		var h_min = Math.min(selection_begin.h, selection_end.h);
		var h_max = Math.max(selection_begin.h, selection_end.h);

		sentTCDemand(d_min, h_min, d_max, h_max, 0);
	}
});

$("#week_pre").click(function(){
	loadTCData(week_nb-1);
});

$("#week_next").click(function(){
	loadTCData(week_nb-(-1));
});

initPresentationElements();

loadTCData();

</script>

<?php
include_once('defines/environnement_foot.php');

