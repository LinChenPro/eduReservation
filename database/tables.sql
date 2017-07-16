CREATE TABLE `categories` (
  `categ_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `categ_name` varchar(50),
  PRIMARY KEY (`categ_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `user_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50),
  `user_pwd` varchar(50),
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `teacher_categ` (
  `tc_tid` int(6) unsigned NOT NULL,
  `tc_categ_id` int(6) unsigned NOT NULL ,
  `tc_expire_time` datetime,
  PRIMARY KEY (`tc_tid`, `tc_categ_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `teacher_prise` (
  `tp_tid` int(6) unsigned NOT NULL,
  `tp_categ_id` int(6) NOT NULL unsigned,
  `tp_prise` int NOT NULL,
  `tp_effective_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tp_tid`, `tp_categ_id`, `tp_effective_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `teacher_remuneration` (
  `tr_tid` int(6) unsigned NOT NULL,
  `tr_month_nb` int unsigned NOT NULL,
  `tr_sum` int NOT NULL DEFAULT 0,
  `tr_paydate` date,
  `tr_paid` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`tr_tid`, `tr_month_nb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `teacher_schedule` (
  `ts_tid` int(6) unsigned NOT NULL,
  `ts_week_nb` int unsigned NOT NULL,
  `ts_slot_0` char(48) NOT NULL,
  `ts_slot_1` char(48) NOT NULL,
  `ts_slot_2` char(48) NOT NULL,
  `ts_slot_3` char(48) NOT NULL,
  `ts_slot_4` char(48) NOT NULL,
  `ts_slot_5` char(48) NOT NULL,
  `ts_slot_6` char(48) NOT NULL,
  PRIMARY KEY (`ts_tid`, `ts_week_nb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `student_balance` (
  `sb_sid` int(6) unsigned NOT NULL ,
  `sb_amount` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`sb_sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `student_session` (
  `session_id` int(6)  unsigned NOT NULL AUTO_INCREMENT,
  `session_sid` int(6) unsigned NOT NULL,
  `session_expire_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `session_create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `student_operation` (
  `ope_id` int(6)  unsigned NOT NULL AUTO_INCREMENT,
  `ope_session_id` int(6)  unsigned NOT NULL,
  `ope_res_id` int(6)  unsigned,
  `ope_tid` int(6) unsigned NOT NULL,
  `ope_categ_id` int(6) unsigned NOT NULL,
  `ope_day_nb` int unsigned NOT NULL,
  `ope_begin_nb` int unsigned NOT NULL,
  `ope_end_nb` int unsigned NOT NULL,
  `ope_statut` tinyint(1),
  `ope_create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ope_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `reservation` (
  `res_id` int(6)  unsigned NOT NULL AUTO_INCREMENT,
  `res_tid` int(6) unsigned NOT NULL,
  `res_sid` int(6) unsigned NOT NULL,
  `res_categ_id` int(6) unsigned NOT NULL,
  `res_day_nb` int unsigned NOT NULL,
  `res_begin_nb` int unsigned NOT NULL,
  `res_end_nb` int unsigned NOT NULL,
  `res_statut` tinyint(1),
  `res_last_modify` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`res_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



