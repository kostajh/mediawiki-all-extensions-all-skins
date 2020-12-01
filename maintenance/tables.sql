-- SQL to create the initial tables for the MediaWiki database.
-- This is read and executed by the install script; you should
-- not have to run it by itself unless doing a manual install.

-- This is a shared schema file used for both MySQL and SQLite installs.
--
-- For more documentation on the database schema, see
-- https://www.mediawiki.org/wiki/Manual:Database_layout
--
-- General notes:
--
-- If possible, create tables as InnoDB to benefit from the
-- superior resiliency against crashes and ability to read
-- during writes (and write during reads!)
--
-- Only the 'searchindex' table requires MyISAM due to the
-- requirement for fulltext index support, which is missing
-- from InnoDB.
--
--
-- The MySQL table backend for MediaWiki currently uses
-- 14-character BINARY or VARBINARY fields to store timestamps.
-- The format is YYYYMMDDHHMMSS, which is derived from the
-- text format of MySQL's TIMESTAMP fields.
--
-- Historically TIMESTAMP fields were used, but abandoned
-- in early 2002 after a lot of trouble with the fields
-- auto-updating.
--
-- The Postgres backend uses TIMESTAMPTZ fields for timestamps,
-- and we will migrate the MySQL definitions at some point as
-- well.
--
--
-- The /*_*/ comments in this and other files are
-- replaced with the defined table prefix by the installer
-- and updater scripts. If you are installing or running
-- updates manually, you will need to manually insert the
-- table prefix if any when running these scripts.
--


--
-- The user table contains basic account information,
-- authentication keys, etc.
--
-- Some multi-wiki sites may share a single central user table
-- between separate wikis using the $wgSharedDB setting.
--
-- Note that when a external authentication plugin is used,
-- user table entries still need to be created to store
-- preferences and to key tracking information in the other
-- tables.
--
CREATE TABLE /*_*/user (
  user_id int unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,

  -- Usernames must be unique, must not be in the form of
  -- an IP address. _Shouldn't_ allow slashes or case
  -- conflicts. Spaces are allowed, and are _not_ converted
  -- to underscores like titles. See the User::newFromName() for
  -- the specific tests that usernames have to pass.
  user_name varchar(255) binary NOT NULL default '',

  -- Optional 'real name' to be displayed in credit listings
  user_real_name varchar(255) binary NOT NULL default '',

  -- Password hashes, see User::crypt() and User::comparePasswords()
  -- in User.php for the algorithm
  user_password tinyblob NOT NULL,

  -- When using 'mail me a new password', a random
  -- password is generated and the hash stored here.
  -- The previous password is left in place until
  -- someone actually logs in with the new password,
  -- at which point the hash is moved to user_password
  -- and the old password is invalidated.
  user_newpassword tinyblob NOT NULL,

  -- Timestamp of the last time when a new password was
  -- sent, for throttling and expiring purposes
  -- Emailed passwords will expire $wgNewPasswordExpiry
  -- (a week) after being set. If user_newpass_time is NULL
  -- (eg. created by mail) it doesn't expire.
  user_newpass_time binary(14),

  -- Note: email should be restricted, not public info.
  -- Same with passwords.
  user_email tinytext NOT NULL,

  -- If the browser sends an If-Modified-Since header, a 304 response is
  -- suppressed if the value in this field for the current user is later than
  -- the value in the IMS header. That is, this field is an invalidation timestamp
  -- for the browser cache of logged-in users. Among other things, it is used
  -- to prevent pages generated for a previously logged in user from being
  -- displayed after a session expiry followed by a fresh login.
  user_touched binary(14) NOT NULL default '',

  -- A pseudorandomly generated value that is stored in
  -- a cookie when the "remember password" feature is
  -- used (previously, a hash of the password was used, but
  -- this was vulnerable to cookie-stealing attacks)
  user_token binary(32) NOT NULL default '',

  -- Initially NULL; when a user's e-mail address has been
  -- validated by returning with a mailed token, this is
  -- set to the current timestamp.
  user_email_authenticated binary(14),

  -- Randomly generated token created when the e-mail address
  -- is set and a confirmation test mail sent.
  user_email_token binary(32),

  -- Expiration date for the user_email_token
  user_email_token_expires binary(14),

  -- Timestamp of account registration.
  -- Accounts predating this schema addition may contain NULL.
  user_registration binary(14),

  -- Count of edits and edit-like actions.
  --
  -- *NOT* intended to be an accurate copy of COUNT(*) WHERE rev_actor refers to a user's actor_id
  -- May contain NULL for old accounts if batch-update scripts haven't been
  -- run, as well as listing deleted edits and other myriad ways it could be
  -- out of sync.
  --
  -- Meant primarily for heuristic checks to give an impression of whether
  -- the account has been used much.
  --
  user_editcount int,

  -- Expiration date for user password.
  user_password_expires varbinary(14) DEFAULT NULL

) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/user_name ON /*_*/user (user_name);
CREATE INDEX /*i*/user_email_token ON /*_*/user (user_email_token);
CREATE INDEX /*i*/user_email ON /*_*/user (user_email(50));

