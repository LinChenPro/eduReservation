<?php
$page_name = "teacher_calendar";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();
$user = dbFindByKey("User", $uid);

?>

<script src="/js/jquery-3.2.1.min.js"></script>
<script src="/js/ajax_query_manage.js"></script>

<div> teacher : <?=$user->user_name?></div>


<div id="week_head">
	<a id="week_pre" href="#"> last week </a>
	<span id="week_title"></span>
	<a id="week_next" href="#"> next week </a>
</div>
<div id="teacher_calendar_div" style="position:relative;">
	<div id="res_ope_div"></div>
	<div id="selection_div" style="z-index:1"></div>
	<div id="res_poe_detail_div" style="z-index:101"></div>
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
		<tr id="caltr_<?=$h?>"><td class="cal_h" id="cal_h_<?=$h?>"><?=hNbToCreneax($h)?></td></tr>
<?php
}
?>		
	</table>
</div>
<button id="sendSelect">select</button>
<button id="sendRemove">remove</button>
<pre id="showData" style="width:80%;margin:10px;padding:10px;border:1px solid black"></pre>


<script type="text/javascript">
/************  param js:  **************/
var demandeUrl = "/teacher_calendar_treate.php"

// datas
var uid=<?=$uid?>;
var week_nb = null;
var week_first_day = null;
var data_stamp = null;
var crtSchedules = null;
var crtReservations = null;
var crtOperations = null;

var selection_begin = null;
var selection_end = null;
var isMouseDown = false;

var crtDetalTD = null; // td whose elm detail info shows in a div float

// ajax query objects
var queryLoad = new AjaxQuery("queryLoad", showScheduleData, autoTCRefresh, demandeUrl);
var queryUpdate = new AjaxQuery("queryUpdate", showScheduleData, autoTCRefresh, demandeUrl);
var queryRefresh = new AjaxQuery("queryRefresh", showScheduleData, autoTCRefresh, demandeUrl, true);

// ajax request type constants
var TCA_TYPE_UPDATE = "<?=TCA_TYPE_UPDATE?>";
var TCA_TYPE_LOAD = "<?=TCA_TYPE_LOAD?>";
var TCA_TYPE_REFRESH = "<?=TCA_TYPE_REFRESH?>";

/************* ajax **************/


function sentTCDemand(d1, t1, d2, t2, to_statut){
	var demande = { 
		'action' : TCA_TYPE_UPDATE, 
		'uid' : uid, 
		'week_nb' : week_nb, 
		'from_day': d1,
		'from_h' : t1,
		'to_day' : d2,
		'to_h' : t2, 
		'to_statut': to_statut 
	};
	queryUpdate.sendAjaxQuery(demande);
}

function loadTCData(demande_week_nb){
	if(demande_week_nb!=null){
		week_nb = demande_week_nb;
	}
	send_week_nb = demande_week_nb==null?week_nb:demande_week_nb;
	var demande = { 
		'action' : TCA_TYPE_LOAD, 
		'uid' : uid, 
		'week_nb' : send_week_nb
	};

	queryLoad.sendAjaxQuery(demande);

}

function autoTCRefresh(){
	var demande = { 
		'action' : TCA_TYPE_REFRESH, 
		'uid' : uid, 
		'week_nb' : week_nb, 
		'timestamp' : data_stamp
	};
	queryRefresh.sendAjaxQuery(demande);
}



function showScheduleData(responseData){
	// do not show unchanged refresh result
	if(responseData["action"]==TCA_TYPE_REFRESH){
		if(!(responseData["week_nb"] == week_nb && responseData["timestamp"]>data_stamp)){
			return;
		}
	}

	week_nb = responseData["week_nb"];
	week_first_day = responseData["week_first_day"];
	data_stamp = responseData["timestamp"];

	if(responseData["action"]!=TCA_TYPE_REFRESH){
		initSelectionVars();
	}

	// show schedule
	crtSchedules = responseData["schedule_data"];
	for(var d=0; d<7; d++){
		var schedule = crtSchedules[d];
		$("#cal_d_"+d).html(schedule.day_str);

		for(var h=0; h<48; h++){
			$("#cal_"+d+"_"+h).attr("data-statut", schedule.day_status[h])
			.attr("data-day", schedule.day_nb)
			.attr("data-h", h)
			.html(schedule.day_nb + " - " + h)
			.attr("data-type", "");
		}
	}

	// show reservations
	crtReservations = responseData["reservation_data"];
	if(crtReservations != null){
		for(var i=0; i<crtReservations.length; i++){
			var reservation = crtReservations[i];
			var d = reservation.day_nb - week_first_day;
			var type = "res_"+(reservation.tid==uid?"t":"s");
			for(var h=reservation.begin_nb; h<=reservation.end_nb; h++){
				$("#cal_"+d+"_"+h).attr("data-type", type).attr("data-index", i);
			}
		}
	}

	// show operations
	crtOperations = responseData["operation_data"];
	if(crtOperations != null){
		for(var i=0; i<crtOperations.length; i++){
			var operation = crtOperations[i];
			var d = operation.day_nb - week_first_day;
			var type = "ope_";
			type += (operation.tid==uid?"t":"s");
			type += "_"+operation.statut;
			for(var h=operation.begin_nb; h<=operation.end_nb; h++){
				$("#cal_"+d+"_"+h).attr("data-type", type).attr("data-index", i);
			}
		}
	}

	$("#week_title").html(responseData.week_nb);
	$("#showData").html(JSON.stringify(responseData));

	if(!responseData.succes){
		alert(responseData.error);
	}
}






