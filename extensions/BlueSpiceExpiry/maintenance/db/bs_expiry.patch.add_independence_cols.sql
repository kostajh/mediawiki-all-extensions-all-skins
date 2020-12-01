ALTER TABLE /*$wgDBprefix*/bs_expiry ADD COLUMN `exp_id` INT(10) NOT NULL PRIMARY KEY AUTO_INCREMENT;
ALTER TABLE /*$wgDBprefix*/bs_expiry ADD COLUMN `exp_page_id` INT(10) NOT NULL AFTER `exp_id`;
ALTER TABLE /*$wgDBprefix*/bs_expiry ADD COLUMN `exp_date` DATE NOT NULL AFTER `exp_page_id`;
ALTER TABLE /*$wgDBprefix*/bs_expiry ADD COLUMN `exp_comment` VARBINARY(255) AFTER `exp_date`;
