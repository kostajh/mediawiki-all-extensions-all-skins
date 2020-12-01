-- RevisionCommentSupplement version 0.3.0 database schema update

ALTER TABLE /*_*/rev_comment_supp
  DROP INDEX rcs_revid,
  DROP INDEX rcs_user_timestamp,
  CHANGE rcs_user_name rcs_user_text varchar(255) binary NOT NULL default '',
  CHANGE rcs_timestamp rcs_timestamp varbinary(14) NOT NULL default '';

CREATE UNIQUE INDEX /*i*/rcs_rev_id ON /*_*/rev_comment_supp (rcs_rev_id);
CREATE INDEX /*i*/rcs_timestamp ON /*_*/rev_comment_supp (rcs_timestamp,rcs_rev_id);
CREATE INDEX /*i*/rcs_user_text ON /*_*/rev_comment_supp (rcs_user_text,rcs_rev_id,rcs_timestamp);
