<?php
$CURRENT_DATETIME = new DateTime();
define("DATE_ZERO", '2017-07-10');
define("DB_DATETIME_FORMAT", 'Y-m-d H:i:s');
define("DAY_FORMAT", 'Y-m-d');

// return DateTime
function getDateZero(){
	return new DateTime(DATE_ZERO);
}

// DateTime to day_nb
function dateToDayNb($date){
	global $CURRENT_DATETIME;
	if($date==null){
		$date = $CURRENT_DATETIME;
	}
	return date_diff(getDateZero(), $date)->days;
}

// DateTime to week_nb
function dateToWeekNb($date){
	global $CURRENT_DATETIME;
	if($date==null){
		$date = $CURRENT_DATETIME;
	}
	return dayNbToWeekNb(dateToDayNb($date));
}

// day_nb to week_nb
function dayNbToWeekNb($day_nb){
	return intdiv($day_nb, 7);
}

// week_nb to day_nb of 1st day
function getWeekFirstDayNb($week_nb){
	return $week_nb * 7;
}

// YYYY-MM-DD HH:mm:ss to DateTime
function getDbDateTime($date_str){
	return date_create_from_format(DB_DATETIME_FORMAT, $date_str);
}

// day_nb to DateTime
function dayNbToDateTime($day_nb){
	return getDateZero()->add(new DateInterval("P".$day_nb."D"));
}

// day_nb to YYYY-MM-DD
function dayNbToStr($day_nb, $format=DAY_FORMAT){
	return dayNbToDateTime($day_nb)->format($format);
}

// get string hh:mm - hh:mm
function hNbToCreneax($h_from, $h_to){
	if($h_to==null){
		$h_to = $h_from;
	}
	$t_begin = $h_from;
	$t_end = $h_to+1;
	return hNbToTime($t_begin)." - ".hNbToTime($t_end);
}

// get string hh:mm
function hNbToTime($h_nb){
	$hour = intdiv($h_nb,2);
	$minute = ($h_nb%2)*30;
	return sprintf("%'.02d",$hour).":".sprintf("%'.02d",$minute);
}

/*
echo hNbToCreneax(0)."<br>";
echo hNbToCreneax(1)."<br>";
echo hNbToCreneax(20)."<br>";
echo hNbToCreneax(0,23)."<br>";
echo hNbToCreneax(24,47)."<br>";

/*
echo dateToDayNb()."<br>";
echo dateToWeekNb()."<br>";
echo getWeekFirstDayNb(dateToWeekNb())."<br>";
*/

/*
$week_nb = dateToWeekNb();
$day_nb = getWeekFirstDayNb($week_nb);
echo $week_nb."<br>";
for($i=0; $i<7; $i++){
	echo dayNbToStr($day_nb+$i)."<br>";

}
*/