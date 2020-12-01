<?php
/**
 * Syslog utilities :) Really it doesn't have to be syslog specific but it's what fundraising
 * at MW has always used. This just serves as a convienent wrapper class for managing things I find
 * useful in logs.
 *
 * As to why am I reinventing the wheel for the nth time; well... MW's own logging mechanism sucks
 * and does not do what FR needs it to do; and PHPs debug_log() would also require a wrapper.
 *
 * -- License --
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
 *
 * @file
 */

/**
 * Methods for logging stuff to Syslog
 */
class Logger {

	/** @var self The global instance of the Syslog class */
	private static $inst = null;

	/** @var string What process to tell syslog the message came from */
	private static $bucket = '';
	/** @var string A persistent string prepended to all messages */
	private static $context = '';
	/** @var string[] A stack of strings, whatever is on the top of the stack will get prepended to the message */
	private static $label = [ '' ];

	private function __construct() {
	}

	/**
	 * Log a message string to Syslog
	 * @static
	 *
	 * @param string $message The string to log
	 * @param int $priority The syslog priority to log this message at
	 * @param string|null $label When pushing/popping a label would be too much work
	 */
	public static function log( $message, $priority = LOG_INFO, $label = null ) {
		if ( static::$inst == null ) {
			static::$inst = new Logger();
		}

		static::$inst->logString( $message, $priority, $label );
	}

	/**
	 * Log an exception to Syslog. The stack trace will be included as a JSON string.
	 * @static
	 *
	 * @param Exception $ex The exception
	 * @param string $additionalText Any string to also log as context, default is empty
	 * @param int $priority The syslog priority to log this message at, default ERR
	 * @param string|null $label When pushing/popping a label would be too much work
	 */
	public static function logEx( Exception $ex, $additionalText = '', $priority = LOG_ERR, $label = null ) {
		if ( static::$inst == null ) {
			static::$inst = new Logger();
		}

		$msg = [];
		$msg[] = get_class( $ex );

		if ( $additionalText != '' ) {
			$msg[] = $additionalText;
		}

		$msg[] = '@';
		$msg[] = $ex->getFile() . ':' . $ex->getLine();
		$msg[] = '->';
		$msg[] = $ex->getMessage();
		$msg[] = '; Trace ->';
		$msg[] = json_encode( $ex->getTrace() );

		static::$inst->logString( implode( ' ', $msg ), $priority, $label );
	}

	/**
	 * Set the process Syslog thinks this is. This is just a string that gets prepended to the log
	 * message by the Syslog utility. Can be used for very cheap bucketing of logs.
	 * @static
	 *
	 * @param string $bucket
	 */
	public static function setBucket( $bucket ) {
		static::$bucket = $bucket;
	}

	/**
	 * A string that will be added to the front of every log message regardless of the current
	 * label. Use it for things like request IDs.
	 * @static
	 *
	 * @param string $context
	 */
	public static function setContext( $context ) {
		static::$context = $context;
	}

	/**
	 * By pushing a label, you change the sub identifier string for each message logged after the
	 * string was pushed.
	 * @static
	 *
	 * @param string $label
	 */
	public static function pushLabel( $label ) {
		static::$label[] = $label;
	}

	/**
	 * Go back to the last label.
	 * @static
	 */
	public static function popLabel() {
		array_pop( static::$label );
	}

	/**
	 * Perform the actual log operation.
	 * @param string $msgText
	 * @param int $pri
	 * @param string|null $label
	 */
	private function logString( $msgText, $pri, $label = null ) {
		global $wgFundraisingEmailUnsubscribeLogFacility;

		if ( static::$context != '' ) {
			$msg[] = '(' . static::$context . ')';
		}
		$msg[] = ':';
		if ( $label != null ) {
			$msg[] = $label;
			$msg[] = ':';
		} elseif ( end( static::$label ) != '' ) {
			$msg[] = end( static::$label );
			$msg[] = ':';
		}
		$msg[] = $msgText;

		openlog( static::$bucket, LOG_ODELAY, $wgFundraisingEmailUnsubscribeLogFacility );
		syslog( $pri, implode( ' ', $msg ) );
		closelog();
	}
}
