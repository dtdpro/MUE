CREATE TABLE IF NOT EXISTS `#__mue_coupons` (
  `cu_id` int(11) NOT NULL AUTO_INCREMENT,
  `cu_type` enum('percent','amount') NOT NULL,
  `cu_code` varchar(50) NOT NULL,
  `cu_limit` int(11) NOT NULL,
  `cu_value` double NOT NULL,
  `cu_start` date NOT NULL,
  `cu_end` date NOT NULL,
  `cu_plans` text NOT NULL,
  `published` int(11) NOT NULL DEFAULT '1',
  `access` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `#__mue_usersubs` ADD `usrsub_coupon` varchar(50) NOT NULL;

ALTER TABLE `#__mue_subs` ADD `sub_type` VARCHAR(10) NOT NULL DEFAULT 'normal' AFTER `sub_desc`;

ALTER TABLE `#__mue_usersubs` CHANGE `usrsub_type` `usrsub_type` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `#__mue_usersubs` ADD `usrsub_cost` VARCHAR(50) NOT NULL AFTER `usrsub_coupon`;

ALTER TABLE `#__mue_ugroups` ADD `ug_send_welcome_email` BOOLEAN NOT NULL DEFAULT TRUE AFTER `ug_desc`;

ALTER TABLE `#__mue_usergroup` ADD `userg_subendplanname` VARCHAR(255) NOT NULL AFTER `userg_lastpaidvia`;