--
-- Core of the wiki: each page has an entry here which identifies
-- it by title and contains some essential metadata.
--
CREATE TABLE /*_*/page (
  -- Unique identifier number. The page_id will be preserved across
  -- edits and rename operations, but not deletions and recreations.
  page_id int unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,

  -- A page name is broken into a namespace and a title.
  -- The namespace keys are UI-language-independent constants,
  -- defined in includes/Defines.php
  page_namespace int NOT NULL,

  -- The rest of the title, as text.
  -- Spaces are transformed into underscores in title storage.
  page_title varchar(255) binary NOT NULL,

  -- Comma-separated set of permission keys indicating who
  -- can move or edit the page.
  page_restrictions tinyblob NULL,

  -- 1 indicates the article is a redirect.
  page_is_redirect tinyint unsigned NOT NULL default 0,

  -- 1 indicates this is a new entry, with only one edit.
  -- Not all pages with one edit are new pages.
  page_is_new tinyint unsigned NOT NULL default 0,

  -- Random value between 0 and 1, used for Special:Randompage
  page_random real unsigned NOT NULL,

  -- This timestamp is updated whenever the page changes in
  -- a way requiring it to be re-rendered, invalidating caches.
  -- Aside from editing this includes permission changes,
  -- creation or deletion of linked pages, and alteration
  -- of contained templates.
  page_touched binary(14) NOT NULL default '',

  -- This timestamp is updated whenever a page is re-parsed and
  -- it has all the link tracking tables updated for it. This is
  -- useful for de-duplicating expensive backlink update jobs.
  page_links_updated varbinary(14) NULL default NULL,

  -- Handy key to revision.rev_id of the current revision.
  -- This may be 0 during page creation, but that shouldn't
  -- happen outside of a transaction... hopefully.
  page_latest int unsigned NOT NULL,

  -- Uncompressed length in bytes of the page's current source text.
  page_len int unsigned NOT NULL,

  -- content model, see CONTENT_MODEL_XXX constants
  page_content_model varbinary(32) DEFAULT NULL,

  -- Page content language
  page_lang varbinary(35) DEFAULT NULL
) /*$wgDBTableOptions*/;

-- The title index. Care must be taken to always specify a namespace when
-- by title, so that the index is used. Even listing all known namespaces
-- with IN() is better than omitting page_namespace from the WHERE clause.
CREATE UNIQUE INDEX /*i*/name_title ON /*_*/page (page_namespace,page_title);

-- The index for Special:Random
CREATE INDEX /*i*/page_random ON /*_*/page (page_random);

-- Questionable utility, used by ProofreadPage, possibly DynamicPageList.
-- ApiQueryAllPages unconditionally filters on namespace and so hopefully does
-- not use it.
CREATE INDEX /*i*/page_len ON /*_*/page (page_len);

-- The index for Special:Shortpages and Special:Longpages. Also SiteStats::articles()
-- in 'comma' counting mode, MessageCache::loadFromDB().
CREATE INDEX /*i*/page_redirect_namespace_len ON /*_*/page (page_is_redirect, page_namespace, page_len);

--
-- Every edit of a page creates also a revision row.
-- This stores metadata about the revision, and a reference
-- to the text storage backend.
--
CREATE TABLE /*_*/revision (
  -- Unique ID to identify each revision
  rev_id int unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,

  -- Key to page_id. This should _never_ be invalid.
  rev_page int unsigned NOT NULL,

  -- Key to comment.comment_id. Comment summarizing the change.
  rev_comment_id bigint unsigned NOT NULL default 0,

  -- Key to actor.actor_id of the user or IP who made this edit.
  rev_actor bigint unsigned NOT NULL default 0,

  -- Timestamp of when revision was created
  rev_timestamp binary(14) NOT NULL default '',

  -- Records whether the user marked the 'minor edit' checkbox.
  -- Many automated edits are marked as minor.
  rev_minor_edit tinyint unsigned NOT NULL default 0,

  -- Restrictions on who can access this revision
  rev_deleted tinyint unsigned NOT NULL default 0,

  -- Length of this revision in bytes
  rev_len int unsigned,

  -- Key to revision.rev_id
  -- This field is used to add support for a tree structure (The Adjacency List Model)
  rev_parent_id int unsigned default NULL,

  -- SHA-1 text content hash in base-36
  rev_sha1 varbinary(32) NOT NULL default ''
) /*$wgDBTableOptions*/ MAX_ROWS=10000000 AVG_ROW_LENGTH=1024;
-- In case tables are created as MyISAM, use row hints for MySQL <5.0 to avoid 4GB limit

-- The index is proposed for removal, do not use it in new code: T163532.
-- Used for ordering revisions within a page by rev_id, which is usually
-- incorrect, since rev_timestamp is normally the correct order. It can also
-- be used by dumpBackup.php, if a page and rev_id range is specified.
CREATE INDEX /*i*/rev_page_id ON /*_*/revision (rev_page, rev_id);

-- Used by ApiQueryAllRevisions
CREATE INDEX /*i*/rev_timestamp ON /*_*/revision (rev_timestamp);

-- History index
CREATE INDEX /*i*/page_timestamp ON /*_*/revision (rev_page,rev_timestamp);

-- User contributions index
CREATE INDEX /*i*/rev_actor_timestamp ON /*_*/revision (rev_actor,rev_timestamp,rev_id);

-- Credits index. This is scanned in order to compile credits lists for pages,
-- in ApiQueryContributors. Also for ApiQueryRevisions if rvuser is specified.
CREATE INDEX /*i*/rev_page_actor_timestamp ON /*_*/revision (rev_page,rev_actor,rev_timestamp);

-- Holds text of individual page revisions.
--
-- Field names are a holdover from the 'old' revisions table in
-- MediaWiki 1.4 and earlier: an upgrade will transform that
-- table into the 'text' table to minimize unnecessary churning
-- and downtime. If upgrading, the other fields will be left unused.
--
CREATE TABLE /*_*/text (
  -- Unique text storage key number.
  -- Note that the 'oldid' parameter used in URLs does *not*
  -- refer to this number anymore, but to rev_id.
  --
  -- content.content_address refers to this column
  old_id int unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,

  -- Depending on the contents of the old_flags field, the text
  -- may be convenient plain text, or it may be funkily encoded.
  old_text mediumblob NOT NULL,

  -- Comma-separated list of flags:
  -- gzip: text is compressed with PHP's gzdeflate() function.
  -- utf-8: text was stored as UTF-8.
  --        If $wgLegacyEncoding option is on, rows *without* this flag
  --        will be converted to UTF-8 transparently at load time. Note
  --        that due to a bug in a maintenance script, this flag may
  --        have been stored as 'utf8' in some cases (T18841).
  -- object: text field contained a serialized PHP object.
  --         The object either contains multiple versions compressed
  --         together to achieve a better compression ratio, or it refers
  --         to another row where the text can be found.
  -- external: text was stored in an external location specified by old_text.
  --           Any additional flags apply to the data stored at that URL, not
  --           the URL itself. The 'object' flag is *not* set for URLs of the
  --           form 'DB://cluster/id/itemid', because the external storage
  --           system itself decompresses these.
  old_flags tinyblob NOT NULL
) /*$wgDBTableOptions*/ MAX_ROWS=10000000 AVG_ROW_LENGTH=10240;
-- In case tables are created as MyISAM, use row hints for MySQL <5.0 to avoid 4GB limit

--
-- Archive area for deleted pages and their revisions.
-- These may be viewed (and restored) by admins through the Special:Undelete interface.
--
CREATE TABLE /*_*/archive (
  -- Primary key
  ar_id int unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,

  -- Copied from page_namespace
  ar_namespace int NOT NULL default 0,
  -- Copied from page_title
  ar_title varchar(255) binary NOT NULL default '',

  -- Basic revision stuff...
  ar_comment_id bigint unsigned NOT NULL,
  ar_actor bigint unsigned NOT NULL,
  ar_timestamp binary(14) NOT NULL default '',
  ar_minor_edit tinyint NOT NULL default 0,

  -- Copied from rev_id.
  --
  -- @since 1.5 Entries from 1.4 will be NULL here. When restoring
  -- archive rows from before 1.5, a new rev_id is created.
  ar_rev_id int unsigned NOT NULL,

  -- Copied from rev_deleted. Although this may be raised during deletion.
  -- Users with the "suppressrevision" right may "archive" and "suppress"
  -- content in a single action.
  -- @since 1.10
  ar_deleted tinyint unsigned NOT NULL default 0,

  -- Copied from rev_len, length of this revision in bytes.
  -- @since 1.10
  ar_len int unsigned,

  -- Copied from page_id. Restoration will attempt to use this as page ID if
  -- no current page with the same name exists. Otherwise, the revisions will
  -- be restored under the current page. Can be used for manual undeletion by
  -- developers if multiple pages by the same name were archived.
  --
  -- @since 1.11 Older entries will have NULL.
  ar_page_id int unsigned,

  -- Copied from rev_parent_id.
  -- @since 1.13
  ar_parent_id int unsigned default NULL,

  -- Copied from rev_sha1, SHA-1 text content hash in base-36
  -- @since 1.19
  ar_sha1 varbinary(32) NOT NULL default ''
) /*$wgDBTableOptions*/;

-- Index for Special:Undelete to page through deleted revisions
CREATE INDEX /*i*/name_title_timestamp ON /*_*/archive (ar_namespace,ar_title,ar_timestamp);

