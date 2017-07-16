<?php
static public function getTeacherAvailableCategs($tid){
	return dbFindObjs('Categ', "categ_id in(select tc_categ_id from teacher_categs where tc_tid=$tid)");
}

static public function deleteTeacherAvailableCategs($tid){
	return dbFindObjs('Categ', "categ_id in(select tc_categ_id from teacher_categs where tc_tid=$tid)");
}

