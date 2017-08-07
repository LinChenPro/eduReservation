<?php
define("SBK_TYPE_LOAD", "load");
define("SBK_TYPE_REFRESH", "refresh");
define("SBK_TYPE_TEACHERLIST", "teacher_list");
define("SBK_TYPE_CREATE", "create_ope");
define("SBK_TYPE_RESTORE", "restore");
define("SBK_TYPE_DELETERES", "delete_res");
define("SBK_TYPE_CANCELOPE", "cancel_ope");
define("SBK_TYPE_MOVE", "move_lesson");


class ActionResult{
	public $succes;
	public $infos;
	public $error;

	function __construct($succes=null, $infos=null, $error=null){
		$this->succes = $succes;
		$this->infos = $infos;
		$this->error = $error;
	}
}

class StudentCalendarResponse{
	public $tid;
	public $sid;
	public $categ_id;
	public $action;
	public $week_nb;
	public $timestamp;
	public $schedule_data;
	public $crtStudentLessons;
	public $crtTeacherLessonsTies;
	public $succes;
	public $error;
	public $infos;

	public static function newInstance($tid, $sid, $categ_id, $action, $week_nb, $timestamp, $succes){
		$response = new TeacherCalendarResponse();
		$response->tid = $tid;
		$response->sid = $sid;
		$response->categ_id = $categ_id;
		$response->action=$action;
		$response->week_nb = $week_nb;
		$response->timestamp = $timestamp;
		$response->succes = $succes;
		return $response;
	}
}

define("LESSON_STATUT_DELETING", 1);
define("LESSON_STATUT_CREATING", 2);
define("LESSON_STATUT_CREATED", 3);
define("LESSON_STATUT_MOVEDAWAY", 4);
define("LESSON_STATUT_MOVEDHERE", 5);
define("LESSON_STATUT_FIXED", 6);


class Lesson{
	public $res_id;
	public $ope_id;

	public $tid;
	public $t_name;
	public $sid;
	public $s_name;
	public $categ_id;
	public $categ_name;
	public $res_week;
	public $ope_week;
	public $day_nb;
	public $begin_h;
	public $end_h;
	public $statut;
	public $res_statut;

	public $is_tiers;
	public $editable;

	public function formalisation($crt_tid, $crt_sid){
		$this->is_tiers = ($this->sid != $crt_sid && $this->tid != $crt_sid);
		if($this->is_tiers){
			if($this->tid != $crt_tid){
				$this->tid = null;
				$this->t_name = null;
				$this->categ_id = null;
				$this->categ_name = null;
			}
			if($this->sid != $crt_tid){
				$this->sid = null;
				$this->s_name = null;
			}
		}

		$this->editable = ($this->sid == $crt_sid);
	}
}

function operationToLesson($operation, $crt_tid, $crt_sid){
	$lesson = new Lesson();
	$lesson->res_id = $operation->res_id;
	$lesson->ope_id = $operation->ope_id;
	$lesson->tid = $operation->tid;
	$lesson->t_name = $operation->t_name;
	$lesson->sid = $operation->sid;
	$lesson->s_name = $operation->s_name;
	$lesson->categ_id = $operation->categ_id;
	$lesson->categ_name = $operation->categ_name;
	$lesson->res_week = $operation->res_week_nb;
	$lesson->ope_week = $operation->week_nb;
	$lesson->day_nb = $operation->day_nb;
	$lesson->begin_h = $operation->begin_nb;
	$lesson->end_h = $operation->end_nb;
	$lesson->day_text = dayNbToStr($operation->day_nb);
	$lesson->time_text = hNbToCreneax($operation->begin_nb, $operation->end_nb);
	$lesson->statut = $operation->statut==OPE_STATUT_TODELETE ? LESSON_STATUT_DELETING : (
		$operation->statut==OPE_STATUT_TOMOVE ? LESSON_STATUT_MOVEDHERE : LESSON_STATUT_CREATING
	);
	$lesson->res_statut = $operation->statut==OPE_STATUT_TODELETE ? RES_STATUT_DELETING : (
		$operation->statut==OPE_STATUT_TOMOVE ? RES_STATUT_MOVING : null
	);
	
	$lesson->formalisation($crt_tid, $crt_sid);
	return $lesson;
}

