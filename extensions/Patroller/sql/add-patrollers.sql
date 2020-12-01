--
-- Table to hold temporary "assignments" of recent changes when using the Patroller extension.
--

-- Patrollers table
CREATE TABLE /*_*/patrollers (
	-- Unique ID for each change
	ptr_change int(8) NOT NULL,
	-- Timestamp of change
	ptr_timestamp varchar(14) NOT NULL,
	-- Set the unique key
	UNIQUE KEY ptr_change (ptr_change),
	-- Set the key for timestamp
	KEY ptr_timestamp (ptr_timestamp)
) /*$wgDBTableOptions*/;