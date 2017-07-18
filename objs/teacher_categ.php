<?php
/*

  `tc_tid` int(6) unsigned NOT NULL,
  `tc_categ_id` int(6) unsigned NOT NULL ,
  `tc_expire_time` datetime,

*/
class TeacherCateg{
	public $teacher;
	public $categ;

	public $read_time;
	public $expire_time; 
	public $prise;
	public $in_cell; // $expire_time == null || $expire_time > $read_time

	public $count_students;
	public $count_all_reservations;
	public $count_active_reservations;
	public $count_selections;
	public $count_purchase;
	

	/* --- definitions for db functions --- */
	static public function dbLineToObj($line){
		$obj = new TeacherCateg();
		return $obj;
	}

	static public function getTeacherCategs($tid){
		var $sql = "select users.*, categories.*, CURRENT_TIMESTAMP as read_time, tc_expire_time, (tc_expire_time is null or tc_expire_time>CURRENT_TIMESTAMP) as in_cell,tp_prise, tp_effective_time from teacher_categ, users, categories,teacher_prise where tc_tid=user_id and categ_id=tc_categ_id and tc_tid=tp_tid and tp_categ_id=tc_categ_id and tc_tid=$tid";
		return dbGetObjsByQuery($sql, "TeacherCateg::dbLineToObj");
	}


}

static public function getTeacherAvailableCategs($tid){
	return dbFindObjs('Categ', "categ_id in(select tc_categ_id from teacher_categs where tc_tid=$tid)");
}

static public function deleteTeacherAvailableCategs($tid){
	return dbFindObjs('Categ', "categ_id in(select tc_categ_id from teacher_categs where tc_tid=$tid)");
}

