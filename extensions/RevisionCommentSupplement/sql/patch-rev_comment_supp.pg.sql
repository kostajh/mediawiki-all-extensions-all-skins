-- RevisionCommentSupplement version 0.3.0 database schema update

BEGIN;

DROP INDEX rcs_revid;
DROP INDEX rcs_user_timestamp;

ALTER TABLE rev_comment_supp
  ALTER COLUMN rcs_user SET DEFAULT 0,
  ALTER COLUMN rcs_user_name SET DEFAULT '',
  ALTER COLUMN rcs_user_name SET NOT NULL,
  ALTER COLUMN rcs_timestamp SET NOT NULL,
  ALTER COLUMN rcs_comment SET NOT NULL;

ALTER TABLE rev_comment_supp
  RENAME COLUMN rcs_user_name TO rcs_user_text;

CREATE UNIQUE INDEX rcs_rev_id ON rev_comment_supp (rcs_rev_id);
CREATE INDEX rcs_timestamp ON rev_comment_supp (rcs_timestamp,rcs_rev_id);
CREATE INDEX rcs_user_text ON rev_comment_supp (rcs_user_text,rcs_timestamp,rcs_rev_id);

COMMIT;
