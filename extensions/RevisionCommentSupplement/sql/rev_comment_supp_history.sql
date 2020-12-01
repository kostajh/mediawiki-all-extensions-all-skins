CREATE TABLE /*_*/rev_comment_supp_history (
  -- key
  rcsh_id bigint unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,

  -- rev_id
  rcsh_rev_id int unsigned NOT NULL default 0,

  -- user_id
  rcsh_user int unsigned NOT NULL default 0,

  -- user_name
  rcsh_user_text varchar(255) binary NOT NULL default '',

  -- modified
  rcsh_timestamp varbinary(14) NOT NULL default '',

  -- deleted flag
  rcsh_hidden tinyint unsigned NOT NULL default 0,

  -- additional comment
  rcsh_supplement tinyblob NOT NULL,

  -- reason
  rcsh_reason tinyblob NOT NULL

) /*$wgDBTableOptions*/;
CREATE UNIQUE INDEX /*i*/rcsh_id ON /*_*/rev_comment_supp_history (rcsh_id);
CREATE INDEX /*i*/rcsh_rev_id ON /*_*/rev_comment_supp_history (rcsh_rev_id,rcsh_hidden);
CREATE INDEX /*i*/rcsh_timestamp ON /*_*/rev_comment_supp_history (rcsh_timestamp,rcsh_rev_id);
CREATE INDEX /*i*/rcsh_user_text ON /*_*/rev_comment_supp_history (rcsh_user_text,rcsh_rev_id,rcsh_timestamp);
