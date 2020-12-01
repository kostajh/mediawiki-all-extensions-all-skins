CREATE TABLE `emailrequest` (
  `email` tinyblob NOT NULL,
  `request` blob NOT NULL,
  PRIMARY KEY (`email`(50))
) ENGINE=InnoDB DEFAULT CHARSET=binary;
