<?php
define("TCA_TYPE_LOAD", "load");
define("TCA_TYPE_UPDATE", "upload");
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
	$response->timestamp = getUserWeekStamp($tid, $week_nb);
	$response->schedule_data = getTeacherCalendar($tid, $week_nb);
	$response->reservation_data = getUserReservations($tid, $week_nb);
	$response->operation_data = getUserOperations($tid, $week_nb);
	$response->succes = true;
	$response->error = null;
	$response->infos = null;

	return $response;
}
/* ------------------------------------------------------ */
define("RES_STATUT_CREATED", 1);
define("RES_STATUT_DELETING", 2);
define("RES_STATUT_DELETED", 3);
define("RES_STATUT_FIXED", 4);
define("RES_STATUT_ACTIVE", 5);
define("RES_STATUT_FINISHED", 6);

class Reservation{
	public $res_id;
	public $tid;
	public $t_name;
	public $sid;
	public $s_name;
	public $categ_id;
	public $categ_name;
	public $day_nb;
	public $begin_nb;
	public $end_nb;
	public $statut;
}

function getUserReservations($uid, $week_nb){
	$sql = "select reservation.*, t.user_name as t_name, s.user_name as s_name, c.categ_name as categ_name "
		."from reservation, users as t, users as s, categories as c "
		."where t.user_id=res_tid and s.user_id=res_sid and c.categ_id=res_categ_id and res_statut in(1,4,5,6) "
		."and 1 in(res_tid, res_sid)";

	$reservationArr = dbGetObjsByQuery($sql, function($row){
		$reservation = new Reservation();
		$reservation->res_id = $row["res_id"];
		$reservation->tid = $row["res_tid"];
		$reservation->t_name = $row["t_name"];
		$reservation->sid = $row["res_sid"];
		$reservation->s_name = $row["s_name"];
		$reservation->categ_id = $row["res_categ_id"];
		$reservation->categ_name = $row["categ_name"];
		$reservation->day_nb = $row["res_day_nb"];
		$reservation->begin_nb = $row["res_begin_nb"];
		$reservation->end_nb = $row["res_end_nb"];
		$reservation->statut = $row["res_statut"];
		return $reservation;
	});

	return $reservationArr;

}

/* --------------------------------------------------------------- */
function getUserOperations($uid, $week_nb){
	return null;
}
/* ---------------------------------------------------------------- */
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

/**** function week stamp *****/
function getUserWeekStamp($uid, $week_nb){
	$sql = "select * from weekly_action_stamp where stamp_uid=$uid and stamp_week_nb=$week_nb";

	$stamp_time = dbGetObjByQuery($sql, function($row){
		return $row["stamp_time"];
	});

	if($stamp_time==null){
		$stamp_time = getDateZeroStr();
	}
	return $stamp_time;
}

function checkChange($uid, $week_nb, $demand_timestamp){
	return getUserWeekStamp($uid, $week_nb)>$demand_timestamp;
}

//"insert into weekly_action_stamp(stamp_uid, stamp_week_nb) value(1, 1) ON DUPLICATE KEY UPDATE stamp_time=CURRENT_TIMESTAMP"
/************************/