function reservationToLesson($reservation, $crt_tid, $crt_sid){
	$lesson = new Lesson();
	$lesson->res_id = $reservation->res_id;
	$lesson->ope_id = $reservation->ope_id;;
	$lesson->tid = $reservation->tid;
	$lesson->t_name = $reservation->t_name;
	$lesson->sid = $reservation->sid;
	$lesson->s_name = $reservation->s_name;
	$lesson->categ_id = $reservation->categ_id;
	$lesson->categ_name = $reservation->categ_name;
	$lesson->res_week = $reservation->week_nb;
	$lesson->ope_week = $reservation->ope_week_nb;
	$lesson->day_nb = $reservation->day_nb;
	$lesson->begin_h = $reservation->begin_nb;
	$lesson->end_h = $reservation->end_nb;
	$lesson->day_text = dayNbToStr($reservation->day_nb);
	$lesson->time_text = hNbToCreneax($reservation->begin_nb, $reservation->end_nb);
	$lesson->statut = $reservation->statut==RES_STATUT_CREATED ? LESSON_STATUT_CREATED : (
		$reservation->statut==RES_STATUT_MOVING ? LESSON_STATUT_MOVEDAWAY : LESSON_STATUT_FIXED
	);

	$lesson->res_statut = $reservation->statut;

	$lesson->formalisation($crt_tid, $crt_sid);
	return $lesson;
}

function pushObjToLessonArr(&$lessonArr, $objArr, $convertFun, $tid, $sid){
	if(!empty($objArr)){
		foreach ($objArr as $obj) {
			array_push($lessonArr, call_user_func_array($convertFun, array($obj, $tid, $sid)));
		}
	}	
}

function loadStudentCalendar($tid, $sid, $categ_id, $week_nb){
	// prepare data:
	$reservation_data = getUserReservations($sid, $week_nb);
	$operation_data = getUserOperations($sid, $week_nb);
	$crtTeacherReservationsTiers = $tid==null ? null : getUserReservations($tid, $week_nb, "$sid not in(res_tid, res_sid)");
	$crtTeacherOperationsTiers = $tid==null ? null : getUserOperations($tid, $week_nb, "$sid not in(ope_tid, session_sid)");

	$crtStudentLessons = array();
	pushObjToLessonArr($crtStudentLessons, $reservation_data, 'reservationToLesson', $tid, $sid);
	pushObjToLessonArr($crtStudentLessons, $operation_data, 'operationToLesson', $tid, $sid);
	$crtTeacherLessonsTies = array();
	pushObjToLessonArr($crtTeacherLessonsTies, $crtTeacherReservationsTiers, 'reservationToLesson', $tid, $sid);
	pushObjToLessonArr($crtTeacherLessonsTies, $crtTeacherOperationsTiers, 'operationToLesson', $tid, $sid);




	$response = new StudentCalendarResponse();
	$response->tid = $tid;
	$response->sid = $sid;
	$response->categ_id = $categ_id;
	$response->action=TCA_TYPE_LOAD;
	$response->week_nb = $week_nb;
	$response->current_week_days = getWeekDays($week_nb);
	$response->week_first_day = getWeekFirstDayNb($week_nb);
	$response->timestamp = getUsersWeekStamp($week_nb, $sid, $tid);
	$response->schedule_data = $tid==null ? null : getTeacherCalendar($tid, $week_nb);
	$response->crtStudentLessons = empty($crtStudentLessons) ? null : $crtStudentLessons;
	$response->crtTeacherLessonsTies = empty($crtTeacherLessonsTies) ? null : $crtTeacherLessonsTies;
	$response->succes = true;
	$response->error = null;
	$response->infos = null;

	return $response;
}

function createOperation($categ_id, $tid, $sid, $day_nb, $from_h, $to_h, $tp_id){
/*	sleep(12);
*/
	$week_nb = dayNbToWeekNb($day_nb);

	$session_id = getExistSessionId($sid);
	if($session_id==null){
		return new ActionResult(false, null, "Your edit session is expired.");
	}

	// confirm teacher schedule
	if(!confirmTeacherSchedule($tid, $day_nb, $from_h, $to_h)){
		return new ActionResult(false, null, "This slot is not allowed by the teacher.");
	}

	// confirm conflict ope and res
	if(!confirmConflictResOpe($tid, $sid, $day_nb, $from_h, $to_h)){
		return new ActionResult(false, null, "This slot is already occupied.");
	}	

	addOperationToDB($session_id, "null", $tid, $sid, $categ_id, $tp_id, $week_nb, $day_nb, $from_h, $to_h, "null", OPE_STATUT_TOCREATE);

	return new ActionResult(true, "a new reservation will be created");
}

