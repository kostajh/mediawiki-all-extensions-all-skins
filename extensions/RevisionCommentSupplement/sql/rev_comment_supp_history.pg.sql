
-- Postgres version of schema

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

COMMIT;
