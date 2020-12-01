-- This is the relational database mapping of class User.
-- For details on fields, see User.php
-- For details on mapping, see UserCRUD.php
CREATE TABLE IF NOT EXISTS /*_*/wikispeech_sdc_user(
    wssdcu_identity varbinary(32) NOT NULL PRIMARY KEY,
    wssdcu_mediawiki_user int unsigned,
    wssdcu_year_born int unsigned
)/*$wgDBTableOptions*/;

CREATE INDEX /*i*/get_by_mediawiki_user ON /*_*/wikispeech_sdc_user (wssdcu_mediawiki_user);