function moveLessonTo($ope_id, $res_id, $tid, $sid, $dest_week_nb, $dest_day_nb, $dest_begin_h, $dest_end_h){
	// confirm teacher schedule
	if(!confirmTeacherSchedule($tid, $dest_day_nb, $dest_begin_h, $dest_end_h)){
		return new ActionResult(false, null, "This slot is not allowed by the teacher.");
	}

	// confirm conflict ope and res
	if(!confirmConflictResOpe($tid, $sid, $dest_day_nb, $dest_begin_h, $dest_end_h)){
		return new ActionResult(false, null, "This slot is already occupied.");
	}	

	if(!empty($ope_id)){
		query("update student_operation set ope_week_nb=$dest_week_nb, ope_day_nb=$dest_day_nb, ope_begin_nb=$dest_begin_h, ope_end_nb=$dest_end_h where ope_id=$ope_id");
		return new ActionResult(true, "Lesson is rescheduled");
	}else{
		$reservation = getReservationById($res_id);
		$sid = $reservation->sid;
		$tid = $reservation->tid;

		TODO("find better way to treate session");
		$session_id = getExistSessionId($sid);
		if($session_id==null){
			return new ActionResult(false, null, "Your edit session is expired.");
		}

		query("delete from student_operation where ope_res_id=$res_id");
		addOperationToDB($session_id, $res_id, $tid, $sid, $reservation->categ_id, $reservation->tp_id, $dest_week_nb, $dest_day_nb, $dest_begin_h, $dest_end_h, $reservation->pur_id, OPE_STATUT_TOMOVE);
		query("update reservation set res_statut=".RES_STATUT_MOVING." where res_id=$res_id");

		return new ActionResult(true, "Reservation will be rescheduled.");
				
	}
}

function restoreReservation($ope_id, $res_id){
	$operation = getOperationById($ope_id);
	$reservation = getReservationById($res_id);

	$ope_week_nb = $operation->week_nb;
	$res_week_nb = $reservation->week_nb;
	$res_day_nb = $reservation->day_nb;
	$res_from_h = $reservation->begin_nb;
	$res_to_h = $reservation->end_nb;
	$sid = $reservation->sid;
	$tid = $reservation->tid;

	// confirm teacher schedule
	if(!confirmTeacherSchedule($tid, $res_day_nb, $res_from_h, $res_to_h)){
		return new ActionResult(false, null, "This slot is not allowed by the teacher.");
	}

	// confirm conflict ope and res
	if(!confirmConflictResOpe($tid, $sid, $res_day_nb, $res_from_h, $res_to_h, $res_id, $ope_id)){
		return new ActionResult(false, null, "This slot is already occupied.");
	}	

	query("delete from student_operation where ope_id=$ope_id");
	query("update reservation set res_statut=".RES_STATUT_CREATED." where res_id=$res_id");

	return new ActionResult(true, "reservation restored succesfully");
}

function deleteReservation($res_id){
	$reservation = getReservationById($res_id);
	$res_week_nb = $reservation->week_nb;
	$sid = $reservation->sid;
	$tid = $reservation->tid;

	TODO("find better way to treate session");
	$session_id = getExistSessionId($sid);
	if($session_id==null){
		return new ActionResult(false, null, "Your edit session is expired.");
	}

	query("delete from student_operation where ope_res_id=$res_id");
	addOperationToDB($session_id, $res_id, $tid, $sid, $reservation->categ_id, $reservation->tp_id, $res_week_nb, $reservation->day_nb, $reservation->begin_nb, $reservation->end_nb, $reservation->pur_id, OPE_STATUT_TODELETE);
	query("update reservation set res_statut=".RES_STATUT_DELETING." where res_id=$res_id");

	return new ActionResult(true, "reservation will be deleted.");
}

function cancelOperation($ope_id){
/*	$operation = getOperationById($ope_id);
	$ope_week_nb = $operation->week_nb;
	$sid = $operation->sid;
	$tid = $operation->tid;
*/
	query("delete from student_operation where ope_id=$ope_id");

	return new ActionResult(true, "creation of reservation is cancelled");
}

