<?php
$page_name = "student_booking";

require_once('defines/environnement_head.php');

$uid = getCurrentUid();
$user = dbFindByKey("User", $uid);

$currentSession = getOrCreateSession($uid);
if($currentSession->session_statut!=SESSION_STATUT_INOPERATION){
	TODO("booking : forbidden normal operations to session in special statut");
}
?>

<script src="/js/jquery-3.2.1.min.js"></script>
<script src="/js/ajax_query_manage.js"></script>

<div> 
	student : <?=$user->user_name?>
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
	select category : 
	<select name="categ_selector" id="categ_selector">
		<option value="">Please select a category</option>
<?php
// options of categories
$categs = Categ::getCategs("categ_id in(select distinct(tc_categ_id) from teacher_categ where (tc_expire_time is null or tc_expire_time>'".$currentSession->session_create_time."'))");
if(!empty($categs)){
	foreach ($categs as $categ) {
?>		
		<option value="<?=$categ->categ_id?>"><?=$categ->categ_name?></option>
<?php
	}
}
?>		
	</select>
	&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
	select teacher : 
	<select name="teacher_selector" id="teacher_selector"></select>
</div>


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
var demandeUrl = "/student_booking_treate.php?uid=<?=$uid?>"

// datas

var uid=<?=$uid?>; // student id
var categ_id = null // categ id;
var tid=null; // teacher id
var week_nb = null;
var week_first_day = null;
var current_week_days = null;
var data_stamp = null; // max value of teacher's data_stamp and student's data stamp
var crtSchedules = null; // schedule of teacher, removed the times of res and opes in whiche tid as role student
var crtReservations = null; // student's all reservations
var crtOperations = null; // student's all operations

var crtTeacherReservationsTiers = null; // contains les info annonyme
var crtTeacherOperationsTiers = null; // contains les info annonyme

// mouse reaction needed
var selection_begin = null;
var selection_end = null;
var isMouseDown = false;

var crtDetalTD = null; // td whose elm detail info shows in a div float
var crtTeacherList = null;


// list of request
/*
var queryLoad = null;
var queryUpdate = null; 		// *- replaced by the new querys 
var queryNewSelection = null;	// *+
var queryCancelRes = null;		// *+
var queryGiveupOpe = null;		// *+
var queryMove = null; 			// *+ ?need?

var queryRefresh = null;
var queryGetTeacherLists = null;	// *+
*/

// ajax query objects
var queryLoad = new AjaxQuery("queryLoad", showScheduleData, autoSBKRefresh, demandeUrl);
var queryUpdate = new AjaxQuery("queryUpdate", showScheduleData, autoSBKRefresh, demandeUrl);
var queryRefresh = new AjaxQuery("queryRefresh", showScheduleData, autoSBKRefresh, demandeUrl, true);
var queryTeacherList = new AjaxQuery("queryTeacherList", feedTeacherSelector, autoSBKRefresh, demandeUrl);

// ajax request type constants
var SBK_TYPE_UPDATE = "<?=SBK_TYPE_UPDATE?>";
var SBK_TYPE_LOAD = "<?=SBK_TYPE_LOAD?>";
var SBK_TYPE_REFRESH = "<?=SBK_TYPE_REFRESH?>";
var SBK_TYPE_TEACHERLIST = "<?=SBK_TYPE_TEACHERLIST?>";

/************* ajax **************/
function loadTeachers(){
	var demande = { 
		'action' : SBK_TYPE_TEACHERLIST, 
		'sid' : uid,
		'categ_id': categ_id 
	};
	queryTeacherList.sendAjaxQuery(demande);
}


function sentSBKDemand(d1, t1, d2, t2, to_statut){
	var demande = { 
		'action' : SBK_TYPE_UPDATE, 
		'categ_id' : categ_id, 
		'tid' : tid, 
		'sid' : uid,
		'week_nb' : week_nb, 
		'from_day': d1,
		'from_h' : t1,
		'to_day' : d2,
		'to_h' : t2, 
		'to_statut': to_statut 
	};
	queryUpdate.sendAjaxQuery(demande);
}

function loadSBKData(demande_week_nb){
	if(demande_week_nb!=null){
		week_nb = demande_week_nb;
	}
	send_week_nb = demande_week_nb==null?week_nb:demande_week_nb;
	var demande = { 
		'action' : SBK_TYPE_LOAD, 
		'categ_id' : categ_id, 
		'tid' : tid, 
		'sid' : uid,
		'week_nb' : send_week_nb
	};
	queryLoad.sendAjaxQuery(demande);

}

function autoSBKRefresh(){
	var demande = { 
		'action' : SBK_TYPE_REFRESH, 
		'categ_id' : categ_id, 
		'tid' : tid, 
		'sid' : uid,
		'week_nb' : week_nb, 
		'timestamp' : data_stamp
	};
	queryRefresh.sendAjaxQuery(demande);
}

function feedTeacherSelector(responseData){
	$("#showData").html(JSON.stringify(responseData));
	crtTeacherList = responseData;
	showDatas();
}

