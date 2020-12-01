-- This is the relational database mapping of class SkippedManuscriptPrompt.
-- For details on fields, see SkippedManuscriptPrompt.php
-- For details on mapping, see SkippedManuscriptPromptCRUD.php
CREATE TABLE IF NOT EXISTS /*_*/wikispeech_sdc_skipped_manuscript_prompt(
    wssdcsmp_identity varbinary(32) NOT NULL PRIMARY KEY,
    wssdcsmp_user varbinary(32) NOT NULL default '',
    wssdcsmp_manuscript_prompt varbinary(32) NOT NULL default '',
    wssdcsmp_skipped varbinary(14) NOT NULL default ''
)/*$wgDBTableOptions*/;

CREATE INDEX /*i*/list_by_user ON /*_*/wikispeech_sdc_skipped_manuscript_prompt (wssdcsmp_user);
CREATE INDEX /*i*/list_by_manuscript_prompt ON /*_*/wikispeech_sdc_skipped_manuscript_prompt (wssdcsmp_manuscript_prompt);
CREATE INDEX /*i*/get_by_user_and_manuscript_prompt ON /*_*/wikispeech_sdc_skipped_manuscript_prompt (wssdcsmp_user, wssdcsmp_manuscript_prompt);
