CREATE TABLE IF NOT EXISTS /*_*/thicc_threads (
  th_thread_id int(10) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  th_page_id int(10) unsigned NOT NULL,
  th_title varbinary(255) NOT NULL
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/th_page_id ON /*_*/thicc_threads (th_page_id);