/**************     presentation, element reactions     **************/
$("body").mouseup(function(){
	isMouseDown = false;
	showSelection();
});

function initSelectionVars(){
	selection_begin = null;
	selection_end = null;
	isMouseDown = false;
	showSelection();
}

function select_begin(elm){
	event.stopPropagation();

	isMouseDown = true;
	var obj = $(elm);
	selection_begin = {day:obj.attr("data-day"), h:obj.attr("data-h")};
	selection_end = selection_begin;
	showSelection();
}

function select_continue(elm){
	event.stopPropagation();
	
	if(isMouseDown){
		var obj = $(elm);
		selection_end = {day:obj.attr("data-day"), h:obj.attr("data-h")};
		showSelection();
	}else{
	}
}

function select_end(elm){
	if(isMouseDown){
		event.stopPropagation();
		var obj = $(elm);
		selection_end = {day:obj.attr("data-day"), h:obj.attr("data-h")};
		isMouseDown = false;
		showSelection();
	}else{
		selection_begin = null;
		selection_end = null;
		showSelection();
	}
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

function showResOpeDetail(elm){
	if(!isMouseDown){
		event.stopPropagation();
		var detail_div = $("#res_poe_detail_div");

		var obj = $(elm);
		var index = obj.attr("data-index");
		if(index != null){
			crtDetalTD = elm;

			var htmlContent = "";
			if(obj.attr("data-type").startsWith("res")){
				htmlContent = getResDetailHtml(index);
			}else{
				htmlContent = getOpeDetailHtml(index);
			}

			var p_elm = obj.position();
			detail_div
			.css("left", p_elm.left+100)
			.css("top", p_elm.top)
			.html(htmlContent)
			.show();
		}
	}
}

function hideResOpeDetail(elm){
		event.stopPropagation();
		var detail_div = $("#res_poe_detail_div");

		var obj = $(elm);
		var index = obj.attr("data-index");
		if(crtDetalTD == elm){
			detail_div.hide().html("");
		}
}

function getResDetailHtml(index){
	if(crtReservations == null || crtReservations.length<=index){
		return "data not found";
	}

	var obj = crtReservations[index];
	var code = "Categ : " + obj.categ_name+"<br>";
	if(obj.sid==uid){
		code += "Teacher : "+obj.t_name;
	}else{
		code += "Student : "+obj.s_name;
	}
	return code;
}

function getOpeDetailHtml(index){
	if(crtOperations == null || crtOperations.length<=index){
		return "data not found";
	}

	var obj = crtOperations[index];
	var code = "Categ : " + obj.categ_name+"<br>";
	if(obj.sid==uid){
		code += "Teacher : "+obj.t_name+"<br>";
	}else{
		code += "Student : "+obj.s_name+"<br>";
	}
	code += "Statut : "+(obj.statut==0?"deleting...":"creating...")+"<br>";
	return code;
}

function initPresentationElements(){
	for(var h=0; h<48; h++){
		var tds = "";
		for(var d=0; d<7; d++){
			tds += '<td id="cal_'+d+'_'+h+'" class="cal_cell"></td>';
		}
		$("#caltr_"+h).append(tds);
	}

	$(".cal_cell")
	.mousedown(function(){select_begin(this)})
	.mouseup(function(){select_end(this)})
	.mousemove(function(){select_continue(this)})
	.mouseover(function(){showResOpeDetail(this)})
	.mouseout(function(){hideResOpeDetail(this)});
}




/**********  element reaction definitions *********/
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