-- Index for Special:DeletedContributions
CREATE INDEX /*i*/ar_actor_timestamp ON /*_*/archive (ar_actor,ar_timestamp);

-- Index for linking archive rows with tables that normally link with revision
-- rows, such as change_tag.
CREATE UNIQUE INDEX /*i*/ar_revid_uniq ON /*_*/archive (ar_rev_id);



--
-- Track category inclusions *used inline*
-- This tracks a single level of category membership
--
CREATE TABLE /*_*/categorylinks (
  -- Key to page_id of the page defined as a category member.
  cl_from int unsigned NOT NULL default 0,

  -- Name of the category.
  -- This is also the page_title of the category's description page;
  -- all such pages are in namespace 14 (NS_CATEGORY).
  cl_to varchar(255) binary NOT NULL default '',

  -- A binary string obtained by applying a sortkey generation algorithm
  -- (Collation::getSortKey()) to page_title, or cl_sortkey_prefix . "\n"
  -- . page_title if cl_sortkey_prefix is nonempty.
  cl_sortkey varbinary(230) NOT NULL default '',

  -- A prefix for the raw sortkey manually specified by the user, either via
  -- [[Category:Foo|prefix]] or {{defaultsort:prefix}}.  If nonempty, it's
  -- concatenated with a line break followed by the page title before the sortkey
  -- conversion algorithm is run.  We store this so that we can update
  -- collations without reparsing all pages.
  -- Note: If you change the length of this field, you also need to change
  -- code in LinksUpdate.php. See T27254.
  cl_sortkey_prefix varchar(255) binary NOT NULL default '',

  -- This isn't really used at present. Provided for an optional
  -- sorting method by approximate addition time.
  cl_timestamp timestamp NOT NULL,

  -- Stores $wgCategoryCollation at the time cl_sortkey was generated.  This
  -- can be used to install new collation versions, tracking which rows are not
  -- yet updated.  '' means no collation, this is a legacy row that needs to be
  -- updated by updateCollation.php.  In the future, it might be possible to
  -- specify different collations per category.
  cl_collation varbinary(32) NOT NULL default '',

  -- Stores whether cl_from is a category, file, or other page, so we can
  -- paginate the three categories separately.  This only has to be updated
  -- when moving pages into or out of the category namespace, since file pages
  -- cannot be moved to other namespaces, nor can non-files be moved into the
  -- file namespace.
  cl_type ENUM('page', 'subcat', 'file') NOT NULL default 'page',
  PRIMARY KEY (cl_from,cl_to)
) /*$wgDBTableOptions*/;


-- We always sort within a given category, and within a given type.  FIXME:
-- Formerly this index didn't cover cl_type (since that didn't exist), so old
-- callers won't be using an index: fix this?
CREATE INDEX /*i*/cl_sortkey ON /*_*/categorylinks (cl_to,cl_type,cl_sortkey,cl_from);

-- Used by the API (and some extensions)
CREATE INDEX /*i*/cl_timestamp ON /*_*/categorylinks (cl_to,cl_timestamp);

-- Used when updating collation (e.g. updateCollation.php)
CREATE INDEX /*i*/cl_collation_ext ON /*_*/categorylinks (cl_collation, cl_to, cl_type, cl_from);


--
-- Blocks against user accounts, IP addresses and IP ranges.
--
CREATE TABLE /*_*/ipblocks (
  -- Primary key, introduced for privacy.
  ipb_id int NOT NULL PRIMARY KEY AUTO_INCREMENT,

  -- Blocked IP address in dotted-quad form or user name.
  ipb_address tinyblob NOT NULL,

  -- Blocked user ID or 0 for IP blocks.
  ipb_user int unsigned NOT NULL default 0,

  -- Actor who made the block.
  ipb_by_actor bigint unsigned NOT NULL,

  -- Key to comment_id. Text comment made by blocker.
  ipb_reason_id bigint unsigned NOT NULL,

  -- Creation (or refresh) date in standard YMDHMS form.
  -- IP blocks expire automatically.
  ipb_timestamp binary(14) NOT NULL default '',

  -- Indicates that the IP address was banned because a banned
  -- user accessed a page through it. If this is 1, ipb_address
  -- will be hidden, and the block identified by block ID number.
  ipb_auto bool NOT NULL default 0,

  -- If set to 1, block applies only to logged-out users
  ipb_anon_only bool NOT NULL default 0,

  -- Block prevents account creation from matching IP addresses
  ipb_create_account bool NOT NULL default 1,

  -- Block triggers autoblocks
  ipb_enable_autoblock bool NOT NULL default '1',

  -- Time at which the block will expire.
  -- May be "infinity"
  ipb_expiry varbinary(14) NOT NULL default '',

  -- Start and end of an address range, in hexadecimal
  -- Size chosen to allow IPv6
  -- FIXME: these fields were originally blank for single-IP blocks,
  -- but now they are populated. No migration was ever done. They
  -- should be fixed to be blank again for such blocks (T51504).
  ipb_range_start tinyblob NOT NULL,
  ipb_range_end tinyblob NOT NULL,

  -- Flag for entries hidden from users and Sysops
  ipb_deleted bool NOT NULL default 0,

  -- Block prevents user from accessing Special:Emailuser
  ipb_block_email bool NOT NULL default 0,

  -- Block allows user to edit their own talk page
  ipb_allow_usertalk bool NOT NULL default 0,

  -- ID of the block that caused this block to exist
  -- Autoblocks set this to the original block
  -- so that the original block being deleted also
  -- deletes the autoblocks
  ipb_parent_block_id int default NULL,

  -- Block user from editing any page on the site (other than their own user
  -- talk page).
  ipb_sitewide bool NOT NULL default 1

) /*$wgDBTableOptions*/;

