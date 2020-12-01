CREATE TABLE /*_*/phptags_schemas (
	template_id int NOT NULL,
	table_schema text NOT NULL
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/template_id ON /*_*/phptags_schemas (template_id);

CREATE TABLE /*_*/phptags_page_templates (
	page_id int NOT NULL,
	templates text NOT NULL
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX /*i*/page_id ON /*_*/phptags_page_templates (page_id);