function showScheduleData(responseData){

	// for test data
	$("#showData").html(JSON.stringify(responseData));

	// do not show unchanged refresh result
	if(responseData["action"]==SBK_TYPE_REFRESH){
		if(!(responseData["week_nb"] == week_nb && responseData["timestamp"]>data_stamp)){
			return;
		}
	}

	if(responseData["action"]!=SBK_TYPE_REFRESH){
		initSelectionVars();
	}

	week_nb = responseData["week_nb"];
	week_first_day = responseData["week_first_day"];
	data_stamp = responseData["timestamp"];
	current_week_days = responseData["current_week_days"]

	crtSchedules = responseData["schedule_data"];
	crtTeacherReservationsTiers = responseData["crtTeacherReservationsTiers"];
	crtTeacherOperationsTiers = responseData["crtTeacherOperationsTiers"];

	crtReservations = responseData["reservation_data"];
	crtOperations = responseData["operation_data"];

	
	showDatas();

}

function showDatas(){
	// show current_week_days
	if(current_week_days != null){
		for(var d=0; d<7; d++){
			var text = current_week_days[d].text;
			$("#cal_d_"+d).html(text);
		}
	}

	// show schedule
	if(crtSchedules!=null){
		for(var d=0; d<7; d++){
			var schedule = crtSchedules[d];
			for(var h=0; h<48; h++){
				$("#cal_"+d+"_"+h).attr("data-statut", schedule.day_status[h])
				.attr("data-day", schedule.day_nb)
				.attr("data-h", h)
				.html(schedule.day_nb + " - " + h)
				.attr("data-type", "");
			}
		}
	}else{
		for(var d=0; d<7; d++){
			for(var h=0; h<48; h++){
				$("#cal_"+d+"_"+h).attr("data-statut", 0)
				.attr("data-day", current_week_days[d].day_nb)
				.attr("data-h", h)
				.html(current_week_days[d].day_nb + " - " + h)
				.attr("data-type", "");
			}
		}
	}

	// show reservations
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

	// show tiers reservations
	if(crtTeacherReservationsTiers != null){
		for(var i=0; i<crtTeacherReservationsTiers.length; i++){
			var reservation = crtTeacherReservationsTiers[i];
			var d = reservation.day_nb - week_first_day;
			var type = "res_"+(reservation.tid==uid?"t":"s");
			for(var h=reservation.begin_nb; h<=reservation.end_nb; h++){
				$("#cal_"+d+"_"+h).attr("data-type", type).attr("data-index", i);
			}
		}
	}

	// show tiers operations
	if(crtTeacherOperationsTiers != null){
		for(var i=0; i<crtTeacherOperationsTiers.length; i++){
			var operation = crtTeacherOperationsTiers[i];
			var d = operation.day_nb - week_first_day;
			var type = "ope_";
			type += (operation.tid==uid?"t":"s");
			type += "_"+operation.statut;
			for(var h=operation.begin_nb; h<=operation.end_nb; h++){
				$("#cal_"+d+"_"+h).attr("data-type", type).attr("data-index", i);
			}
		}
	}

	$("#week_title").html(week_nb);
	showTeacherOptions();

}

function showTeacherOptions(){
	var teacherSelector = $("#teacher_selector");
	if(crtTeacherList == null){
		teacherSelector.html('<option value="">please select first the category</option>');
	}else if(crtTeacherList.length==0){
		teacherSelector.html('<option value="">no available teacher for this category</option>');
	}else{
		var optionCode = '<option value="">please select a teacher</option>';
		for(var i=0; i<crtTeacherList.length; i++){
			var teacher = crtTeacherList[i];
			var selected = teacher.tid==tid ? " selected" : "";
			optionCode += '<option value="'+teacher.tid+'"'+selected+'>'+teacher.t_name+' prise:'+(teacher.teacher_prise/100)+'</option>\n';
		}
		teacherSelector.html(optionCode);
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

/**********  data manipunations *********/

function clearCrtTeacherData(){
	tid = null;
	crtSchedules = null;
	crtTeacherReservationsTiers = null;
	crtTeacherOperationsTiers = null;	
}

/**********  element reaction definitions *********/


// treate selection of category
$("#categ_selector").change(function(){
	categ_id = this.value == "" ? null : this.value;
	crtTeacherList = null;
	clearCrtTeacherData();
	if(categ_id==null){
		initSelectionVars();
		clearCrtTeacherData();
		showDatas();
	}else{
		initSelectionVars();
		clearCrtTeacherData();
		loadTeachers();
	}
});

$("#teacher_selector").change(function(){
	tid = this.value == "" ? null : this.value;
	if(tid != null){
		loadSBKData();
	}else{
		clearCrtTeacherData();
		initSelectionVars();
		showDatas();

	}
});

$("#sendSelect").click(function(){
	if(selection_begin!=null && selection_end!=null){
		var d_min = Math.min(selection_begin.day, selection_end.day);
		var d_max = Math.max(selection_begin.day, selection_end.day);
		var h_min = Math.min(selection_begin.h, selection_end.h);
		var h_max = Math.max(selection_begin.h, selection_end.h);

		sentSBKDemand(d_min, h_min, d_max, h_max, 1);
	}
});

$("#sendRemove").click(function(){
	if(selection_begin!=null && selection_end!=null){
		var d_min = Math.min(selection_begin.day, selection_end.day);
		var d_max = Math.max(selection_begin.day, selection_end.day);
		var h_min = Math.min(selection_begin.h, selection_end.h);
		var h_max = Math.max(selection_begin.h, selection_end.h);

		sentSBKDemand(d_min, h_min, d_max, h_max, 0);
	}
});

$("#week_pre").click(function(){
	loadSBKData(week_nb-1);
});

$("#week_next").click(function(){
	loadSBKData(week_nb-(-1));
});

initPresentationElements();

loadSBKData();

</script>

<?php
include_once('defines/environnement_foot.php');

