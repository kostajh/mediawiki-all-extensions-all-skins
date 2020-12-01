-- RevisionCommentSupplement version 0.4.0 database schema update

BEGIN;

ALTER TABLE rev_comment_supp
  RENAME COLUMN rcs_comment TO rcs_supplement;

COMMIT;
