-- This is the Postgres version
-- See namespace_names.sql for the original version

BEGIN;

CREATE TABLE namespace_names (
  ns_id INTEGER NOT NULL DEFAULT 0,
  ns_name TEXT NOT NULL DEFAULT '',
  ns_default INTEGER NOT NULL DEFAULT 0,
  ns_canonical INTEGER DEFAULT NULL
);

COMMIT;
