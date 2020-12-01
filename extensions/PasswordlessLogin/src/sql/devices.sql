--
-- extension PasswordlessLogin
--
CREATE TABLE /*$wgDBprefix*/passwordlesslogin_devices (
  id int NOT NULL AUTO_INCREMENT,
  device_id varchar(255) NULL,
  device_user_id int(10) unsigned NOT NULL,
  device_pair_token varchar(32) NULL,
  secret varchar(255) NULL,
  confirmed int(1) DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY (device_id),
  UNIQUE KEY (device_user_id)
) /*$wgDBTableOptions*/;
