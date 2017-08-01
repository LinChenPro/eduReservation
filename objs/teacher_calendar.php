<?php
define("TCA_TYPE_LOAD", "load");
define("TCA_TYPE_UPDATE", "update");
define("TCA_TYPE_REFRESH", "refresh");

class TeacherCalendarResponse{
	public $tid;
	public $action;
	public $week_nb;
	public $timestamp;
	public $schedule_data;
	public $reservation_data;
	public $operation_data;
	public $succes;
	public $error;
	public $infos;
 
	public static function newInstance($tid, $action, $week_nb, $timestamp, $succes){
		$response = new TeacherCalendarResponse();
		$response->tid = $tid;
		$response->action=$action;
		$response->week_nb = $week_nb;
		$response->timestamp = $timestamp;
		$response->succes = $succes;
		return $response;
	}
}

function loadTeacherCalendar($tid, $week_nb){
	$sql = "select * from teacher_schedule left join weekly_action_stamp on (ts_tid=stamp_uid and ts_week_nb=stamp_week_nb) where ts_tid=$tid and ts_week_nb=$week_nb";

	$response = new TeacherCalendarResponse();
	$response->tid = $tid;
	$response->action=TCA_TYPE_LOAD;
	$response->week_nb = $week_nb;
	$response->week_first_day = getWeekFirstDayNb($week_nb);
	$response->timestamp = getUserWeekStamp($tid, $week_nb);
	$response->schedule_data = getTeacherCalendar($tid, $week_nb);
	$response->reservation_data = getUserReservations($tid, $week_nb);
	$response->operation_data = getUserOperations($tid, $week_nb);
	$response->succes = true;
	$response->error = null;
	$response->infos = null;

	return $response;
}

function updateTeacherCalendar(
	$uid, 
	$week_nb, 
	$from_day,
	$from_h,
	$to_day,
	$to_h,
	$to_statut
){
	// get current data:
	$currentCalendar = loadTeacherCalendar($uid, $week_nb);

	// set value to_statut
	$week_first_day = $currentCalendar->week_first_day;
	$from_day_i = $from_day - $week_first_day;
	$to_day_i = $to_day - $week_first_day;
	for($d = $from_day_i; $d<=$to_day_i; $d++){
		for($h = $from_h; $h<=$to_h; $h++){
			$currentCalendar->schedule_data[$d]->day_status[$h] = $to_statut;
		}
	}

	// replace by reservations and operations
	if($to_statut==TCA_STATUT_OCCUPIED){
		$reservations = $currentCalendar->reservation_data;
		if(!empty($reservations))
		foreach ($reservations as $reservation) {
			if($reservation->tid==$uid){
				$d = $reservation->day_nb-$week_first_day;
				for($h = $reservation->begin_nb; $h<=$reservation->end_nb; $h++){
					$currentCalendar->schedule_data[$d]->day_status[$h] = TCA_STATUT_AVAILABLE;
				}
			}
		}

		$operations = $currentCalendar->operation_data;
		if(!empty($operations))
		foreach ($operations as $operation) {
			if($operation->tid==$uid){
				$d = $operation->day_nb-$week_first_day;
				for($h = $operation->begin_nb; $h<=$operation->end_nb; $h++){
					$currentCalendar->schedule_data[$d]->day_status[$h] = TCA_STATUT_AVAILABLE;
				}
			}
		}
	}

	updateCalendarsToDb($uid, $week_nb, $currentCalendar->schedule_data);
	$currentCalendar->timestamp = getUserWeekStamp($uid, $week_nb);

	return $currentCalendar;
}

/* ------------------------------------------------------ */
define("RES_STATUT_CREATED", 1);
define("RES_STATUT_DELETING", 2);
define("RES_STATUT_DELETED", 3);
define("RES_STATUT_MOVING", 4);
define("RES_STATUT_FIXED", 5);
define("RES_STATUT_ACTIVE", 6);
define("RES_STATUT_FINISHED", 7);

class Reservation{
	public $res_id;
	public $ope_id;
	public $tid;
	public $t_name;
	public $sid;
	public $s_name;
	public $categ_id;
	public $categ_name;
	public $week_nb;
	public $ope_week_nb;
	public $day_nb;
	public $begin_nb;
	public $end_nb;
	public $statut;
	public $tp_id;
	public $pur_id;


}

