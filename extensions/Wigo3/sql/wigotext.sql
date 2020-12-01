CREATE TABLE /*$wgDBprefix*/wigotext (
	vote_id VARCHAR(255) NOT NULL,
	text MEDIUMBLOB,
	is_cp BOOLEAN DEFAULT FALSE,
	PRIMARY KEY (vote_id)
) /*$wgDBTableOptions*/;
