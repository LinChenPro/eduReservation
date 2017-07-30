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
	<div id="lesson_div"></div>
	<span id="lesson_detail_span">
		lesson : <span id="focuslesson_categ"></span>
		<br>
		day : <span id="focuslesson_day"></span>
		<br>
		time : <span id="focuslesson_time"></span>
		<br>
		teacher : <span id="focuslesson_teacher"></span>
		<br>
		student : <span id="focuslesson_student"></span>
		<br>
		statut : <span id="focuslesson_statut"></span>
		<br>
		<a id="focuslesson_a_delete" style="display:none" href="javascript:focusLessonDelete()">delete</a> 
		<a id="focuslesson_a_restore" style="display:none" href="javascript:focusLessonRestore()">restore</a> 
		<a id="focuslesson_a_cancel" style="display:none" href="javascript:focusLessonCancel()">cancel</a> 
		<a id="focuslesson_a_move" style="display:none" href="javascript:focusLessonMove()">move</a> 
		<a id="focuslesson_a_goto" style="display:none" href="javascript:focusLessonGoto()">goto</a> 

	</span>
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
<br>
<textarea id="showData" style="width:80%;margin:10px;padding:10px;border:1px solid black;height:300px" rows="50"></textarea>


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
var crtStudentLessons = null; // student's all lessons, with role student or teacher
var crtTeacherLessonsTies = null; // teacher's all lessons with others, with role student or teacher

var querySucces = null;
var queryError = null;
var queryInfos = null;


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
var queryRefresh = new AjaxQuery("queryRefresh", showScheduleData, autoSBKRefresh, demandeUrl, true);
var queryTeacherList = new AjaxQuery("queryTeacherList", feedTeacherSelector, autoSBKRefresh, demandeUrl);
var queryGotoCategTeacher = new AjaxQuery("queryGotoCategTeacher", gotoSelectedTeacher, autoSBKRefresh, demandeUrl);
var queryCreate = new AjaxQuery("queryCreate", showScheduleData, autoSBKRefresh, demandeUrl);
var queryRestore = new AjaxQuery("queryRestore", showScheduleData, autoSBKRefresh, demandeUrl);
var queryDeleteRes = new AjaxQuery("queryDeleteRes", showScheduleData, autoSBKRefresh, demandeUrl);
var queryCancelOpe = new AjaxQuery("queryCancelOpe", showScheduleData, autoSBKRefresh, demandeUrl);


// ajax request type constants
var SBK_TYPE_LOAD = "<?=SBK_TYPE_LOAD?>";
var SBK_TYPE_REFRESH = "<?=SBK_TYPE_REFRESH?>";
var SBK_TYPE_TEACHERLIST = "<?=SBK_TYPE_TEACHERLIST?>";
var SBK_TYPE_CREATE = "<?=SBK_TYPE_CREATE?>";
var SBK_TYPE_RESTORE = "<?=SBK_TYPE_RESTORE?>";
var SBK_TYPE_DELETERES = "<?=SBK_TYPE_DELETERES?>";
var SBK_TYPE_CANCELOPE = "<?=SBK_TYPE_CANCELOPE?>";

var LESSON_STATUT_DELETING = "<?=LESSON_STATUT_DELETING?>"
var LESSON_STATUT_CREATING = "<?=LESSON_STATUT_CREATING?>"
var LESSON_STATUT_CREATED = "<?=LESSON_STATUT_CREATED?>"
var LESSON_STATUT_FIXED = "<?=LESSON_STATUT_FIXED?>"

var RES_STATUT_CREATED = "<?=RES_STATUT_CREATED?>"
var RES_STATUT_DELETING = "<?=RES_STATUT_DELETING?>"
var RES_STATUT_DELETED = "<?=RES_STATUT_DELETED?>"
var RES_STATUT_FIXED = "<?=RES_STATUT_FIXED?>"
var RES_STATUT_ACTIVE = "<?=RES_STATUT_ACTIVE?>"
var RES_STATUT_FINISHED = "<?=RES_STATUT_FINISHED?>"


