-- RevisionCommentSupplement version 0.4.0 database schema update

BEGIN;

CREATE SEQUENCE rev_comment_supp_history_id_seq;
CREATE TABLE rev_comment_supp_history (
  rcsh_id         BIGINT  NOT NULL PRIMARY KEY DEFAULT nextval('rev_comment_supp_history_id_seq'),
  rcsh_rev_id     INTEGER  NOT NULL DEFAULT 0,
  rcsh_user       INTEGER  NOT NULL DEFAULT 0,
  rcsh_user_text  TEXT  NOT NULL DEFAULT '',
  rcsh_timestamp  TIMESTAMPTZ  NOT NULL,
  rcsh_hidden    SMALLINT  NOT NULL DEFAULT 0,
  rcsh_supplement TEXT  NOT NULL,
  rcsh_reason     TEXT  NOT NULL
);
CREATE UNIQUE INDEX rcsh_id ON rev_comment_supp_history (rcsh_id);
CREATE INDEX rcsh_rev_id ON rev_comment_supp_history (rcsh_rev_id,rcsh_hidden);
CREATE INDEX rcsh_timestamp ON rev_comment_supp_history (rcsh_timestamp,rcsh_rev_id);
CREATE INDEX rcsh_user_text ON rev_comment_supp_history (rcsh_user_text,rcsh_rev_id,rcsh_timestamp);

ALTER TABLE rev_comment_supp
  ADD rcs_latest BIGINT;
ALTER TABLE rev_comment_supp
  ALTER rcs_latest SET NOT NULL;
ALTER TABLE rev_comment_supp
  ALTER rcs_latest SET DEFAULT 0;

LOCK TABLE rev_comment_supp, rev_comment_supp_history;

INSERT INTO rev_comment_supp_history (
    rcsh_rev_id,
    rcsh_user,
    rcsh_user_text,
    rcsh_timestamp,
    rcsh_supplement
  )
  SELECT
    rcs_rev_id,
    rcs_user,
    rcs_user_text,
    rcs_timestamp,
    rcs_supplement
  FROM rev_comment_supp;

UPDATE rev_comment_supp
  SET rcs_latest=rcsh_id
  FROM rev_comment_supp_history
  WHERE rcs_rev_id=rcsh_rev_id;

COMMIT;
