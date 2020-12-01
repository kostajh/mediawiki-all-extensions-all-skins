-- This is the relational database mapping of class Manuscript.
-- For details on fields, see Manuscript.php
-- For details on mapping, see ManuscriptCRUD.php
CREATE TABLE IF NOT EXISTS /*_*/wikispeech_sdc_manuscript(
    wssdcm_identity varbinary(32) NOT NULL PRIMARY KEY,
    wssdcm_name varchar(64) NOT NULL default '',
    wssdcm_created varbinary(14) NOT NULL default '',
    wssdcm_disabled varbinary(14) NULL default NULL,
    wssdcm_language varbinary(32) NOT NULL default '',
    wssdcm_domain varbinary(32)
)/*$wgDBTableOptions*/;

CREATE INDEX /*i*/list_by_language ON /*_*/wikispeech_sdc_manuscript (wssdcm_language);
CREATE INDEX /*i*/list_by_domain ON /*_*/wikispeech_sdc_manuscript (wssdcm_domain);
