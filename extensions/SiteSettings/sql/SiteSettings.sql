--
-- SQL for table needed to run Site Settings, in the main database
--

--
-- Table structure for table `site_settings`
--

CREATE TABLE IF NOT EXISTS /*_*/site_settings (
	name varchar(100) NOT NULL,
	namespace varchar(50) NOT NULL,
	language_code varchar(10) NOT NULL,
	hours_timezone_offset float NOT NULL,
	use_american_dates tinyint(1) NOT NULL,
	use_24_hour_time tinyint(1) NOT NULL,
	show_page_counters tinyint(1) NOT NULL,
	use_subpages tinyint(1) NOT NULL,
	allow_external_images tinyint(1) NOT NULL,
	allow_lowercase_page_names tinyint(1) NOT NULL,
	default_skin varchar(50) NOT NULL,
	background_color varchar(20) default NULL,
	sidebar_color varchar(20) default NULL,
	sidebar_border_color varchar(20) NULL,
	copyright_text varchar(255) default NULL,
	copyright_url varchar(150) default NULL,
	logo_file varchar(100) default NULL,
	favicon_file varchar(50) default NULL,
	viewing_policy_id int(11) NOT NULL,
	registration_policy_id int(11) NOT NULL,
	editing_policy_id int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;
