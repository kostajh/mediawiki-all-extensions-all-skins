--
-- extension PasswordlessLogin
--
CREATE TABLE /*$wgDBprefix*/passwordlesslogin_challenges (
  challenge varchar(255) NOT NULL,
  challenge_user_id int(10) unsigned NOT NULL,
  success int(1) DEFAULT 0 NOT NULL,
  PRIMARY KEY (challenge),
  UNIQUE KEY (challenge_user_id)
) /*$wgDBTableOptions*/;
