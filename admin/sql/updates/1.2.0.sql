CREATE TABLE IF NOT EXISTS `#__mams_usersubs` (
  `usrsub_id` int(11) NOT NULL AUTO_INCREMENT,
  `usrsub_user` int(11) NOT NULL,
  `usrsub_course` int(11) NOT NULL,
  `usrsub_type` enum('paypal','redeem','admin', 'google') NOT NULL,
  `usrsub_transid` varchar(255) NOT NULL,
  `usrsub_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usrsub_ip` varchar(20) NOT NULL,
  `usrsub_status` enum('notyetstarted','verified','canceled','accepted','pending','started','denied','refunded','failed','pending','reversed','canceled_reversal','expired','voided','completed','dispute') NOT NULL,
  `usrsub_start` datetime NOT NULL,
  `usrsub_end` datetime NOT NULL,
  PRIMARY KEY (`usrsub_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__mams_subs` (
  `sub_id` int(11) NOT NULL AUTO_INCREMENT,
  `sub_inttitle` varchar(255) NOT NULL,
  `sub_exttitle` varchar(255) NOT NULL,
  `sub_desc` TEXT NOT NULL,
  `sub_length` int(11) NOT NULL,
  `sub_period` enum('Day','Week','Month', 'Year') NOT NULL,
  `sub_cost` float NOT NULL,
  `sub_recurring` tinyint(1) NOT NULL DEFAULT '0',
  `published` int(11) NOT NULL,
  `access` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`sub_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;