CREATE TABLE /*_*/metadata (
	md_page INT UNSIGNED NOT NULL,
	md_key VARCHAR(255) NOT NULL,
	md_value VARCHAR(255) NOT NULL
)/*$wgDBTableOptions*/;

ALTER TABLE /*_*/metadata ADD UNIQUE KEY md_page_key ( md_page, md_key );