/************* ajax **************/
function loadTeachers(){
	var demande = { 
		'action' : SBK_TYPE_TEACHERLIST, 
		'sid' : uid,
		'categ_id': categ_id 
	};
	queryTeacherList.sendAjaxQuery(demande);
}

function gotoCategTeacher(){
	var demande = { 
		'action' : SBK_TYPE_TEACHERLIST, 
		'sid' : uid,
		'categ_id': categ_id 
	};
	queryGotoCategTeacher.sendAjaxQuery(demande);
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

function createDemand(day_nb, from_h, to_h){
	var demande = { 
		'action' : SBK_TYPE_CREATE, 
		'categ_id' : categ_id, 
		'tid' : tid, 
		'sid' : uid,
		'day_nb' : day_nb,
		'from_h' : from_h,
		'to_h' : to_h, 
		'tp_id' : getTeacherPrise(tid),
		'week_nb' : week_nb
	};
	queryCreate.sendAjaxQuery(demande);
}

function restoreDemand(ope_id, res_id){
	var demande = { 
		'action' : SBK_TYPE_RESTORE, 
		'ope_id' : ope_id,
		'res_id' : res_id,
		'categ_id' : categ_id, 
		'tid' : tid, 
		'sid' : uid,
		'week_nb' : week_nb
	};
	queryRestore.sendAjaxQuery(demande);
}

function deleteResDemand(res_id){
	var demande = { 
		'action' : SBK_TYPE_DELETERES, 
		'res_id' : res_id,
		'categ_id' : categ_id, 
		'tid' : tid, 
		'sid' : uid,
		'week_nb' : week_nb
	};
	queryDeleteRes.sendAjaxQuery(demande);
}

function cancelOpeDemand(ope_id){
	var demande = { 
		'action' : SBK_TYPE_CANCELOPE, 
		'ope_id' : ope_id,
		'categ_id' : categ_id, 
		'tid' : tid, 
		'sid' : uid,
		'week_nb' : week_nb
	};
	queryCancelOpe.sendAjaxQuery(demande);
}

function displayVars(responseData){
	var t = "crtStudentLessons = " + JSON.stringify(crtStudentLessons);
	t +=  "\n\crtTeacherLessonsTies = " + JSON.stringify(crtTeacherLessonsTies);
//	t +=  "\n\nresponseData = " + JSON.stringify(responseData);
	$("#showData").html(t);
}

function feedTeacherSelector(responseData){
	crtTeacherList = responseData;
	showDatas();
}

function gotoSelectedTeacher(responseData){
	crtTeacherList = responseData;
	showDatas();
	$("#teacher_selector").val(tid);

	setTimeout(function(){
		$("#teacher_selector").change();
	}, 10);
}


function getTeacherPrise(tid){
	for(var i=0; i<crtTeacherList.length; i++){
		var ti = crtTeacherList[i];
		if(ti.tid == tid){
			return ti.teacher_prise_id;
		}
	}
	return -1;
}

function showScheduleData(responseData){
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
	crtStudentLessons = responseData["crtStudentLessons"];
	crtTeacherLessonsTies = responseData["crtTeacherLessonsTies"];

	querySucces = responseData["succes"];
	queryError = responseData["error"];
	queryInfos = responseData["infos"];

	showDatas();
}

function setCellAttrbuts(day_nb, h, statut, type){
	var d = day_nb-week_first_day;
	$("#cal_"+d+"_"+h).attr("data-statut", statut)
	.attr("data-day", day_nb)
	.attr("data-h", h)
	.attr("data-type", type)
	.html(day_nb + " - " + h)
	;
}


//------------- show lesson -----------

var currentFocusLesson = null;
var lessonInOperation = false;
var statut_texts = [];
var res_statut_texts = [];
statut_texts[LESSON_STATUT_CREATED] = "created";
statut_texts[LESSON_STATUT_CREATING] = "creating";
statut_texts[LESSON_STATUT_DELETING] = "deleting";
statut_texts[LESSON_STATUT_FIXED] = "fixed";
res_statut_texts[RES_STATUT_CREATED] = "created";
res_statut_texts[RES_STATUT_DELETING] = "deleting";
res_statut_texts[RES_STATUT_DELETED] = "deleted";
res_statut_texts[RES_STATUT_FIXED] = "fixed";
res_statut_texts[RES_STATUT_ACTIVE] = "active";
res_statut_texts[RES_STATUT_FINISHED] = "finished";


function showLessonElm(lesson){
	var spanObj = $('<span class="span_lesson"></span>').appendTo($("#lesson_div"));
	initView(spanObj, lesson);
	setLessonMouseOver(spanObj, lesson);
	setLessonMouseOut(spanObj, lesson);
}

function getLessonPosition(day_nb, begin_h, end_h){
	var p = {};
	var obj_day = $("#cal_d_"+(day_nb-week_first_day));
	var obj_eh = $("#cal_h_"+end_h);
	p.top = $("#cal_h_"+begin_h).position().top;
	p.left = obj_day.position().left;
	p.width = obj_day.outerWidth();
	p.height = obj_eh.position().top-p.top+obj_eh.outerHeight();
	return p;
}

function initView(spanObj, lesson){
	var pos = getLessonPosition(lesson.day_nb, lesson.begin_h, lesson.end_h);
	spanObj.css("left", pos.left).css("top", pos.top).css("width", pos.width).css("height", pos.height);
	spanObj.addClass(lesson.is_tiers?"tiers":"mine");
	spanObj.addClass((lesson.tid==uid || lesson.sid==tid)? "role-invers" : null);
	spanObj.addClass(lesson.editable? "editable" : "non_editable");
	spanObj.addClass(getStatutClass(lesson));

	var text = lesson.t_name == null? "T" : lesson.t_name;
	text += " " + (lesson.categ_name == null? "lesson" : lesson.categ_name);
	text += " " + (lesson.s_name == null? "S" : lesson.s_name);
	spanObj.html(text);

}

function setLessonMouseOver(obj, lesson){
	obj.mouseover(function(evt){		
		currentFocusLesson = lesson;
		lessonInOperation = true;
		showDetailSpan();
	});
}

function setLessonMouseOut(obj, lesson){
	obj.mouseout(function(evt){
		lessonInOperation = false;
		setTimeout(function(){
			if(!lessonInOperation){
				currentFocusLesson = null;
				showDetailSpan();
			}
		}, 1000);
	});
}

function showDetailSpan(){
	if(currentFocusLesson == null){
		$("#focuslesson_categ").html("");
		$("#focuslesson_day").html("");
		$("#focuslesson_time").html("");
		$("#focuslesson_teacher").html("");
		$("#focuslesson_student").html("");
		$("#focuslesson_statut").html("");
		$("#focuslesson_a_delete").css("display", "none");
		$("#focuslesson_a_restore").css("display", "none");
		$("#focuslesson_a_cancel").css("display", "none");
		$("#focuslesson_a_move").css("display", "none");
		$("#focuslesson_a_goto").css("display", "none");
	}else{
		$("#focuslesson_categ").html(currentFocusLesson.categ_name);
		$("#focuslesson_day").html(currentFocusLesson.day_text);
		$("#focuslesson_time").html(currentFocusLesson.time_text);
		$("#focuslesson_teacher").html(currentFocusLesson.t_name);
		$("#focuslesson_student").html(currentFocusLesson.s_name);
		if(!currentFocusLesson.is_tiers){
			$("#focuslesson_statut").html(getStatutText(currentFocusLesson));
		}

		$('a[id*="focuslesson_a_"]').css("display", "none");
		if(currentFocusLesson.editable){
			var statut = currentFocusLesson.statut;	
			if(statut==LESSON_STATUT_CREATED){
				$("#focuslesson_a_delete").css("display", "inline");
			}
			if(statut==LESSON_STATUT_DELETING){
				$("#focuslesson_a_restore").css("display", "inline");
			}
			if(statut==LESSON_STATUT_CREATING){
				$("#focuslesson_a_cancel").css("display", "inline");
			}
			if((statut==LESSON_STATUT_CREATED || statut==LESSON_STATUT_CREATING)
				&& currentFocusLesson.tid == tid){
				$("#focuslesson_a_move").css("display", "inline");
			}if(currentFocusLesson.tid!=tid || currentFocusLesson.categ_id!=categ_id){
				$("#focuslesson_a_goto").css("display", "inline");
			}
		}
	}
}

function getStatutText(lesson){
	if(lesson.statut != LESSON_STATUT_FIXED){
		return statut_texts[lesson.statut];
	}else{
		return res_statut_texts[lesson.res_statut];
	}
}

function getStatutClass(lesson){
	return statut_texts[lesson.statut];
}

function focusLessonDelete(){
	deleteResDemand(currentFocusLesson.res_id);
}

function focusLessonRestore(){
	restoreDemand(currentFocusLesson.ope_id, currentFocusLesson.res_id);
}

function focusLessonCancel(){
	cancelOpeDemand(currentFocusLesson.ope_id);
}

function focusLessonMove(){

}

function focusLessonGoto(){
	initSelectionVars();
	clearCrtTeacherData();
	categ_id = currentFocusLesson.categ_id;
	tid = currentFocusLesson.tid;
	$("#categ_selector").val(categ_id);
	gotoCategTeacher();
}


$("#lesson_detail_span").mouseleave(function(){
	currentFocusLesson = null;
	lessonInOperation = false;
	showDetailSpan();
}).mouseover(function(){
	lessonInOperation = true;
});


//-------------------------------------

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
				setCellAttrbuts(schedule.day_nb, h, schedule.day_status[h], "");
			}
		}
	}else{
		for(var d=0; d<7; d++){
			for(var h=0; h<48; h++){
				setCellAttrbuts(current_week_days[d].day_nb, h, 0, "");
			}
		}
	}

	// clear old elements
	$("#lesson_div").html("");

	// show student lessons
	if(crtStudentLessons != null){
		for(var i=0; i<crtStudentLessons.length; i++){
			showLessonElm(crtStudentLessons[i]);
		}
	}

	// show teacher tier lessons
	if(crtTeacherLessonsTies != null){
		for(var i=0; i<crtTeacherLessonsTies.length; i++){
			showLessonElm(crtTeacherLessonsTies[i]);
		}
	}


	$("#week_title").html(week_nb);
	showTeacherOptions();

	if(querySucces!=null){
		if(querySucces==true){
			showInfos();
		}else if(querySucces==false){
			showError();
		}
	}

	// for test
	displayVars();
}

