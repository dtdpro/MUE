ALTER TABLE  `#__mue_ufields` CHANGE  `uf_type`  `uf_type` ENUM(  'textar',  'textbox',  'multi',  'cbox',  'mcbox',  'yesno',  'dropdown',  'message',  'email',  'username',  'phone',  'password',  'mlist',  'birthday',  'captcha',  'mailchimp',  'cmlist' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL