--
-- extension GoogleAnalyticsTopPages SQL schema
--
CREATE TABLE /*$wgDBprefix*/page_google_stats (
  page_title varbinary(255) NOT NULL PRIMARY KEY,
  page_visitors bigint(20) unsigned NOT NULL default 0,
  page_latest int(10) unsigned NOT NULL default 0
) /*$wgDBTableOptions*/;