-- Unique index to support "user already blocked" messages
-- Any new options which prevent collisions should be included
CREATE UNIQUE INDEX /*i*/ipb_address_unique ON /*_*/ipblocks (ipb_address(255), ipb_user, ipb_auto);

-- For querying whether a logged-in user is blocked
CREATE INDEX /*i*/ipb_user ON /*_*/ipblocks (ipb_user);

-- For querying whether an IP address is in any range
CREATE INDEX /*i*/ipb_range ON /*_*/ipblocks (ipb_range_start(8), ipb_range_end(8));

-- Index for Special:BlockList
CREATE INDEX /*i*/ipb_timestamp ON /*_*/ipblocks (ipb_timestamp);

-- Index for table pruning
CREATE INDEX /*i*/ipb_expiry ON /*_*/ipblocks (ipb_expiry);

-- Index for removing autoblocks when a parent block is removed
CREATE INDEX /*i*/ipb_parent_block_id ON /*_*/ipblocks (ipb_parent_block_id);


--
-- Uploaded images and other files.
--
CREATE TABLE /*_*/image (
  -- Filename.
  -- This is also the title of the associated description page,
  -- which will be in namespace 6 (NS_FILE).
  img_name varchar(255) binary NOT NULL default '' PRIMARY KEY,

  -- File size in bytes.
  img_size int unsigned NOT NULL default 0,

  -- For images, size in pixels.
  img_width int NOT NULL default 0,
  img_height int NOT NULL default 0,

  -- Extracted Exif metadata stored as a serialized PHP array.
  img_metadata mediumblob NOT NULL,

  -- For images, bits per pixel if known.
  img_bits int NOT NULL default 0,

  -- Media type as defined by the MEDIATYPE_xxx constants
  img_media_type ENUM("UNKNOWN", "BITMAP", "DRAWING", "AUDIO", "VIDEO", "MULTIMEDIA", "OFFICE", "TEXT", "EXECUTABLE", "ARCHIVE", "3D") default NULL,

  -- major part of a MIME media type as defined by IANA
  -- see https://www.iana.org/assignments/media-types/
  -- for "chemical" cf. http://dx.doi.org/10.1021/ci9803233 by the ACS
  img_major_mime ENUM("unknown", "application", "audio", "image", "text", "video", "message", "model", "multipart", "chemical") NOT NULL default "unknown",

  -- minor part of a MIME media type as defined by IANA
  -- the minor parts are not required to adher to any standard
  -- but should be consistent throughout the database
  -- see https://www.iana.org/assignments/media-types/
  img_minor_mime varbinary(100) NOT NULL default "unknown",

  -- Foreign key to comment table, which contains the description field as entered by the uploader.
  -- This is displayed in image upload history and logs.
  img_description_id bigint unsigned NOT NULL,

  -- actor_id of the uploader.
  img_actor bigint unsigned NOT NULL,

  -- Time of the upload.
  img_timestamp varbinary(14) NOT NULL default '',

  -- SHA-1 content hash in base-36
  img_sha1 varbinary(32) NOT NULL default ''
) /*$wgDBTableOptions*/;

-- Used by Special:Newimages and ApiQueryAllImages
CREATE INDEX /*i*/img_actor_timestamp ON /*_*/image (img_actor,img_timestamp);
-- Used by Special:ListFiles for sort-by-size
CREATE INDEX /*i*/img_size ON /*_*/image (img_size);
-- Used by Special:Newimages and Special:ListFiles
CREATE INDEX /*i*/img_timestamp ON /*_*/image (img_timestamp);
-- Used in API and duplicate search
CREATE INDEX /*i*/img_sha1 ON /*_*/image (img_sha1(10));
-- Used to get media of one type
CREATE INDEX /*i*/img_media_mime ON /*_*/image (img_media_type,img_major_mime,img_minor_mime);


