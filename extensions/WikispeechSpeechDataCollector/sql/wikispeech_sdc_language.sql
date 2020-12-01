-- This is the relational database mapping of class Language.
-- For details on fields, see Language.php
-- For details on mapping, see LanguageCRUD.php
CREATE TABLE IF NOT EXISTS /*_*/wikispeech_sdc_language(
    wssdcl_identity varbinary(32) NOT NULL PRIMARY KEY,
    wssdcl_native_name varchar(64) NOT NULL default '',
    wssdcl_iso639a1 varchar(3),
    wssdcl_iso639a2b varchar(3),
    wssdcl_iso639a2t varchar(3),
    wssdcl_iso639a3 varchar(3)
)/*$wgDBTableOptions*/;

CREATE INDEX /*i*/list_by_iso639a1 ON /*_*/wikispeech_sdc_language (wssdcl_iso639a1);
CREATE INDEX /*i*/list_by_iso639a2b ON /*_*/wikispeech_sdc_language (wssdcl_iso639a2b);
CREATE INDEX /*i*/list_by_iso639a2t ON /*_*/wikispeech_sdc_language (wssdcl_iso639a2t);
CREATE INDEX /*i*/list_by_iso639a3 ON /*_*/wikispeech_sdc_language (wssdcl_iso639a3);
