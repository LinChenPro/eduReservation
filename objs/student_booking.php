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
define("LESSON_STATUT_FIXED", 4);


class Lesson{
	public $res_id;
	public $ope_id;

	public $tid;
	public $t_name;
	public $sid;
	public $s_name;
	public $categ_id;
	public $categ_name;
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
	$lesson->day_nb = $operation->day_nb;
	$lesson->begin_h = $operation->begin_nb;
	$lesson->end_h = $operation->end_nb;
	$lesson->statut = $operation->statut==0 ? LESSON_STATUT_DELETING : LESSON_STATUT_CREATING;
	$lesson->res_statut = $operation->statut==0 ? RES_STATUT_DELETING : null;

	$lesson->formalisation($crt_tid, $crt_sid);
	return $lesson;
}

function reservationToLesson($reservation, $crt_tid, $crt_sid){
	$lesson = new Lesson();
	$lesson->res_id = $reservation->res_id;
	$lesson->ope_id = null;
	$lesson->tid = $reservation->tid;
	$lesson->t_name = $reservation->t_name;
	$lesson->sid = $reservation->sid;
	$lesson->s_name = $reservation->s_name;
	$lesson->categ_id = $reservation->categ_id;
	$lesson->categ_name = $reservation->categ_name;
	$lesson->day_nb = $reservation->day_nb;
	$lesson->begin_h = $reservation->begin_nb;
	$lesson->end_h = $reservation->end_nb;
	$lesson->statut = $reservation->statut==RES_STATUT_CREATED ? LESSON_STATUT_CREATED : LESSON_STATUT_FIXED;
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

