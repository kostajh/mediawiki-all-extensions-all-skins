-- Create the table : 'downloads_files'

CREATE TABLE IF NOT EXISTS /*_*/downloads_files (
  -- I guess, file name is unique
  filename      INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  -- By default, downloaded 0 time (could be 1)
  downloaded    INT(5)       NOT NULL DEFAULT 0,
  -- By default, downloaded 0 time (could be 1)
  last_download INT(5)       NOT NULL DEFAULT 0
) /*$wgDBTableOptions*/;
