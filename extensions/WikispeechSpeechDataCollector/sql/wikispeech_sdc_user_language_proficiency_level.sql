-- This is the relational database mapping of class UserLanguageProficiencyLevel.
-- For details on fields, see UserLanguageProficiencyLevel.php
-- For details on mapping, see UserLanguageProficiencyLevelCRUD.php
CREATE TABLE IF NOT EXISTS /*_*/wikispeech_sdc_user_language_proficiency_level(
    wssdculpl_identity varbinary(32) NOT NULL PRIMARY KEY,
    wssdculpl_user varbinary(32) NOT NULL default '',
    wssdculpl_language varbinary(32) NOT NULL default '',
    wssdculpl_proficiency_level int unsigned NOT NULL default 0
)/*$wgDBTableOptions*/;

CREATE INDEX /*i*/list_by_user ON /*_*/wikispeech_sdc_user_language_proficiency_level (wssdculpl_user);
CREATE INDEX /*i*/get_by_user_and_language ON /*_*/wikispeech_sdc_user_language_proficiency_level (wssdculpl_user, wssdculpl_language);
