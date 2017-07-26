<?php
/*

  `tc_tid` int(6) unsigned NOT NULL,
  `tc_categ_id` int(6) unsigned NOT NULL ,
  `tc_expire_time` datetime,

*/
class TeacherCateg{
	public $tid;
	public $categ;
	public $tc_expire_time; 
	public $in_cell; // $expire_time == null || $expire_time > $read_time
	public $tp_prise;
	public $tp_id;
	public $tp_effective_time;
	public $read_time;

	public $count_students;
	public $count_all_reservations;
	public $count_active_reservations;
	public $count_selections;
	public $count_purchase;
	

	/* --- definitions for db functions --- */
	static public function dbLineToObj($line){
		$obj = new TeacherCateg();
		$obj->tid = $line["tid"];
		$obj->categ = new Categ($line["categ_id"], $line["categ_name"]);
		$obj->tc_expire_time = $line["tc_expire_time"]; 
		$obj->in_cell = $line["in_cell"];

		$obj->tp_id = $line["tp_id"];
		$obj->tp_prise = $line["tp_prise"];
		$obj->tp_effective_time = $line["tp_effective_time"];

		$obj->read_time = $line["read_time"];
		return $obj;
	}

	static public function getTeacherCategs($tid, $read_time='CURRENT_TIMESTAMP'){
		$sql  = "select $tid as tid, categories.*, $read_time as read_time, tc_expire_time,";
		$sql .= " tc_tid is not null and (tc_expire_time is null or tc_expire_time>$read_time) as in_cell,";
		$sql .= " tp_id, tp_prise, tp_effective_time";
		$sql .= " from categories";
		$sql .= " left join teacher_categ on categ_id=tc_categ_id and tc_tid=$tid";
		// $sql .= " left join users on tc_tid=user_id";
		$sql .= " left join (select * from teacher_prise where tp_id in(select max(tp_id) from teacher_prise";
		$sql .= " where $read_time>=tp_effective_time and tp_tid=$tid group by tp_categ_id)) p";
		$sql .= " on tc_tid=tp_tid and tp_categ_id=categ_id order by categ_id";

		return dbGetObjsByQuery($sql, "TeacherCateg::dbLineToObj");
	}

}

function getCurrentCategTeachers($categ_id, $sid, $demande_time){
	$sql = "select user_id, user_name, tp_id, tp_prise from users, teacher_categ,";
	$sql.= "(select tp_id, tp_tid, tp_prise from teacher_prise where tp_prise is not null and tp_id in";
	$sql.= "(select max(tp_id) from teacher_prise where tp_effective_time<'$demande_time' and tp_categ_id=$categ_id group by tp_tid)) as p ";
	$sql.= "where tc_tid=user_id and tc_categ_id=$categ_id and user_id<>$sid and (tc_expire_time is null or tc_expire_time >'$demande_time') and tp_tid=user_id";

	return dbGetObjsByQuery($sql, function($line){
		$obj = (object)[
			'tid' => $line["user_id"],
			't_name' => $line["user_name"],
			'teacher_prise_id' => $line["tp_id"],
			'teacher_prise' => $line["tp_prise"],
		];

		return $obj;
	});

}
/*
static public function getTeacherAvailableCategs($tid){
	return dbFindObjs('Categ', "categ_id in(select tc_categ_id from teacher_categs where tc_tid=$tid)");
}

static public function deleteTeacherAvailableCategs($tid){
	return dbFindObjs('Categ', "categ_id in(select tc_categ_id from teacher_categs where tc_tid=$tid)");
}
*/

