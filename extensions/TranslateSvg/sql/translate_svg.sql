-- Stores a list of SVGs that have begun the translation process
CREATE TABLE /*$wgDBprefix*/translate_svg (
  -- Link to page.page_id
  ts_page_id int NOT NULL
) /*$wgDBTableOptions*/;
-- Index on ts_page_id
CREATE UNIQUE INDEX /*i*/ts_page_id ON /*$wgDBprefix*/translate_svg (ts_page_id);