CREATE TABLE /*_*/rev_comment_supp (
  -- rev_id
  rcs_rev_id int unsigned NOT NULL PRIMARY KEY,

  -- rcsh_id
  rcs_latest bigint unsigned NOT NULL default 0,

  -- user_id
  rcs_user int unsigned NOT NULL default 0,

  -- user_name
  rcs_user_text varchar(255) binary NOT NULL default '',

  -- last modified
  rcs_timestamp varbinary(14) NOT NULL default '',

  -- additional comment
  rcs_supplement tinyblob NOT NULL

) /*$wgDBTableOptions*/;
CREATE UNIQUE INDEX /*i*/rcs_rev_id ON /*_*/rev_comment_supp (rcs_rev_id);
CREATE INDEX /*i*/rcs_timestamp ON /*_*/rev_comment_supp (rcs_timestamp,rcs_rev_id);
CREATE INDEX /*i*/rcs_user_text ON /*_*/rev_comment_supp (rcs_user_text,rcs_rev_id,rcs_timestamp);