function showInfos(){
	if(queryInfos != null){
		alert(queryInfos);	
	}
	querySucces = null;
}

function showError(){
	if(queryError != null){
		alert(queryError);	
	}
	querySucces = null;
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
	if(event.target.id == "sendSelect"){ // TODO
		return;
	}

	if(isMouseDown){
		isMouseDown = false;
		showSelection();
	}else{
		initSelectionVars();
	}
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
		event.stopPropagation();
	if(isMouseDown){
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
	crtTeacherLessonsTies = null;
}

/**********  element reaction definitions *********/


// treate selection of category
$("#categ_selector").change(function(){
	categ_id = this.value == "" ? null : this.value;
	crtTeacherList = null;
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
	event.stopPropagation();
	event.preventDefault();

	if(selection_begin!=null && selection_end!=null){
		var d_min = Math.min(selection_begin.day, selection_end.day);
		var d_max = Math.max(selection_begin.day, selection_end.day);
		var h_min = Math.min(selection_begin.h, selection_end.h);
		var h_max = Math.max(selection_begin.h, selection_end.h);

		createDemand(d_min, h_min, h_max);
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
	event.preventDefault();
});

$("#week_next").click(function(){
	loadSBKData(week_nb-(-1));
	event.preventDefault();
});

initPresentationElements();

loadSBKData();

</script>

<?php
include_once('defines/environnement_foot.php');

