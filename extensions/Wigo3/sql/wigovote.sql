CREATE TABLE /*$wgDBprefix*/wigovote (
	id varchar(255) NOT NULL,
	voter_name varchar(255) NOT NULL,
	vote int NOT NULL default 0,
	timestamp varbinary(14),
	PRIMARY KEY (id,voter_name)
) /*$wgDBTableOptions*/;
