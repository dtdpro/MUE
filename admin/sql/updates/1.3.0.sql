CREATE TABLE IF NOT EXISTS `#__mue_userdir` (
  `ud_user` int(11) NOT NULL,
  `ud_lat` float NOT NULL,
  `ud_lon` float NOT NULL,
  `ud_userinfo` TEXT NOT NULL,
  `ud_searchinfo` TEXT NOT NULL,
  UNIQUE KEY `ud_user` (`ud_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE  `#__mue_ufields` ADD  `uf_userdir` BOOLEAN NOT NULL DEFAULT FALSE;
