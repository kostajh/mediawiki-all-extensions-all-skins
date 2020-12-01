CREATE TABLE /*_*/encrypted_file (
  page_id  int         NOT NULL,
  user_id  int         NOT NULL,
  password varchar(32) NOT NULL,
  PRIMARY KEY (page_id)
) /* $wgDbTableOptions */ ;

