<?php

function getExistSession($sid){
	$sql = "select session_id from student_session where session_sid=$sid and session_expire_time>CURRENT_TIMESTAMP";
	return dbGetObjByQuery($sql, function($line){
		return $line["session_id"];
	});

}

function addOperation($tid, $sid, $categ_id, $tp_id, $week_nb, $day_nb, $begin_nb, $end_nb, $statut){
	$session_id = getExistSession($sid);
	$expire_time = "'2017-07-30'"; // for test
	if($session_id == null){
		$session_id = insertQuery(
			"insert into student_session(session_sid, session_expire_time) value($sid, $expire_time)"
		);
	}

	$res_id = 'NULL';
	if($statut==OPE_STATUT_TODELETE){ // insert test reservation
		$sql_insert_reservation = "insert into reservation(res_tid,res_sid,res_categ_id,res_tp_id,res_week_nb,res_day_nb,res_begin_nb,res_end_nb,res_statut,res_create_time) "
		."values($tid, $sid, $categ_id, $tp_id, $week_nb, $day_nb, $begin_nb, $end_nb, ".RES_STATUT_DELETING.", CURRENT_TIMESTAMP)";
		$res_id = insertQuery($sql_insert_reservation);	
	}

	$sql_insert_operation = "insert into student_operation(ope_session_id, ope_res_id, ope_tid, ope_categ_id,ope_tp_id,ope_week_nb,ope_day_nb,ope_begin_nb,ope_end_nb,ope_statut)"
		." values($session_id, $res_id, $tid, $categ_id, $tp_id, $week_nb, $day_nb, $begin_nb, $end_nb, $statut)";
	query($sql_insert_operation);

}
