<?php
define("TCA_TYPE_LOAD", "load");
define("TCA_TYPE_UPDATE", "upload");
define("TCA_TYPE_REFRESH", "refresh");

class DayCalendar{
	public $day_nb;
	public $day_str;
	public $day_status;
}

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

function getUserReservations($uid, $week_nb){
	return null;
}

function getUserOperations($uid, $week_nb){
	return null;
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

