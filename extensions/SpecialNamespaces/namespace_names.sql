-- Tables for the SpecialNamespaces extension
-- vim: autoindent syn=mysql sts=2 sw=2
-- Replace /*$wgDBprefix*/ with the proper prefix

CREATE TABLE /*$wgDBprefix*/namespace_names (
  `ns_id` INT(8) NOT NULL DEFAULT '0',
  `ns_name` VARCHAR(200) NOT NULL DEFAULT '',
  `ns_default` tinyint(1) NOT NULL DEFAULT '0',
  `ns_canonical` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`ns_name`)
) /*$wgDBTableOptions*/;

