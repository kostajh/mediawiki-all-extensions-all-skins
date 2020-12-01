-- Table that holds information on each installed package.
-- If a package is uninstalled, its entry gets deleted.
CREATE TABLE /*_*/px_packages (
	pxp_id int PRIMARY KEY,
	pxp_date_installed datetime NOT NULL,
	pxp_installing_user int NOT NULL,
	pxp_name varchar(255) NOT NULL,
	pxp_global_id varchar(255) NOT NULL UNIQUE,
	pxp_js_pages varchar(500),
	pxp_css_pages varchar(500),
	pxp_package_data TEXT NOT NULL
) /*$wgDBTableOptions*/;
