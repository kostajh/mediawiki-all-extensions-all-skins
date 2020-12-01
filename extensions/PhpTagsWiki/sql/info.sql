BEGIN;

CREATE TABLE /*_*/phptagswiki_info(
    ptw_page_id int unsigned NOT NULL PRIMARY KEY,
    ptw_timestamp BINARY(14),
    ptw_extract_plain mediumblob
)/*$wgDBTableOptions*/;

COMMIT;
