-- see https://www.mediawiki.org/wiki/Manual:Coding_conventions/Database

--
-- Data saved from <datatable2> tags.
--
CREATE TABLE /*_*/datatable2_data (
  -- Logical table name.
  dtd_table varchar(255) NOT NULL,
  -- Key to the page_id of the page containing the link.
  -- May be null if the table contains data from external sources.
  dtd_page int,
  dtd_01 varchar(255),
  dtd_02 varchar(255),
  dtd_03 varchar(255),
  dtd_04 varchar(255),
  dtd_05 varchar(255),
  dtd_06 varchar(255),
  dtd_07 varchar(255),
  dtd_08 varchar(255),
  dtd_09 varchar(255),
  dtd_10 varchar(255),
  dtd_11 varchar(255),
  dtd_12 varchar(255),
  dtd_13 varchar(255),
  dtd_14 varchar(255),
  dtd_15 varchar(255),
  dtd_16 varchar(255),
  dtd_17 varchar(255),
  dtd_18 varchar(255),
  dtd_19 varchar(255),
  dtd_20 varchar(255),
  dtd_21 varchar(255),
  dtd_22 varchar(255),
  dtd_23 varchar(255),
  dtd_24 varchar(255),
  dtd_25 varchar(255),
  dtd_26 varchar(255),
  dtd_27 varchar(255),
  dtd_28 varchar(255),
  dtd_29 varchar(255),
  dtd_30 varchar(255)
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/dtd_table ON /*_*/datatable2_data(dtd_table);

CREATE INDEX /*i*/dtd_page ON /*_*/datatable2_data(dtd_page);

CREATE INDEX /*i*/dtd_01 ON /*_*/datatable2_data(dtd_01);

CREATE INDEX /*i*/dtd_02 ON /*_*/datatable2_data(dtd_02);

CREATE INDEX /*i*/dtd_03 ON /*_*/datatable2_data(dtd_03);

CREATE INDEX /*i*/dtd_04 ON /*_*/datatable2_data(dtd_04);

CREATE INDEX /*i*/dtd_05 ON /*_*/datatable2_data(dtd_05);

CREATE INDEX /*i*/dtd_06 ON /*_*/datatable2_data(dtd_06);

CREATE INDEX /*i*/dtd_07 ON /*_*/datatable2_data(dtd_07);

CREATE INDEX /*i*/dtd_08 ON /*_*/datatable2_data(dtd_08);

CREATE INDEX /*i*/dtd_09 ON /*_*/datatable2_data(dtd_09);

CREATE INDEX /*i*/dtd_10 ON /*_*/datatable2_data(dtd_10);
