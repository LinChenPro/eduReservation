<?php
$page_name = "index";

require_once('defines/environnement_head.php');


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

















include_once('defines/environnement_foot.php');

