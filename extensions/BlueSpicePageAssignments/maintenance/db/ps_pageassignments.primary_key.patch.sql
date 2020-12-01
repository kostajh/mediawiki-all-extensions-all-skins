ALTER TABLE /*_*/bs_pageassignments DROP PRIMARY KEY;
ALTER TABLE /*_*/bs_pageassignments ADD PRIMARY KEY( pa_page_id, pa_assignee_key, pa_assignee_type );