CREATE TABLE IF NOT EXISTS `#__mue_coupons` (
    `cu_id` int NOT NULL AUTO_INCREMENT,
    `cu_type` enum('percent','amount') NOT NULL,
    `cu_code` varchar(50) NOT NULL,
    `cu_limit` int NOT NULL,
    `cu_value` double NOT NULL,
    `cu_start` date NOT NULL,
    `cu_end` date NOT NULL,
    `cu_plans` text NOT NULL,
    `published` int NOT NULL DEFAULT '1',
    `access` int NOT NULL DEFAULT '1',
    PRIMARY KEY (`cu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE IF NOT EXISTS `#__mue_messages` (
    `msg_id` int NOT NULL AUTO_INCREMENT,
    `msg_from` int NOT NULL DEFAULT '0',
    `msg_to` int NOT NULL DEFAULT '0',
    `msg_subject` varchar(255) DEFAULT NULL,
    `msg_body` text,
    `msg_status` varchar(25) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
    `msg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`msg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE IF NOT EXISTS `#__mue_subs` (
    `sub_id` int NOT NULL AUTO_INCREMENT,
    `sub_inttitle` varchar(255) NOT NULL,
    `sub_exttitle` varchar(255) NOT NULL,
    `sub_desc` text NOT NULL,
    `sub_type` varchar(10) NOT NULL DEFAULT 'normal',
    `sub_length` int NOT NULL,
    `sub_period` enum('Day','Week','Month','Year') NOT NULL,
    `sub_cost` float NOT NULL,
    `sub_recurring` tinyint(1) NOT NULL DEFAULT '0',
    `published` int NOT NULL,
    `access` int NOT NULL,
    `ordering` int NOT NULL,
    PRIMARY KEY (`sub_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

CREATE TABLE IF NOT EXISTS `#__mue_ufields` (
    `uf_id` bigint NOT NULL AUTO_INCREMENT,
    `uf_sname` varchar(50) NOT NULL,
    `ordering` smallint NOT NULL COMMENT 'qnum',
    `uf_name` varchar(255) NOT NULL,
    `uf_type` varchar(25) NOT NULL,
    `uf_cms` tinyint(1) NOT NULL DEFAULT '0',
    `uf_req` tinyint(1) NOT NULL DEFAULT '1',
    `uf_note` text NOT NULL,
    `uf_match` varchar(50) NOT NULL DEFAULT '0',
    `published` tinyint(1) NOT NULL DEFAULT '1',
    `uf_hidden` tinyint(1) NOT NULL DEFAULT '0',
    `uf_change` tinyint(1) NOT NULL DEFAULT '1',
    `uf_reg` tinyint(1) NOT NULL DEFAULT '1',
    `uf_profile` tinyint(1) NOT NULL DEFAULT '1',
    `uf_min` int NOT NULL DEFAULT '0',
    `uf_max` int NOT NULL DEFAULT '0',
    `uf_default` varchar(255) NOT NULL,
    `uf_userdir` tinyint(1) NOT NULL DEFAULT '0',
    `params` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
    PRIMARY KEY (`uf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 AUTO_INCREMENT=100;

CREATE TABLE IF NOT EXISTS `#__mue_ufields_opts` (
    `opt_id` bigint NOT NULL AUTO_INCREMENT,
    `opt_field` bigint NOT NULL,
    `opt_text` text NOT NULL,
    `ordering` int NOT NULL,
    `published` tinyint NOT NULL DEFAULT '1',
    PRIMARY KEY (`opt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE IF NOT EXISTS `#__mue_ugroups` (
    `ug_id` int NOT NULL AUTO_INCREMENT,
    `ug_name` varchar(255) NOT NULL,
    `ug_desc` text NOT NULL,
    `ug_send_welcome_email` tinyint(1) NOT NULL DEFAULT '1',
    `ug_welcome_email` text NOT NULL,
    `ug_lostinfo_email` text NOT NULL,
    `access` int NOT NULL,
    `published` tinyint NOT NULL,
    `ordering` int NOT NULL,
    PRIMARY KEY (`ug_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE IF NOT EXISTS `#__mue_uguf` (
    `uguf_field` int NOT NULL,
    `uguf_group` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE IF NOT EXISTS `#__mue_userdir` (
    `ud_id` int NOT NULL AUTO_INCREMENT,
    `ud_user` int NOT NULL,
    `ud_lat` float NOT NULL,
    `ud_lon` float NOT NULL,
    `ud_userinfo` text NOT NULL,
    `ud_searchinfo` text NOT NULL,
    `ud_usertags` text,
    PRIMARY KEY (`ud_id`),
    UNIQUE KEY `ud_user` (`ud_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE IF NOT EXISTS `#__mue_usergroup` (
    `userg_user` int NOT NULL DEFAULT '0',
    `userg_group` int NOT NULL DEFAULT '0',
    `userg_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `userg_notes` text NOT NULL,
    `userg_siteurl` varchar(255) NOT NULL,
    `userg_subsince` date DEFAULT NULL,
    `userg_subexp` date DEFAULT NULL,
    `userg_lastpaidvia` varchar(50) NOT NULL DEFAULT '',
    `userg_subendplanname` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`userg_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE IF NOT EXISTS `#__mue_users` (
    `usr_id` bigint NOT NULL AUTO_INCREMENT,
    `usr_user` int NOT NULL,
    `usr_field` int NOT NULL,
    `usr_data` text NOT NULL,
    PRIMARY KEY (`usr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE IF NOT EXISTS `#__mue_usersubs` (
    `usrsub_id` int NOT NULL AUTO_INCREMENT,
    `usrsub_user` int NOT NULL,
    `usrsub_sub` int NOT NULL,
    `usrsub_type` varchar(25) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
    `usrsub_transid` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
    `usrsub_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
    `usrsub_rpprofile` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
    `usrsub_rpstatus` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
    `usrsub_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `usrsub_ip` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
    `usrsub_status` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
    `usrsub_start` date DEFAULT NULL,
    `usrsub_end` date DEFAULT NULL,
    `usrsub_coupon` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
    `usrsub_cost` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
    PRIMARY KEY (`usrsub_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

CREATE TABLE IF NOT EXISTS `#__mue_usersubs_log` (
    `usl_id` int NOT NULL AUTO_INCREMENT,
    `usl_usid` int NOT NULL DEFAULT '0',
    `usl_user` int NOT NULL DEFAULT '0',
    `usl_sub` int NOT NULL DEFAULT '0',
    `usl_resarray` text NOT NULL,
    `usl_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`usl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

INSERT INTO `#__mue_ufields` (`uf_id`, `uf_sname`, `ordering`, `uf_name`, `uf_type`, `uf_cms`, `uf_req`, `uf_note`, `uf_match`, `published`, `uf_hidden`, `uf_change`, `uf_reg`, `uf_profile`, `uf_min`, `uf_max`, `uf_default`, `uf_userdir`, `params`) VALUES
(1, 'fname', 2, 'First Name', 'textbox', 0, 1, '', '', 1, 0, 1, 1, 1, 0, 0, '', 0, ''),
(2, 'lname', 3, 'Last Name', 'textbox', 0, 1, '', '', 1, 0, 1, 1, 1, 0, 0, '', 0, ''),
(3, 'email', 5, 'Email Address', 'email', 1, 1, '', '', 1, 0, 0, 1, 1, 0, 0, '', 0, ''),
(4, 'username', 7, 'Username', 'username', 1, 1, '', '', 1, 0, 0, 1, 1, 6, 0, '', 0, ''),
(5, 'block', 8, 'Block User', 'yesno', 1, 1, '', '', 1, 1, 0, 0, 0, 0, 0, '', 0, ''),
(6, 'cemail', 6, 'Confirm Email', 'email', 1, 1, '', 'email', 1, 0, 0, 1, 0, 0, 0, '', 0, ''),
(7, 'password', 9, 'Password', 'password', 1, 1, '', '', 1, 0, 1, 1, 1, 0, 0, '', 0, ''),
(8, 'cpassword', 10, 'Confirm Password', 'password', 1, 1, '', 'password', 1, 0, 1, 1, 1, 0, 0, '', 0, '');

INSERT INTO `#__mue_ugroups` (`ug_id`, `ug_name`, `ug_desc`, `ug_welcome_email`, `ug_lostinfo_email`, `access`, `published`, `ordering`) VALUES
(1, 'Physicians', 'MD, DO', '<p>Dear {fullname},</p>\r\n<p>Welcome to our site. Your user credentials are below.</p>\r\n<p>Site URL: <strong>{site_url}</strong></p>\r\n<p>Username: <strong>{username}</strong><br />\r\n  Password: <strong>{password}</strong></p>\r\n', '<p>Dear {fullname},</p>\r\n<p>Your password has been reset, the information is below</p>\r\n<p>Site URL: <strong>{site_url}</strong></p><p>Username: <strong>{username}</strong><br /> Password: <strong>{password}</strong></p>', 1, 1, 1);

INSERT INTO `#__mue_uguf` (`uguf_field`, `uguf_group`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1);




