-- SQL to create the initial tables for the MediaWiki database.
-- This is read and executed by the install script; you should
-- not have to run it by itself unless doing a manual install.
-- This is the PostgreSQL version.
-- For information about each table, please see the notes in maintenance/tables.sql
-- Please make sure all dollar-quoting uses $mw$ at the start of the line
-- TODO: Change CHAR/SMALLINT to BOOL (still used in a non-bool fashion in PHP code)

BEGIN;
SET client_min_messages = 'ERROR';

CREATE SEQUENCE user_user_id_seq MINVALUE 0 START WITH 0;
CREATE TABLE mwuser ( -- replace reserved word 'user'
  user_id                   INTEGER  NOT NULL  PRIMARY KEY DEFAULT nextval('user_user_id_seq'),
  user_name                 TEXT     NOT NULL  UNIQUE,
  user_real_name            TEXT,
  user_password             TEXT,
  user_newpassword          TEXT,
  user_newpass_time         TIMESTAMPTZ,
  user_token                TEXT,
  user_email                TEXT,
  user_email_token          TEXT,
  user_email_token_expires  TIMESTAMPTZ,
  user_email_authenticated  TIMESTAMPTZ,
  user_touched              TIMESTAMPTZ,
  user_registration         TIMESTAMPTZ,
  user_editcount            INTEGER,
  user_password_expires     TIMESTAMPTZ NULL
);
ALTER SEQUENCE user_user_id_seq OWNED BY mwuser.user_id;
CREATE INDEX user_email_token_idx ON mwuser (user_email_token);

