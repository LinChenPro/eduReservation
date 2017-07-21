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

*/


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








include_once('defines/environnement_foot.php');

