<?php
define("SBK_TYPE_LOAD", "load");
define("SBK_TYPE_UPDATE", "update");
define("SBK_TYPE_REFRESH", "refresh");
define("SBK_TYPE_TEACHERLIST", "teacher_list");

class StudentCalendarResponse{
	public $tid;
	public $sid;
	public $categ_id;
	public $action;
	public $week_nb;
	public $timestamp;
	public $schedule_data;
	public $reservation_data;
	public $operation_data;
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

function loadStudentCalendar($tid, $sid, $categ_id, $week_nb){
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
	$response->reservation_data = getUserReservations($sid, $week_nb);
	$response->operation_data = getUserOperations($sid, $week_nb);

	$response->crtTeacherReservationsTiers = $tid==null ? null : getUserReservations($tid, $week_nb);
	$response->crtTeacherOperationsTiers = $tid==null ? null : getUserOperations($tid, $week_nb);
	$response->succes = true;
	$response->error = null;
	$response->infos = null;

	return $response;
}