-- Create a dummy user to satisfy fk contraints especially with revisions
INSERT INTO mwuser
  VALUES (DEFAULT,'Anonymous','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,now(),now());


CREATE SEQUENCE page_page_id_seq;
CREATE TABLE page (
  page_id            INTEGER        NOT NULL  PRIMARY KEY DEFAULT nextval('page_page_id_seq'),
  page_namespace     SMALLINT       NOT NULL,
  page_title         TEXT           NOT NULL,
  page_restrictions  TEXT,
  page_is_redirect   SMALLINT       NOT NULL  DEFAULT 0,
  page_is_new        SMALLINT       NOT NULL  DEFAULT 0,
  page_random        NUMERIC(15,14) NOT NULL  DEFAULT RANDOM(),
  page_touched       TIMESTAMPTZ,
  page_links_updated TIMESTAMPTZ    NULL,
  page_latest        INTEGER        NOT NULL, -- FK?
  page_len           INTEGER        NOT NULL,
  page_content_model TEXT,
  page_lang          TEXT                     DEFAULT NULL
);
ALTER SEQUENCE page_page_id_seq OWNED BY page.page_id;
CREATE UNIQUE INDEX name_title       ON page (page_namespace, page_title);
CREATE INDEX page_main_title         ON page (page_title text_pattern_ops) WHERE page_namespace = 0;
CREATE INDEX page_talk_title         ON page (page_title text_pattern_ops) WHERE page_namespace = 1;
CREATE INDEX page_user_title         ON page (page_title text_pattern_ops) WHERE page_namespace = 2;
CREATE INDEX page_utalk_title        ON page (page_title text_pattern_ops) WHERE page_namespace = 3;
CREATE INDEX page_project_title      ON page (page_title text_pattern_ops) WHERE page_namespace = 4;
CREATE INDEX page_mediawiki_title    ON page (page_title text_pattern_ops) WHERE page_namespace = 8;
CREATE INDEX page_random             ON page (page_random);
CREATE INDEX page_len                ON page (page_len);
CREATE INDEX page_redirect_namespace_len ON page (page_is_redirect, page_namespace, page_len);

CREATE FUNCTION page_deleted() RETURNS TRIGGER LANGUAGE plpgsql AS
$mw$
BEGIN
DELETE FROM recentchanges WHERE rc_namespace = OLD.page_namespace AND rc_title = OLD.page_title;
RETURN NULL;
END;
$mw$;

CREATE TRIGGER page_deleted AFTER DELETE ON page
  FOR EACH ROW EXECUTE PROCEDURE page_deleted();

CREATE SEQUENCE revision_rev_id_seq;
CREATE TABLE revision (
  rev_id             INTEGER      NOT NULL  UNIQUE DEFAULT nextval('revision_rev_id_seq'),
  rev_page           INTEGER          NULL  REFERENCES page (page_id) ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED,
  rev_comment_id     INTEGER      NOT NULL DEFAULT 0,
  rev_actor          INTEGER      NOT NULL DEFAULT 0,
  rev_timestamp      TIMESTAMPTZ  NOT NULL,
  rev_minor_edit     SMALLINT     NOT NULL  DEFAULT 0,
  rev_deleted        SMALLINT     NOT NULL  DEFAULT 0,
  rev_len            INTEGER          NULL,
  rev_parent_id      INTEGER          NULL,
  rev_sha1           TEXT         NOT NULL DEFAULT ''
);
ALTER SEQUENCE revision_rev_id_seq OWNED BY revision.rev_id;
CREATE UNIQUE INDEX revision_unique ON revision (rev_page, rev_id);
CREATE INDEX rev_timestamp_idx      ON revision (rev_timestamp);
CREATE INDEX rev_actor_timestamp    ON revision (rev_actor,rev_timestamp,rev_id);
CREATE INDEX rev_page_actor_timestamp ON revision (rev_page,rev_actor,rev_timestamp);

CREATE SEQUENCE text_old_id_seq;
CREATE TABLE pagecontent ( -- replaces reserved word 'text'
  old_id     INTEGER  NOT NULL  PRIMARY KEY DEFAULT nextval('text_old_id_seq'),
  old_text   TEXT,
  old_flags  TEXT
);
ALTER SEQUENCE text_old_id_seq OWNED BY pagecontent.old_id;


CREATE SEQUENCE archive_ar_id_seq;
CREATE TABLE archive (
  ar_id             INTEGER      NOT NULL  PRIMARY KEY DEFAULT nextval('archive_ar_id_seq'),
  ar_namespace      SMALLINT     NOT NULL,
  ar_title          TEXT         NOT NULL,
  ar_page_id        INTEGER          NULL,
  ar_parent_id      INTEGER          NULL,
  ar_sha1           TEXT         NOT NULL DEFAULT '',
  ar_comment_id     INTEGER      NOT NULL,
  ar_actor          INTEGER      NOT NULL,
  ar_timestamp      TIMESTAMPTZ  NOT NULL,
  ar_minor_edit     SMALLINT     NOT NULL  DEFAULT 0,
  ar_rev_id         INTEGER      NOT NULL,
  ar_deleted        SMALLINT     NOT NULL  DEFAULT 0,
  ar_len            INTEGER          NULL
);
ALTER SEQUENCE archive_ar_id_seq OWNED BY archive.ar_id;
CREATE INDEX archive_name_title_timestamp ON archive (ar_namespace,ar_title,ar_timestamp);
CREATE INDEX archive_actor                ON archive (ar_actor);
CREATE UNIQUE INDEX ar_revid_uniq ON archive (ar_rev_id);


CREATE TABLE categorylinks (
  cl_from           INTEGER      NOT NULL  REFERENCES page(page_id) ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED,
  cl_to             TEXT         NOT NULL,
  cl_sortkey        TEXT             NULL,
  cl_timestamp      TIMESTAMPTZ  NOT NULL,
  cl_sortkey_prefix TEXT         NOT NULL  DEFAULT '',
  cl_collation      TEXT         NOT NULL  DEFAULT 0,
  cl_type           TEXT         NOT NULL  DEFAULT 'page'
);
CREATE UNIQUE INDEX cl_from ON categorylinks (cl_from, cl_to);
CREATE INDEX cl_sortkey     ON categorylinks (cl_to, cl_sortkey, cl_from);


CREATE SEQUENCE ipblocks_ipb_id_seq;
CREATE TABLE ipblocks (
  ipb_id                INTEGER      NOT NULL  PRIMARY KEY DEFAULT nextval('ipblocks_ipb_id_seq'),
  ipb_address           TEXT             NULL,
  ipb_user              INTEGER          NULL  REFERENCES mwuser(user_id) ON DELETE SET NULL DEFERRABLE INITIALLY DEFERRED,
  ipb_by_actor          INTEGER      NOT NULL,
  ipb_reason_id         INTEGER      NOT NULL,
  ipb_timestamp         TIMESTAMPTZ  NOT NULL,
  ipb_auto              SMALLINT     NOT NULL  DEFAULT 0,
  ipb_anon_only         SMALLINT     NOT NULL  DEFAULT 0,
  ipb_create_account    SMALLINT     NOT NULL  DEFAULT 1,
  ipb_enable_autoblock  SMALLINT     NOT NULL  DEFAULT 1,
  ipb_expiry            TIMESTAMPTZ  NOT NULL,
  ipb_range_start       TEXT,
  ipb_range_end         TEXT,
  ipb_deleted           SMALLINT     NOT NULL  DEFAULT 0,
  ipb_block_email       SMALLINT     NOT NULL  DEFAULT 0,
  ipb_allow_usertalk    SMALLINT     NOT NULL  DEFAULT 0,
  ipb_parent_block_id   INTEGER          NULL            REFERENCES ipblocks(ipb_id) ON DELETE SET NULL DEFERRABLE INITIALLY DEFERRED,
  ipb_sitewide          SMALLINT     NOT NULL  DEFAULT 1
);
ALTER SEQUENCE ipblocks_ipb_id_seq OWNED BY ipblocks.ipb_id;
CREATE UNIQUE INDEX ipb_address_unique ON ipblocks (ipb_address,ipb_user,ipb_auto);
CREATE INDEX ipb_user    ON ipblocks (ipb_user);
CREATE INDEX ipb_range   ON ipblocks (ipb_range_start,ipb_range_end);
CREATE INDEX ipb_parent_block_id   ON ipblocks (ipb_parent_block_id);

CREATE TABLE image (
  img_name         TEXT      NOT NULL  PRIMARY KEY,
  img_size         INTEGER   NOT NULL,
  img_width        INTEGER   NOT NULL,
  img_height       INTEGER   NOT NULL,
  img_metadata     BYTEA     NOT NULL  DEFAULT '',
  img_bits         SMALLINT,
  img_media_type   TEXT,
  img_major_mime   TEXT                DEFAULT 'unknown',
  img_minor_mime   TEXT                DEFAULT 'unknown',
  img_description_id INTEGER NOT NULL,
  img_actor        INTEGER   NOT NULL,
  img_timestamp    TIMESTAMPTZ,
  img_sha1         TEXT      NOT NULL  DEFAULT ''
);
CREATE INDEX img_size_idx      ON image (img_size);
CREATE INDEX img_timestamp_idx ON image (img_timestamp);
CREATE INDEX img_sha1          ON image (img_sha1);

CREATE TABLE oldimage (
  oi_name          TEXT         NOT NULL,
  oi_archive_name  TEXT         NOT NULL,
  oi_size          INTEGER      NOT NULL,
  oi_width         INTEGER      NOT NULL,
  oi_height        INTEGER      NOT NULL,
  oi_bits          SMALLINT         NULL,
  oi_description_id INTEGER     NOT NULL,
  oi_actor         INTEGER      NOT NULL,
  oi_timestamp     TIMESTAMPTZ      NULL,
  oi_metadata      BYTEA        NOT NULL DEFAULT '',
  oi_media_type    TEXT             NULL,
  oi_major_mime    TEXT             NULL DEFAULT 'unknown',
  oi_minor_mime    TEXT             NULL DEFAULT 'unknown',
  oi_deleted       SMALLINT     NOT NULL DEFAULT 0,
  oi_sha1          TEXT         NOT NULL DEFAULT ''
);
ALTER TABLE oldimage ADD CONSTRAINT oldimage_oi_name_fkey_cascaded FOREIGN KEY (oi_name) REFERENCES image(img_name) ON DELETE CASCADE ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;
CREATE INDEX oi_name_timestamp    ON oldimage (oi_name,oi_timestamp);
CREATE INDEX oi_name_archive_name ON oldimage (oi_name,oi_archive_name);
CREATE INDEX oi_sha1              ON oldimage (oi_sha1);


CREATE SEQUENCE filearchive_fa_id_seq;
CREATE TABLE filearchive (
  fa_id                 INTEGER      NOT NULL  PRIMARY KEY DEFAULT nextval('filearchive_fa_id_seq'),
  fa_name               TEXT         NOT NULL,
  fa_archive_name       TEXT,
  fa_storage_group      TEXT,
  fa_storage_key        TEXT,
  fa_deleted_user       INTEGER          NULL  REFERENCES mwuser(user_id) ON DELETE SET NULL DEFERRABLE INITIALLY DEFERRED,
  fa_deleted_timestamp  TIMESTAMPTZ  NOT NULL,
  fa_deleted_reason_id  INTEGER      NOT NULL,
  fa_size               INTEGER      NOT NULL,
  fa_width              INTEGER      NOT NULL,
  fa_height             INTEGER      NOT NULL,
  fa_metadata           BYTEA        NOT NULL  DEFAULT '',
  fa_bits               SMALLINT,
  fa_media_type         TEXT,
  fa_major_mime         TEXT                   DEFAULT 'unknown',
  fa_minor_mime         TEXT                   DEFAULT 'unknown',
  fa_description_id     INTEGER      NOT NULL,
  fa_actor              INTEGER      NOT NULL,
  fa_timestamp          TIMESTAMPTZ,
  fa_deleted            SMALLINT     NOT NULL DEFAULT 0,
  fa_sha1               TEXT         NOT NULL DEFAULT ''
);
ALTER SEQUENCE filearchive_fa_id_seq OWNED BY filearchive.fa_id;
CREATE INDEX fa_name_time ON filearchive (fa_name, fa_timestamp);
CREATE INDEX fa_dupe      ON filearchive (fa_storage_group, fa_storage_key);
CREATE INDEX fa_notime    ON filearchive (fa_deleted_timestamp);
CREATE INDEX fa_nouser    ON filearchive (fa_deleted_user);
CREATE INDEX fa_sha1      ON filearchive (fa_sha1);

CREATE SEQUENCE uploadstash_us_id_seq;
CREATE TYPE media_type AS ENUM ('UNKNOWN','BITMAP','DRAWING','AUDIO','VIDEO','MULTIMEDIA','OFFICE','TEXT','EXECUTABLE','ARCHIVE','3D');
CREATE TABLE uploadstash (
  us_id           INTEGER PRIMARY KEY NOT NULL DEFAULT nextval('uploadstash_us_id_seq'),
  us_user         INTEGER,
  us_key          TEXT,
  us_orig_path    TEXT,
  us_path         TEXT,
  us_props        BYTEA,
  us_source_type  TEXT,
  us_timestamp    TIMESTAMPTZ,
  us_status       TEXT,
  us_chunk_inx    INTEGER NULL,
  us_size         INTEGER,
  us_sha1         TEXT,
  us_mime         TEXT,
  us_media_type   media_type DEFAULT NULL,
  us_image_width  INTEGER,
  us_image_height INTEGER,
  us_image_bits   SMALLINT
);
ALTER SEQUENCE uploadstash_us_id_seq OWNED BY uploadstash.us_id;

CREATE INDEX us_user_idx ON uploadstash (us_user);
CREATE UNIQUE INDEX us_key_idx ON uploadstash (us_key);
CREATE INDEX us_timestamp_idx ON uploadstash (us_timestamp);


CREATE SEQUENCE recentchanges_rc_id_seq;
CREATE TABLE recentchanges (
  rc_id              INTEGER      NOT NULL  PRIMARY KEY DEFAULT nextval('recentchanges_rc_id_seq'),
  rc_timestamp       TIMESTAMPTZ  NOT NULL,
  rc_actor           INTEGER      NOT NULL,
  rc_namespace       SMALLINT     NOT NULL,
  rc_title           TEXT         NOT NULL,
  rc_comment_id      INTEGER      NOT NULL,
  rc_minor           SMALLINT     NOT NULL  DEFAULT 0,
  rc_bot             SMALLINT     NOT NULL  DEFAULT 0,
  rc_new             SMALLINT     NOT NULL  DEFAULT 0,
  rc_cur_id          INTEGER          NULL,
  rc_this_oldid      INTEGER      NOT NULL,
  rc_last_oldid      INTEGER      NOT NULL,
  rc_type            SMALLINT     NOT NULL  DEFAULT 0,
  rc_source          TEXT         NOT NULL,
  rc_patrolled       SMALLINT     NOT NULL  DEFAULT 0,
  rc_ip              CIDR,
  rc_old_len         INTEGER,
  rc_new_len         INTEGER,
  rc_deleted         SMALLINT     NOT NULL  DEFAULT 0,
  rc_logid           INTEGER      NOT NULL  DEFAULT 0,
  rc_log_type        TEXT,
  rc_log_action      TEXT,
  rc_params          TEXT
);
ALTER SEQUENCE recentchanges_rc_id_seq OWNED BY recentchanges.rc_id;
CREATE INDEX rc_timestamp       ON recentchanges (rc_timestamp);
CREATE INDEX rc_timestamp_bot   ON recentchanges (rc_timestamp) WHERE rc_bot = 0;
CREATE INDEX rc_namespace_title_timestamp ON recentchanges (rc_namespace, rc_title, rc_timestamp);
CREATE INDEX rc_cur_id          ON recentchanges (rc_cur_id);
CREATE INDEX new_name_timestamp ON recentchanges (rc_new, rc_namespace, rc_timestamp);
CREATE INDEX rc_ip              ON recentchanges (rc_ip);
CREATE INDEX rc_name_type_patrolled_timestamp ON recentchanges (rc_namespace, rc_type, rc_patrolled, rc_timestamp);
CREATE INDEX rc_this_oldid      ON recentchanges (rc_this_oldid);


CREATE TABLE objectcache (
  keyname  TEXT                   UNIQUE,
  value    BYTEA        NOT NULL  DEFAULT '',
  exptime  TIMESTAMPTZ  NOT NULL
);
CREATE INDEX objectcacache_exptime ON objectcache (exptime);


CREATE SEQUENCE logging_log_id_seq;
CREATE TABLE logging (
  log_id          INTEGER      NOT NULL  PRIMARY KEY DEFAULT nextval('logging_log_id_seq'),
  log_type        TEXT         NOT NULL,
  log_action      TEXT         NOT NULL,
  log_timestamp   TIMESTAMPTZ  NOT NULL,
  log_actor       INTEGER      NOT NULL,
  log_namespace   SMALLINT     NOT NULL,
  log_title       TEXT         NOT NULL,
  log_comment_id  INTEGER      NOT NULL,
  log_params      TEXT,
  log_deleted     SMALLINT     NOT NULL DEFAULT 0,
  log_page        INTEGER
);
ALTER SEQUENCE logging_log_id_seq OWNED BY logging.log_id;
CREATE INDEX logging_type_name ON logging (log_type, log_timestamp);
CREATE INDEX logging_actor_time_backwards ON logging (log_timestamp, log_actor);
CREATE INDEX logging_page_time ON logging (log_namespace, log_title, log_timestamp);
CREATE INDEX logging_times ON logging (log_timestamp);
CREATE INDEX logging_actor_type_time ON logging (log_actor, log_type, log_timestamp);
CREATE INDEX logging_page_id_time ON logging (log_page, log_timestamp);
CREATE INDEX logging_actor_time ON logging (log_actor, log_timestamp);
CREATE INDEX logging_type_action ON logging (log_type, log_action, log_timestamp);

-- Tsearch2 2 stuff. Will fail if we don't have proper access to the tsearch2 tables
-- Make sure you also change patch-tsearch2funcs.sql if the funcs below change.

ALTER TABLE page ADD titlevector tsvector;
CREATE FUNCTION ts2_page_title() RETURNS TRIGGER LANGUAGE plpgsql AS
$mw$
BEGIN
IF TG_OP = 'INSERT' THEN
  NEW.titlevector = to_tsvector(REPLACE(NEW.page_title,'/',' '));
ELSIF NEW.page_title != OLD.page_title THEN
  NEW.titlevector := to_tsvector(REPLACE(NEW.page_title,'/',' '));
END IF;
RETURN NEW;
END;
$mw$;

CREATE TRIGGER ts2_page_title BEFORE INSERT OR UPDATE ON page
  FOR EACH ROW EXECUTE PROCEDURE ts2_page_title();


ALTER TABLE pagecontent ADD textvector tsvector;
CREATE FUNCTION ts2_page_text() RETURNS TRIGGER LANGUAGE plpgsql AS
$mw$
BEGIN
IF TG_OP = 'INSERT' THEN
  NEW.textvector = to_tsvector(NEW.old_text);
ELSIF NEW.old_text != OLD.old_text THEN
  NEW.textvector := to_tsvector(NEW.old_text);
END IF;
RETURN NEW;
END;
$mw$;

CREATE TRIGGER ts2_page_text BEFORE INSERT OR UPDATE ON pagecontent
  FOR EACH ROW EXECUTE PROCEDURE ts2_page_text();

CREATE INDEX ts2_page_title ON page USING gin(titlevector);
CREATE INDEX ts2_page_text ON pagecontent USING gin(textvector);
