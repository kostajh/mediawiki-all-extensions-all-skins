-- Database definition for Expiry
--
-- Part of BlueSpice MediaWiki
--
-- @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-network.hk>
-- @package    BlueSpiceExpiry
-- @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
-- @filesource

CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/bs_expiry (
  `exp_id`      INT(10) NOT NULL AUTO_INCREMENT ,
  `exp_page_id` INT(10) NOT NULL ,
  `exp_date`    DATE NOT NULL,
  `exp_comment` VARBINARY(255) ,
  PRIMARY KEY `exp_id` (`exp_id`) ,
  INDEX `exp_page_id_idx` (`exp_page_id` ASC) ,
  INDEX `exp_date_idx` (`exp_date` ASC)
) /*$wgDBTableOptions*/;