function dbLineToReservation($row){
	$reservation = new Reservation();
	$reservation->res_id = $row["res_id"];
	$reservation->ope_id = $row["ope_id"];
	$reservation->tid = $row["res_tid"];
	$reservation->t_name = $row["t_name"];
	$reservation->sid = $row["res_sid"];
	$reservation->s_name = $row["s_name"];
	$reservation->categ_id = $row["res_categ_id"];
	$reservation->categ_name = $row["categ_name"];
	$reservation->week_nb = $row["res_week_nb"];
	$reservation->ope_week_nb = $row["ope_week_nb"];
	$reservation->day_nb = $row["res_day_nb"];
	$reservation->begin_nb = $row["res_begin_nb"];
	$reservation->end_nb = $row["res_end_nb"];
	$reservation->tp_id = $row["res_tp_id"];
	$reservation->pur_id = $row["res_pur_id"];
	$reservation->statut = $row["res_statut"];
	return $reservation;
}

function getUserReservations($uid, $week_nb, ...$clauses){
	$sql = "select reservation.*, o.ope_id as ope_id, o.ope_week_nb as ope_week_nb, t.user_name as t_name, s.user_name as s_name, c.categ_name as categ_name "
		."from users as t, users as s, categories as c, reservation "
		."left join student_operation as o on ope_res_id=res_id "
		."where t.user_id=res_tid and s.user_id=res_sid and c.categ_id=res_categ_id "
		."and res_week_nb=$week_nb "
		."and res_statut not in(".RES_STATUT_DELETING.",".RES_STATUT_DELETED.") "
		."and $uid in(res_tid, res_sid)";
	if(!empty($clauses)){
		$sql .= concat(" and ", " and ", ...$clauses);
	}
	$reservationArr = dbGetObjsByQuery($sql, 'dbLineToReservation');

	return $reservationArr;

}

function getReservationById($res_id, ...$clauses){
	$sql = "select reservation.*, o.ope_id as ope_id, o.ope_week_nb as ope_week_nb, t.user_name as t_name, s.user_name as s_name, c.categ_name as categ_name "
		."from users as t, users as s, categories as c, reservation "
		."left join student_operation as o on ope_res_id=res_id "
		."where res_id=$res_id and t.user_id=res_tid and s.user_id=res_sid and c.categ_id=res_categ_id ";
	if(!empty($clauses)){
		$sql .= concat(" and ", " and ", ...$clauses);
	}

	$reservation = dbGetObjByQuery($sql, 'dbLineToReservation');

	return $reservation;

}


/* --------------------------------------------------------------- */
define("OPE_STATUT_TODELETE", 0);
define("OPE_STATUT_TOCREATE", 1);
define("OPE_STATUT_TOMOVE", 2);

class Operation{
	public $ope_id;
	public $res_id;
	public $tid;
	public $t_name;
	public $sid;
	public $s_name;
	public $categ_id;
	public $categ_name;
	public $week_nb;
	public $day_nb;
	public $begin_nb;
	public $end_nb;
	public $statut;
}

function dbLineToOperation($row){
		$operation = new Operation();
		$operation->ope_id = $row["ope_id"];
		$operation->res_id = $row["ope_res_id"];
		$operation->tid = $row["ope_tid"];
		$operation->t_name = $row["t_name"];
		$operation->sid = $row["sid"];
		$operation->s_name = $row["s_name"];
		$operation->categ_id = $row["ope_categ_id"];
		$operation->categ_name = $row["categ_name"];
		$operation->week_nb = $row["ope_week_nb"];
		$operation->res_week_nb = $row["res_week_nb"];
		$operation->day_nb = $row["ope_day_nb"];
		$operation->begin_nb = $row["ope_begin_nb"];
		$operation->end_nb = $row["ope_end_nb"];
		$operation->statut = $row["ope_statut"];
		return $operation;	
}

function getUserOperations($uid, $week_nb, ...$clauses){
	$sql = "select student_operation.*, r.res_week_nb as res_week_nb, t.user_name as t_name, s.user_id as sid, s.user_name as s_name, c.categ_name as categ_name "
		."from student_session, users as t, users as s, categories as c, student_operation "
		."left join reservation as r on ope_res_id=res_id "
		."where ope_session_id=session_id and session_expire_time>=CURRENT_TIMESTAMP "
		."and t.user_id=ope_tid and s.user_id=session_sid and c.categ_id=ope_categ_id "
		."and ope_week_nb=$week_nb "
		."and $uid in(ope_tid, session_sid)";
	if(!empty($clauses)){
		$sql .= concat(" and ", " and ", ...$clauses);
	}

	$operationArr = dbGetObjsByQuery($sql, 'dbLineToOperation');

	return $operationArr;

}

