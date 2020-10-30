CREATE TABLE IF NOT EXISTS `#__mue_messages` (
  `msg_id` int(11) NOT NULL AUTO_INCREMENT,
  `msg_from` int(11) NOT NULL,
  `msg_to` int(11) NOT NULL,
  `msg_subject` varchar(255) DEFAULT NULL,
  `msg_body` text,
  `msg_status` varchar(25) NOT NULL,
  `msg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`msg_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ALTER TABLE `#__mue_messages`  ADD `ud_id` INT NOT NULL FIRST,  ADD   PRIMARY KEY  (`ud_id`);
-- ALTER TABLE `#__mue_messages` COLUMN `ud_id` INT auto_increment;