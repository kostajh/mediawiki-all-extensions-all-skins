-- This is the relational database mapping of class ManuscriptDomain.
-- For details on fields, see ManuscriptDomain.php
-- For details on mapping, see ManuscriptDomainCRUD.php
CREATE TABLE IF NOT EXISTS /*_*/wikispeech_sdc_manuscript_domain(
    wssdcmd_identity varbinary(32) NOT NULL PRIMARY KEY,
    wssdcmd_name varchar(64) NOT NULL default '',
    wssdcmd_parent varbinary(32)
)/*$wgDBTableOptions*/;

CREATE INDEX /*i*/list_by_parent ON /*_*/wikispeech_sdc_manuscript_domain (wssdcmd_parent);
