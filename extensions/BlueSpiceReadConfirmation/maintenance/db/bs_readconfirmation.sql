CREATE TABLE /*_*/bs_readconfirmation (
	rc_rev_id int unsigned NOT NULL,
	rc_user_id int unsigned NOT NULL,
	rc_timestamp binary(14) NOT NULL
)/*$wgDBTableOptions*/
COMMENT='BlueSpiceReadConfirmation - Stores information about which revision was read by a user';

CREATE INDEX /*i*/rc_rev_id ON /*_*/bs_readconfirmation (rc_rev_id);
CREATE INDEX /*i*/rc_user_id ON /*_*/bs_readconfirmation (rc_user_id);
