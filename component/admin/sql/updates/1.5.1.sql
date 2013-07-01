ALTER TABLE  `#__mue_usersubs` CHANGE  `usrsub_type`  `usrsub_type` ENUM(  'paypal',  'redeem',  'admin',  'google',  'migrate',  'check' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
