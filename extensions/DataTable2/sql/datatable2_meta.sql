-- see https://www.mediawiki.org/wiki/Manual:Coding_conventions/Database

--
-- Data saved from <datatable2> tags.
--
CREATE TABLE /*_*/datatable2_meta (
  dtm_table varchar(255) NOT NULL PRIMARY KEY,
  -- pipe-separated list of column names
  dtm_columns text NOT NULL
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/dtm_table ON /*_*/datatable2_meta(dtm_table);
