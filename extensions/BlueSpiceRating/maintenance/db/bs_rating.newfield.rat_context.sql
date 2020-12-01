ALTER TABLE /*$wgDBprefix*/bs_rating ADD `rat_context` INT(6) NOT NULL DEFAULT 0;
CREATE INDEX /*i*/rat_context ON /*$wgDBprefix*/bs_rating (rat_context);