CREATE TABLE IF NOT EXISTS `list_signup` (
  `ls_id` int(10) unsigned NOT NULL auto_increment PRIMARY KEY,
  `ls_name` varchar(255),
  `ls_email` varchar(255) NOT NULL,
  `ls_timestamp` binary(14) NOT NULL default ''
) /*$wgDBTableOptions*/;
