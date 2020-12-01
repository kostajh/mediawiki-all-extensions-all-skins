<?php
/**
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
 * Hooks for MetaMaster extension
 *
 * @file
 * @ingroup Extensions
 */
class MetaMasterHooks {
	/**
	 * Tracks the number of calls to the parser hook
	 *
	 * @var int
	 */
	private static $num = 0;

	/**
	 * Adds the parser hook
	 *
	 * @param Parser &$parser
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setFunctionHook( 'metamaster', 'MetaMasterHooks::addMeta' );
	}

	/**
	 * Parse the parser input and get it ready for output
	 *
	 * @param Parser &$parser
	 * @param string $name
	 * @param string $content
	 */
	public static function addMeta( Parser &$parser, $name, $content ) {
		$meta = [ 'name' => htmlspecialchars( $name ), 'content' => htmlspecialchars( $content ) ];
		$parser->getOutput()->setExtensionData( 'metaMaster-' . self::$num, $meta );
		self::$num++;
	}

	/**
	 * Add the meta tag to the output
	 *
	 * @param OutputPage &$out
	 * @param ParserOutput $parseroutput
	 */
	public static function onOutputPageParserOutput( OutputPage &$out, ParserOutput $parseroutput ) {
		for ( $i = 0; $i < self::$num; $i++ ) {
			$meta = $parseroutput->getExtensionData( 'metaMaster-' . $i );
			$out->addMeta( $meta['name'], $meta['content'] );
		}
	}
}
