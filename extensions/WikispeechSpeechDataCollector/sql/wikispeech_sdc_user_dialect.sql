-- This is the relational database mapping of class UserDialect.
-- For details on fields, see UserDialect.php
-- For details on mapping, see UserDialectCRUD.php
CREATE TABLE IF NOT EXISTS /*_*/wikispeech_sdc_user_dialect(
    wssdcud_identity varbinary(32) NOT NULL PRIMARY KEY,
    wssdcud_user varbinary(32) NOT NULL default '',
    wssdcud_language varbinary(32) NOT NULL default '',
    wssdcud_spoken_proficiency_level int unsigned NOT NULL default 0,
    wssdcud_location varchar(1024) NOT NULL default ''
)/*$wgDBTableOptions*/;

CREATE INDEX /*i*/list_by_user ON /*_*/wikispeech_sdc_user_dialect (wssdcud_user);
