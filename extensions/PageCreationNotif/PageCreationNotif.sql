-- MySQL version of the database schema for the Semantic Watchlist extension.
-- Licence: GNU GPL v3+
-- Author: Nischay Nahata < nischayn22@gmail.com >

-- Users who want to be notified.
CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/pcn_users (
  pcn_user_id              INT(10) unsigned    NOT NULL, -- Foreign key: user.user_id
  pcn_notify			   Boolean				NOT NULL,
  PRIMARY KEY  (pcn_user_id)
) /*$wgDBTableOptions*/;