--
-- Previous revisions of uploaded files.
-- Awkwardly, image rows have to be moved into
-- this table at re-upload time.
--
CREATE TABLE /*_*/oldimage (
  -- Base filename: key to image.img_name
  oi_name varchar(255) binary NOT NULL default '',

  -- Filename of the archived file.
  -- This is generally a timestamp and '!' prepended to the base name.
  oi_archive_name varchar(255) binary NOT NULL default '',

  -- Other fields as in image...
  oi_size int unsigned NOT NULL default 0,
  oi_width int NOT NULL default 0,
  oi_height int NOT NULL default 0,
  oi_bits int NOT NULL default 0,
  oi_description_id bigint unsigned NOT NULL,
  oi_actor bigint unsigned NOT NULL,
  oi_timestamp binary(14) NOT NULL default '',

  oi_metadata mediumblob NOT NULL,
  oi_media_type ENUM("UNKNOWN", "BITMAP", "DRAWING", "AUDIO", "VIDEO", "MULTIMEDIA", "OFFICE", "TEXT", "EXECUTABLE", "ARCHIVE", "3D") default NULL,
  oi_major_mime ENUM("unknown", "application", "audio", "image", "text", "video", "message", "model", "multipart", "chemical") NOT NULL default "unknown",
  oi_minor_mime varbinary(100) NOT NULL default "unknown",
  oi_deleted tinyint unsigned NOT NULL default 0,
  oi_sha1 varbinary(32) NOT NULL default ''
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/oi_actor_timestamp ON /*_*/oldimage (oi_actor,oi_timestamp);
CREATE INDEX /*i*/oi_name_timestamp ON /*_*/oldimage (oi_name,oi_timestamp);
-- oi_archive_name truncated to 14 to avoid key length overflow
CREATE INDEX /*i*/oi_name_archive_name ON /*_*/oldimage (oi_name,oi_archive_name(14));
CREATE INDEX /*i*/oi_sha1 ON /*_*/oldimage (oi_sha1(10));


--
-- Record of deleted file data
--
CREATE TABLE /*_*/filearchive (
  -- Unique row id
  fa_id int NOT NULL PRIMARY KEY AUTO_INCREMENT,

  -- Original base filename; key to image.img_name, page.page_title, etc
  fa_name varchar(255) binary NOT NULL default '',

  -- Filename of archived file, if an old revision
  fa_archive_name varchar(255) binary default '',

  -- Which storage bin (directory tree or object store) the file data
  -- is stored in. Should be 'deleted' for files that have been deleted;
  -- any other bin is not yet in use.
  fa_storage_group varbinary(16),

  -- SHA-1 of the file contents plus extension, used as a key for storage.
  -- eg 8f8a562add37052a1848ff7771a2c515db94baa9.jpg
  --
  -- If NULL, the file was missing at deletion time or has been purged
  -- from the archival storage.
  fa_storage_key varbinary(64) default '',

  -- Deletion information, if this file is deleted.
  fa_deleted_user int,
  fa_deleted_timestamp binary(14) default '',
  fa_deleted_reason_id bigint unsigned NOT NULL,

  -- Duped fields from image
  fa_size int unsigned default 0,
  fa_width int default 0,
  fa_height int default 0,
  fa_metadata mediumblob,
  fa_bits int default 0,
  fa_media_type ENUM("UNKNOWN", "BITMAP", "DRAWING", "AUDIO", "VIDEO", "MULTIMEDIA", "OFFICE", "TEXT", "EXECUTABLE", "ARCHIVE", "3D") default NULL,
  fa_major_mime ENUM("unknown", "application", "audio", "image", "text", "video", "message", "model", "multipart", "chemical") default "unknown",
  fa_minor_mime varbinary(100) default "unknown",
  fa_description_id bigint unsigned NOT NULL,
  fa_actor bigint unsigned NOT NULL,
  fa_timestamp binary(14) default '',

  -- Visibility of deleted revisions, bitfield
  fa_deleted tinyint unsigned NOT NULL default 0,

  -- sha1 hash of file content
  fa_sha1 varbinary(32) NOT NULL default ''
) /*$wgDBTableOptions*/;

-- pick out by image name
CREATE INDEX /*i*/fa_name ON /*_*/filearchive (fa_name, fa_timestamp);
-- pick out dupe files
CREATE INDEX /*i*/fa_storage_group ON /*_*/filearchive (fa_storage_group, fa_storage_key);
-- sort by deletion time
CREATE INDEX /*i*/fa_deleted_timestamp ON /*_*/filearchive (fa_deleted_timestamp);
-- sort by uploader
CREATE INDEX /*i*/fa_actor_timestamp ON /*_*/filearchive (fa_actor,fa_timestamp);
-- find file by sha1, 10 bytes will be enough for hashes to be indexed
CREATE INDEX /*i*/fa_sha1 ON /*_*/filearchive (fa_sha1(10));


--
-- Store information about newly uploaded files before they're
-- moved into the actual filestore
--
CREATE TABLE /*_*/uploadstash (
  us_id int unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,

  -- the user who uploaded the file.
  us_user int unsigned NOT NULL,

  -- file key. this is how applications actually search for the file.
  -- this might go away, or become the primary key.
  us_key varchar(255) NOT NULL,

  -- the original path
  us_orig_path varchar(255) NOT NULL,

  -- the temporary path at which the file is actually stored
  us_path varchar(255) NOT NULL,

  -- which type of upload the file came from (sometimes)
  us_source_type varchar(50),

  -- the date/time on which the file was added
  us_timestamp varbinary(14) NOT NULL,

  us_status varchar(50) NOT NULL,

  -- chunk counter starts at 0, current offset is stored in us_size
  us_chunk_inx int unsigned NULL,

  -- Serialized file properties from FSFile::getProps()
  us_props blob,

  -- file size in bytes
  us_size int unsigned NOT NULL,
  -- this hash comes from FSFile::getSha1Base36(), and is 31 characters
  us_sha1 varchar(31) NOT NULL,
  us_mime varchar(255),
  -- Media type as defined by the MEDIATYPE_xxx constants, should duplicate definition in the image table
  us_media_type ENUM("UNKNOWN", "BITMAP", "DRAWING", "AUDIO", "VIDEO", "MULTIMEDIA", "OFFICE", "TEXT", "EXECUTABLE", "ARCHIVE", "3D") default NULL,
  -- image-specific properties
  us_image_width int unsigned,
  us_image_height int unsigned,
  us_image_bits smallint unsigned

) /*$wgDBTableOptions*/;

-- sometimes there's a delete for all of a user's stuff.
CREATE INDEX /*i*/us_user ON /*_*/uploadstash (us_user);
-- pick out files by key, enforce key uniqueness
CREATE UNIQUE INDEX /*i*/us_key ON /*_*/uploadstash (us_key);
-- the abandoned upload cleanup script needs this
CREATE INDEX /*i*/us_timestamp ON /*_*/uploadstash (us_timestamp);


--
-- Primarily a summary table for Special:Recentchanges,
-- this table contains some additional info on edits from
-- the last few days, see Article::editUpdates()
--
CREATE TABLE /*_*/recentchanges (
  rc_id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
  rc_timestamp varbinary(14) NOT NULL default '',

  -- As in revision
  rc_actor bigint unsigned NOT NULL,

  -- When pages are renamed, their RC entries do _not_ change.
  rc_namespace int NOT NULL default 0,
  rc_title varchar(255) binary NOT NULL default '',

  -- as in revision...
  rc_comment_id bigint unsigned NOT NULL,
  rc_minor tinyint unsigned NOT NULL default 0,

  -- Edits by user accounts with the 'bot' rights key are
  -- marked with a 1 here, and will be hidden from the
  -- default view.
  rc_bot tinyint unsigned NOT NULL default 0,

  -- Set if this change corresponds to a page creation
  rc_new tinyint unsigned NOT NULL default 0,

  -- Key to page_id (was cur_id prior to 1.5).
  -- This will keep links working after moves while
  -- retaining the at-the-time name in the changes list.
  rc_cur_id int unsigned NOT NULL default 0,

  -- rev_id of the given revision
  rc_this_oldid int unsigned NOT NULL default 0,

  -- rev_id of the prior revision, for generating diff links.
  rc_last_oldid int unsigned NOT NULL default 0,

  -- The type of change entry (RC_EDIT,RC_NEW,RC_LOG,RC_EXTERNAL)
  rc_type tinyint unsigned NOT NULL default 0,

  -- The source of the change entry (replaces rc_type)
  -- default of '' is temporary, needed for initial migration
  rc_source varchar(16) binary not null default '',

  -- If the Recent Changes Patrol option is enabled,
  -- users may mark edits as having been reviewed to
  -- remove a warning flag on the RC list.
  -- A value of 1 indicates the page has been reviewed.
  rc_patrolled tinyint unsigned NOT NULL default 0,

  -- Recorded IP address the edit was made from, if the
  -- $wgPutIPinRC option is enabled.
  rc_ip varbinary(40) NOT NULL default '',

  -- Text length in characters before
  -- and after the edit
  rc_old_len int,
  rc_new_len int,

  -- Visibility of recent changes items, bitfield
  rc_deleted tinyint unsigned NOT NULL default 0,

  -- Value corresponding to log_id, specific log entries
  rc_logid int unsigned NOT NULL default 0,
  -- Store log type info here, or null
  rc_log_type varbinary(255) NULL default NULL,
  -- Store log action or null
  rc_log_action varbinary(255) NULL default NULL,
  -- Log params
  rc_params blob NULL
) /*$wgDBTableOptions*/;

-- Special:Recentchanges
CREATE INDEX /*i*/rc_timestamp ON /*_*/recentchanges (rc_timestamp);

-- Special:Watchlist
CREATE INDEX /*i*/rc_namespace_title_timestamp ON /*_*/recentchanges (rc_namespace, rc_title, rc_timestamp);

-- Special:Recentchangeslinked when finding changes in pages linked from a page
CREATE INDEX /*i*/rc_cur_id ON /*_*/recentchanges (rc_cur_id);

-- Special:Newpages
CREATE INDEX /*i*/new_name_timestamp ON /*_*/recentchanges (rc_new,rc_namespace,rc_timestamp);

-- Blank unless $wgPutIPinRC=true (false at WMF), possibly used by extensions,
-- but mostly replaced by CheckUser.
CREATE INDEX /*i*/rc_ip ON /*_*/recentchanges (rc_ip);

-- Probably intended for Special:NewPages namespace filter
CREATE INDEX /*i*/rc_ns_actor ON /*_*/recentchanges (rc_namespace, rc_actor);

-- SiteStats active user count, Special:ActiveUsers, Special:NewPages user filter
CREATE INDEX /*i*/rc_actor ON /*_*/recentchanges (rc_actor, rc_timestamp);

-- ApiQueryRecentChanges (T140108)
CREATE INDEX /*i*/rc_name_type_patrolled_timestamp ON /*_*/recentchanges (rc_namespace, rc_type, rc_patrolled, rc_timestamp);

-- Article.php and friends (T139012)
CREATE INDEX /*i*/rc_this_oldid ON /*_*/recentchanges (rc_this_oldid);


--
-- When using the default MySQL search backend, page titles
-- and text are munged to strip markup, do Unicode case folding,
-- and prepare the result for MySQL's fulltext index.
--
-- This table must be MyISAM; InnoDB does not support the needed
-- fulltext index.
--
CREATE TABLE /*_*/searchindex (
  -- Key to page_id
  si_page int unsigned NOT NULL,

  -- Munged version of title
  si_title varchar(255) NOT NULL default '',

  -- Munged version of body text
  si_text mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE UNIQUE INDEX /*i*/si_page ON /*_*/searchindex (si_page);
CREATE FULLTEXT INDEX /*i*/si_title ON /*_*/searchindex (si_title);
CREATE FULLTEXT INDEX /*i*/si_text ON /*_*/searchindex (si_text);


--
-- For a few generic cache operations if not using Memcached
--
CREATE TABLE /*_*/objectcache (
  keyname varbinary(255) NOT NULL default '' PRIMARY KEY,
  value mediumblob,
  exptime datetime
) /*$wgDBTableOptions*/;
CREATE INDEX /*i*/exptime ON /*_*/objectcache (exptime);


CREATE TABLE /*_*/logging (
  -- Log ID, for referring to this specific log entry, probably for deletion and such.
  log_id int unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,

  -- Symbolic keys for the general log type and the action type
  -- within the log. The output format will be controlled by the
  -- action field, but only the type controls categorization.
  log_type varbinary(32) NOT NULL default '',
  log_action varbinary(32) NOT NULL default '',

  -- Timestamp. Duh.
  log_timestamp binary(14) NOT NULL default '19700101000000',

  -- The actor who performed this action
  log_actor bigint unsigned NOT NULL,

  -- Key to the page affected. Where a user is the target,
  -- this will point to the user page.
  log_namespace int NOT NULL default 0,
  log_title varchar(255) binary NOT NULL default '',
  log_page int unsigned NULL,

  -- Key to comment_id. Comment summarizing the change.
  log_comment_id bigint unsigned NOT NULL,

  -- miscellaneous parameters:
  -- LF separated list (old system) or serialized PHP array (new system)
  log_params blob NOT NULL,

  -- rev_deleted for logs
  log_deleted tinyint unsigned NOT NULL default 0
) /*$wgDBTableOptions*/;

-- Special:Log type filter
CREATE INDEX /*i*/type_time ON /*_*/logging (log_type, log_timestamp);

-- Special:Log performer filter
CREATE INDEX /*i*/actor_time ON /*_*/logging (log_actor, log_timestamp);

-- Special:Log title filter, log extract
CREATE INDEX /*i*/page_time ON /*_*/logging (log_namespace, log_title, log_timestamp);

-- Special:Log unfiltered
CREATE INDEX /*i*/times ON /*_*/logging (log_timestamp);

-- Special:Log filter by performer and type
CREATE INDEX /*i*/log_actor_type_time ON /*_*/logging (log_actor, log_type, log_timestamp);

-- Apparently just used for a few maintenance pages (findMissingFiles.php, Flow).
-- Could be removed?
CREATE INDEX /*i*/log_page_id_time ON /*_*/logging (log_page,log_timestamp);

-- Special:Log action filter
CREATE INDEX /*i*/log_type_action ON /*_*/logging (log_type, log_action, log_timestamp);

-- vim: sw=2 sts=2 et
