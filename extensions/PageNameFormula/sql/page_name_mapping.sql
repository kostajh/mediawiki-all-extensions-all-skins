--
-- The page_name_mapping table keeps track of how the name of a page
-- has changed over time.
--
-- rev_id refers to the revision of the page that had a name.
-- page_namespace and page_title matches what the page table showed
-- when that revision was current.
--
CREATE TABLE /*_*/page_name_mapping (
	   page_namespace int NOT NULL,
	   page_title varchar(255) binary NOT NULL,
	   rev_id int unsigned NOT NULL
) /*$wgDBTableOptions*/;
