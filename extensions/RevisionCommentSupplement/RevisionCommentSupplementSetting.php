<?php
/**
 * Copyright (C) 2013 Burthsceh <burthsceh@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

/**
 * $egRevisionCommentSupplementSettings
 *
 * Settings of RevisionCommentSupplement
 *
 * history
 *  bool
 *  true: record changes of supplementary comments into rev_supp_comment_history table
 *  false: don't record changes of supplementary comments into rev_supp_comment_history table
 *
 * log
 *  array|bool|string
 *  'create': log when supplementary comment is created
 *  'modify': log when supplementary comment is modified
 *  'delete': log when supplementary comment is deleted
 *  'hidehistory':   log when items are deleted and/or undeleted in rev_supp_comment_history table
 *  true: always log     equivalent to array( 'create', 'modify', 'delete', 'hidehistory' )
 *  false: never log
 *
 * logsupplement
 *  bool
 *  true: record supplementary comments in log
 *  false: don't record supplementary comments in log
 *
 * logpublish
 *  array|bool|string
 *  'create': publish log when supplementary comment is created
 *  'modify': publish log when supplementary comment is modified
 *  'delete': publish log when supplementary comment is deleted
 *  'hidehistory':   publish log when items are deleted and/or undeleted in rev_supp_comment_history table
 *            except for logging to 'suppress'
 *  true: always publish log     equivalent to array( 'create', 'modify', 'delete', 'hidehistory' )
 *        array( 'create' => true, 'modify' => true, 'delete' => true, 'hidehistory' => true )
 *        array( 'create' => 'rcandudp', 'modify' => 'rcandudp', 'delete' => true, 'hidehistory' => true )
 *  false: never publish log
 *  'rcandudp': publish log in RecentChanges and UDP feed
 *  'rc': publish log in RecentChanges
 *  'udp': publish log in UDP feed of RecentChanges
 */

class RevisionCommentSupplementSetting {

	/**
	 * @param array $param
	 * @return array $egRevisionCommentSupplementSettings
	 */
	public static function setSettings( $param ) {
		global $egRevisionCommentSupplementSettings;
		$egRevisionCommentSupplementSettings = array_replace(
			$egRevisionCommentSupplementSettings, $param
		);
		return $egRevisionCommentSupplementSettings;
	}

	/**
	 * @param string $key
	 * @param array|bool|string $value
	 * @return array $egRevisionCommentSupplementSettings
	 */
	public static function setSetting( $key, $value ) {
		return self::setSettings( array( $key, $value ) );
	}

	/**
	 * @param string $key
	 * @return array|bool|string
	 */
	public static function getSetting( $key ) {
		global $egRevisionCommentSupplementSettings;
		return $egRevisionCommentSupplementSettings[$key];
	}

	/**
	 * @return bool
	 */
	public static function getHistorySetting() {
		return self::getSetting( 'history' ) === true;
	}

	/**
	 * @param string $action
	 * @return bool
	 */
	public static function getLogSetting( $action ) {
		$s = self::getSetting( 'log' );
		if ( is_array( $s ) ) {
			return in_array( $action, $s );
		} elseif ( is_string( $s ) ) {
			return $s === $action;
		} elseif ( is_bool( $s ) ) {
			return $s;
		} else {
			return false;
		}
	}

	/**
	 * @param string $action
	 * @return bool|string
	 */
	public static function getLogPublishSetting( $action ) {
		$s = self::getSetting( 'logpublish' );
		if ( is_array( $s ) ) {
			if ( array_key_exists( $action, $s ) ) {
				if ( in_array( $s[$action], array( 'rcandudp', 'rc', 'udp', true ), true ) ) {
					return $s[$action];
				} else {
					return false;
				}
			}
			ksort( $s );
			$actions = array_fill_keys(
				array( 'create', 'modify', 'delete', 'hidehistory' ), false
			);
			$to = array_fill_keys( array( 'rcandudp', 'rc', 'udp', true, false ), false );
			foreach ( $s as $key => $value ) {
				if ( is_int( $key ) ) {
					if (
						in_array( $value, array( 'create', 'modify', 'delete', 'hidehistory' ) )
					) {
						$actions[$value] = true;
					} else {
						$to[$value] = true;
					}
				} else {
					break;
				}
			}
			if ( $to[false] || ( $to['rc'] && $to['udp'] ) ) {
				return false;
			}
			if ( array_search( true, $actions, true ) ) {
				if ( !$actions[$action] ) {
					return false;
				}
			}
			foreach ( array( 'rc', 'udp', 'rcandudp', true ) as $value ) {
				if ( $to[$value] ) {
					return $value;
				}
			}
			return false;
		} elseif ( is_string( $s ) ) {
			if ( $s === $action ) {
				return true;
			} elseif ( in_array( $s, array( 'rcandudp', 'rc', 'udp' ), true ) ) {
				return $s;
			}
			return false;
		} elseif ( is_bool( $s ) ) {
			return $s;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	public static function getLogSupplementSetting() {
		return self::getSetting( 'logsupplement' ) === true;
	}
}