function addOperationToDB($session_id, $res_id, $tid, $sid, $categ_id, $tp_id, $week_nb, $day_nb, $begin_nb, $end_nb, $pur_id, $statut){

	$sql_insert_operation = "insert into student_operation(ope_session_id,ope_res_id,ope_tid,ope_categ_id,ope_tp_id,ope_week_nb,ope_day_nb,ope_begin_nb,ope_end_nb,ope_statut,ope_pur_id)"
		." values($session_id, $res_id, $tid, $categ_id, $tp_id, $week_nb, $day_nb, $begin_nb, $end_nb, $statut, $pur_id)";
	query($sql_insert_operation);

}

function confirmTeacherSchedule($tid, $day_nb, $from_h, $to_h){
	$week_nb = dayNbToWeekNb($day_nb);
	$weekCalendar = getTeacherCalendar($tid, $week_nb);
	$dayStatus = $weekCalendar[$day_nb%7]->day_status;

	for($h = $from_h; $h<=$to_h; $h++){
		if($dayStatus[$h]==0){
			return false;
		}
	}

	return true;
}

function confirmConflictResOpe($tid, $sid, $day_nb, $from_h, $to_h, $selected_res_id=null, $selected_ope_id=null){
	$sqlRes  = "select count(*) as cnt from reservation where res_day_nb = $day_nb and res_begin_nb<=$to_h and res_end_nb>=$from_h ";
	$sqlRes .= "and res_statut not in(".RES_STATUT_DELETING.",".RES_STATUT_DELETED.") ";
	$sqlRes .= "and (res_tid in($tid, $sid) or res_sid in($tid, $sid)) and not(res_sid=$sid and res_statut=".RES_STATUT_MOVING.") ";

	if($selected_res_id != null){
		$sqlRes .= "and res_id<>$selected_res_id ";
	}
	// echoln();
	// echoln($sqlRes);
	$countResConflict = fieldQuery($sqlRes, "cnt", 0);

	if($countResConflict>0){
		return false;
	}

	$sqlOpe  = "select count(*) as cnt from student_operation, student_session where ope_session_id=session_id ";
	$sqlOpe .= "and session_expire_time>=CURRENT_TIMESTAMP ";
	$sqlOpe .= "and session_statut not in(".SESSION_STATUT_ERROR.", ".SESSION_STATUT_EXPIRED.", ".SESSION_STATUT_CANCELLED.") ";
	$sqlOpe .= "and ope_day_nb = $day_nb and ope_begin_nb<=$to_h and ope_end_nb>=$from_h ";
	$sqlOpe .= "and (ope_tid in($tid, $sid) or session_sid in($tid, $sid)) ";
	$sqlOpe .= "and not(session_sid=$sid and ope_statut=".OPE_STATUT_TODELETE.") ";

	if($selected_ope_id != null){
		$sqlOpe .= "and ope_id<>$selected_ope_id ";
	}

	// echoln($sqlOpe);
	$countOpeConflict = fieldQuery($sqlOpe, "cnt", 0);

	if($countOpeConflict>0){
		return false;
	}
	// echoln();

	return true;
}

function abandonOperations($session_id, ...$ope_status){
	if(empty($ope_status)){
		return;
	}

	$sqlRes = "update reservation, student_operation set res_statut=".RES_STATUT_CREATED." where res_id=ope_res_id and ope_session_id=$session_id and ope_statut in(".concat("", ",", ...$ope_status).")";
	$sqlClean = "delete from student_operation where ope_session_id=$session_id and ope_statut in(".concat("", ", ", ...$ope_status).")";

	query($sqlRes);
	query($sqlClean);
}

