CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/bs_extendedstatistics_snapshot (
	ess_id         INT(10)            NOT NULL AUTO_INCREMENT ,
	ess_data       mediumblob         NOT NULL,
	ess_timestamp  binary(14),
	PRIMARY KEY (ess_id),
	UNIQUE KEY ess_id (ess_id)
) /*$wgDBTableOptions*/ COMMENT='BlueSpice: ExtendedStatistics - Stores snapshot data';