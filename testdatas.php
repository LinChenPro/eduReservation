<?php
$page_name = "index";

require_once('defines/environnement_head.php');

/*
dbDeleteObjs("Categ");
dbInsert(Categ::getInstance("Math"));
dbInsert(Categ::getInstance("Music"));
dbInsert(Categ::getInstance("English"));
dbInsert(Categ::getInstance("Physic"));


dbDeleteObjs("User");
dbInsert(User::getInstance("zhao","zhao"));
dbInsert(User::getInstance("qian","qian"));
dbInsert(User::getInstance("sun","sun"));
dbInsert(User::getInstance("li","li"));

query("delete from teacher_categ");
query("insert into teacher_categ(tc_tid,tc_categ_id) values(1, 1)");
query("insert into teacher_categ(tc_tid,tc_categ_id) values(2, 2)");
query("insert into teacher_categ(tc_tid,tc_categ_id) values(3, 3)");
query("insert into teacher_categ(tc_tid,tc_categ_id) values(1, 2)");
query("insert into teacher_categ(tc_tid,tc_categ_id,tc_expire_time) values(1, 3, '2017-07-10')");
query("insert into teacher_categ(tc_tid,tc_categ_id,tc_expire_time) values(1, 4, '2017-07-10')");

query("insert into teacher_prise(tp_tid,tp_categ_id,tp_prise,tp_effective_time) values(1, 1, 1500, '2017-07-10')");
query("insert into teacher_prise(tp_tid,tp_categ_id,tp_prise) values(1, 1, 1600)");



query(
	"insert into teacher_schedule(ts_tid,ts_week_nb,ts_slot_0,ts_slot_1,ts_slot_2,ts_slot_3,ts_slot_4,ts_slot_5,ts_slot_6) values(1, 1," 
	."'100101101001100101101001100101101001100101101001',"
	."'100101101001100101101001100101101001100101101001',"
	."'100101101001100101101001100101101001100101101001',"
	."'',"
	."'100101101001100101101001100101101001100101101001',"
	."'100101101001100101101001100101101001100101101001',"
	."'100101101001100101101001100101101001100101101001')"
);


query("insert into reservation(res_tid,res_sid,res_categ_id,res_tp_id,res_week_nb,res_day_nb,res_begin_nb,res_end_nb,res_statut,res_create_time) values(1, 2, 1, 1, 1, 11, 20, 21, 1, CURRENT_TIMESTAMP)");
query("insert into reservation(res_tid,res_sid,res_categ_id,res_tp_id,res_week_nb,res_day_nb,res_begin_nb,res_end_nb,res_statut,res_create_time) values(3, 1, 2, 2, 1, 12, 30, 30, 1, CURRENT_TIMESTAMP)");
query("insert into reservation(res_tid,res_sid,res_categ_id,res_tp_id,res_week_nb,res_day_nb,res_begin_nb,res_end_nb,res_statut,res_create_time) values(2, 3, 3, 3, 1, 13, 40, 41, 2, CURRENT_TIMESTAMP)");

*/

$tid = 1; $sid = 2; $categ_id = 2; $tp_id = 1;

$week_nb = 2; $day_nb = 17; $begin_nb = 18; $end_nb = 19; 
$statut = OPE_STATUT_TOCREATE;
addOperation($tid, $sid, $categ_id, $tp_id, $week_nb, $day_nb, $begin_nb, $end_nb, $statut);

$week_nb = 2; $day_nb = 18; $begin_nb = 25; $end_nb = 26; 
$statut = OPE_STATUT_TODELETE;
addOperation($tid, $sid, $categ_id, $tp_id, $week_nb, $day_nb, $begin_nb, $end_nb, $statut);


insert into purchase(pur_tid, pur_sid, pur_categ_id, pur_tp_id, pur_hour_total, pur_hour_rest, pur_create_time) value(1, 2, 1, 1, 10, 10, CURRENT_TIMESTAMP);

insert into purchase(pur_tid, pur_sid, pur_categ_id, pur_tp_id, pur_hour_total, pur_hour_rest, pur_create_time) value(1, 2, 1, 7, 10, 10, CURRENT_TIMESTAMP);

insert into student_balance(sb_sid, sb_amount) value(2, 20000);

include_once('defines/environnement_foot.php');