function applyOperations($session_id, ...$ope_status){
	if(empty($ope_status)){
		return;
	}

	$sqlNewResCnt = "select count(1) as count from student_operation where ope_session_id=$session_id and ope_statut=".OPE_STATUT_TOCREATE;
	$count_new_res = fieldQuery($sqlNewResCnt, "count", 0);
	foreach ($ope_status as $ope_statut) {
		if($ope_statut == OPE_STATUT_TOCREATE && $count_new_res>0){ // TODO && create operations not treated
			$sqlRes  = "insert into reservation (res_tid,res_sid,res_categ_id,res_tp_id,res_week_nb,res_day_nb,res_begin_nb,res_end_nb,res_statut,res_create_time,res_modify_time,res_pur_id,res_session_id) ";			
			$sqlRes .= "select ope_tid,session_sid,ope_categ_id,ope_tp_id,ope_week_nb,ope_day_nb,ope_begin_nb,ope_end_nb,".RES_STATUT_CREATED.",CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,ope_pur_id,session_id ";
			$sqlRes .= "from student_operation, student_session where session_id=$session_id and ope_session_id=$session_id and ope_statut=$ope_statut"; 


			$sqlHist  = "insert into history(hist_res_id,hist_tid,hist_sid,hist_categ_id,hist_pur_id,hist_tp_id,hist_week_nb,hist_day_nb,hist_begin_nb,hist_end_nb,hist_action,hist_action_time,hist_session_create_time,hist_session_id) ";
			$sqlHist .= "select res_id,res_tid,res_sid,res_categ_id,res_pur_id,res_tp_id,res_week_nb,res_day_nb,res_begin_nb,res_end_nb,".HIST_ACTION_TYPE_CREATE.",res_modify_time,session_create_time,res_session_id ";
			$sqlHist .= "from reservation, student_session where session_id=$session_id and res_session_id=$session_id and res_statut=".RES_STATUT_CREATED;

			query($sqlRes);
			query($sqlHist);
		}else if($ope_statut == OPE_STATUT_TODELETE){
			$sqlRes = "update reservation set res_statut=".RES_STATUT_DELETED.", res_modify_time=CURRENT_TIMESTAMP, res_session_id=$session_id where res_id in(select ope_res_id from student_operation where ope_statut=".OPE_STATUT_TODELETE." and ope_session_id=$session_id)";

			$sqlHist  = "insert into history(hist_res_id,hist_tid,hist_sid,hist_categ_id,hist_pur_id,hist_tp_id,hist_week_nb,hist_day_nb,hist_begin_nb,hist_end_nb,hist_action,hist_action_time,hist_session_create_time,hist_session_id) ";
			$sqlHist .= "select res_id,res_tid,res_sid,res_categ_id,res_pur_id,res_tp_id,res_week_nb,res_day_nb,res_begin_nb,res_end_nb,".HIST_ACTION_TYPE_DELETE.",res_modify_time,session_create_time,res_session_id ";
			$sqlHist .= "from reservation, student_session, student_operation where session_id=$session_id and res_session_id=$session_id and res_statut=".RES_STATUT_DELETED." and ope_session_id=$session_id and ope_res_id=res_id and ope_statut=".OPE_STATUT_TODELETE;

			query($sqlRes);
			query($sqlHist);
		}else if($ope_statut == OPE_STATUT_TOMOVE){
			$sqlRes = "update reservation, student_operation set res_statut=".RES_STATUT_CREATED.", res_modify_time=CURRENT_TIMESTAMP, res_session_id=ope_session_id, res_week_nb=ope_week_nb, res_day_nb=ope_day_nb, res_begin_nb=ope_begin_nb, res_end_nb=ope_end_nb where res_id=ope_res_id and res_statut=".RES_STATUT_MOVING." and ope_statut=".OPE_STATUT_TOMOVE." and ope_session_id=$session_id";

			$sqlHist  = "insert into history(hist_res_id,hist_tid,hist_sid,hist_categ_id,hist_pur_id,hist_tp_id,hist_week_nb,hist_day_nb,hist_begin_nb,hist_end_nb,hist_action,hist_action_time,hist_session_create_time,hist_session_id) ";
			$sqlHist .= "select res_id,res_tid,res_sid,res_categ_id,res_pur_id,res_tp_id,res_week_nb,res_day_nb,res_begin_nb,res_end_nb,".HIST_ACTION_TYPE_MOVE.",res_modify_time,session_create_time,res_session_id ";
			$sqlHist .= "from reservation, student_session, student_operation where session_id=$session_id and res_session_id=$session_id and ope_session_id=$session_id and ope_res_id=res_id and ope_statut=".OPE_STATUT_TOMOVE;

			query($sqlRes);
			query($sqlHist);
		}
	}

	$sqlClean = "delete from student_operation where ope_session_id=$session_id and ope_statut in(".concat("", ", ", ...$ope_status).")";
	query($sqlClean);

}





