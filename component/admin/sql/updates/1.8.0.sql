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