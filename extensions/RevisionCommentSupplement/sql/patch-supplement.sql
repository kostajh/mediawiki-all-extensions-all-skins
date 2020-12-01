-- RevisionCommentSupplement version 0.4.0 database schema update

ALTER TABLE /*_*/rev_comment_supp
  CHANGE rcs_comment rcs_supplement tinyblob NOT NULL;

