<?php
namespace PhpTagsObjects;

use PhpTags\GenericObject;
use SiteStats;

/**
 * Static accessor class for site_stats and related things
 *
 * @author pastakhov
 */
class WikiWStats extends GenericObject {

	/* @todo
	 * pagesInCategory()
	 * pagesInNamespace()
	 * usersInGroup()
	 *
	 */

	public static function c_ACTIVE_USERS() {
		static $value = false;
		if ( $value === false ) {
			$value = (int)SiteStats::activeUsers();
		}
		return $value;
	}

	public static function c_ADMINS() {
		static $value = false;
		if ( $value === false ) {
			$value = (int)SiteStats::numberingroup( 'sysop' );
		}
		return $value;
	}

	public static function c_ARTICLES() {
		static $value = false;
		if ( $value === false ) {
			$value = (int)SiteStats::articles();
		}
		return $value;
	}

	public static function c_EDITS() {
		static $value = false;
		if ( $value === false ) {
			$value = (int)SiteStats::edits();
		}
		return $value;
	}

	public static function c_FILES() {
		static $value = false;
		if ( $value === false ) {
			$value = (int)SiteStats::images();
		}
		return $value;
	}

	public static function c_PAGES() {
		static $value = false;
		if ( $value === false ) {
			$value = (int)SiteStats::pages();
		}
		return $value;
	}

	public static function c_USERS() {
		static $value = false;
		if ( $value === false ) {
			$value = (int)SiteStats::users();
		}
		return $value;
	}

	public static function c_JOBS() {
		static $value = false;
		if ( $value === false ) {
			$value = (int)SiteStats::jobs();
		}
		return $value;
	}

}
