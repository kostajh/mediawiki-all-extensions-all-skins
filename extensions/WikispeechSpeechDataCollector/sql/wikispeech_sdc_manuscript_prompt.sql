-- This is the relational database mapping of class ManuscriptPrompt.
-- For details on fields, see ManuscriptPrompt.php
-- For details on mapping, see ManuscriptPromptCRUD.php
CREATE TABLE IF NOT EXISTS /*_*/wikispeech_sdc_manuscript_prompt(
    wssdcmp_identity varbinary(32) NOT NULL PRIMARY KEY,
    wssdcmp_manuscript varbinary(32) NOT NULL default '',
    wssdcmp_index int unsigned NOT NULL default 0,
    wssdcmp_content varchar(1024) NOT NULL default ''
)/*$wgDBTableOptions*/;

CREATE INDEX /*i*/list_by_manuscript_and_index ON /*_*/wikispeech_sdc_manuscript_prompt (wssdcmp_manuscript, wssdcmp_index);