function getOperationById($ope_id, ...$clauses){
	$sql = "select student_operation.*, r.res_week_nb as res_week_nb, t.user_name as t_name, s.user_id as sid, s.user_name as s_name, c.categ_name as categ_name "
		."from student_session, users as t, users as s, categories as c, student_operation "
		."left join reservation as r on ope_res_id=res_id "
		."where ope_session_id=session_id and session_expire_time>=CURRENT_TIMESTAMP "
		."and t.user_id=ope_tid and s.user_id=session_sid and c.categ_id=ope_categ_id "
		."and ope_id=$ope_id";
	if(!empty($clauses)){
		$sql .= concat(" and ", " and ", ...$clauses);
	}

	$operation = dbGetObjByQuery($sql, 'dbLineToOperation');

	return $operation;

}

/* ---------------------------------------------------------------- */
define("TCA_STATUT_AVAILABLE", 1);
define("TCA_STATUT_OCCUPIED", 0);


class DayCalendar{
	public $day_nb;
	public $day_str;
	public $day_status;
}

function getTeacherCalendar($tid, $week_nb){
	$sql = "select * from teacher_schedule where ts_tid=$tid and ts_week_nb=$week_nb";
	$dayCalendarArr = dbGetObjByQuery($sql, function($row){
		$db_week_nb = $row["ts_week_nb"];
		$day_nb = getWeekFirstDayNb($db_week_nb);
		$calendar7Day = array();
		for($i=0; $i<7; $i++){
			if(empty($row["ts_slot_$i"])){
				array_push($calendar7Day, defaultDayCalendar($day_nb+$i));
			}else{
				$calendar = new DayCalendar();
				$calendar->day_nb = $day_nb+$i;
				$calendar->day_str = dayNbToStr($day_nb+$i);
				$calendar->day_status = calendarStringToArray($row["ts_slot_$i"]);
				array_push($calendar7Day, $calendar);
			}
		}
		return $calendar7Day;
	});

	if(empty($dayCalendarArr)){
		$dayCalendarArr = defaultWeekCalendar($week_nb);
	}
	return $dayCalendarArr;
}


function calendarArrayToString($arr){
	return implode($arr);
}

function calendarStringToArray($str){
	if(!empty($str)){
		$arr = str_split($str);
		foreach ($arr as $i => $v) {
			$arr[$i] = $v-0;
		}
		return $arr;
	}
	return array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
}

function defaultDayCalendar($day_nb){
	$calendar = new DayCalendar();
	$calendar->day_nb = $day_nb;
	$calendar->day_str = dayNbToStr($day_nb);
	$calendar->day_status = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
	return $calendar;
}

function defaultWeekCalendar($week_nb){
	$day_nb = getWeekFirstDayNb($week_nb);
	$calendar = array();
	for($i=0; $i<7; $i++){
		array_push($calendar, defaultDayCalendar($day_nb+$i));
	}
	return $calendar;
}

function updateCalendarsToDb($tid, $week_nb, $schedule_data){
	$slot_0 = calendarArrayToString($schedule_data[0]->day_status);
	$slot_1 = calendarArrayToString($schedule_data[1]->day_status);
	$slot_2 = calendarArrayToString($schedule_data[2]->day_status);
	$slot_3 = calendarArrayToString($schedule_data[3]->day_status);
	$slot_4 = calendarArrayToString($schedule_data[4]->day_status);
	$slot_5 = calendarArrayToString($schedule_data[5]->day_status);
	$slot_6 = calendarArrayToString($schedule_data[6]->day_status);
      
	$sql = "insert into teacher_schedule(ts_tid, ts_week_nb, ts_slot_0, ts_slot_1, ts_slot_2, ts_slot_3, ts_slot_4, ts_slot_5, ts_slot_6) ";
	$sql .= "values($tid, $week_nb, '$slot_0', '$slot_1', '$slot_2', '$slot_3', '$slot_4', '$slot_5', '$slot_6') ";
	$sql .= "ON DUPLICATE KEY UPDATE ts_slot_0='$slot_0', ts_slot_1='$slot_1', ts_slot_2='$slot_2', ts_slot_3='$slot_3', ts_slot_4='$slot_4', ts_slot_5='$slot_5', ts_slot_6='$slot_6'";
	query($sql);

	updateUserWeekStamp($tid, $week_nb);
}

