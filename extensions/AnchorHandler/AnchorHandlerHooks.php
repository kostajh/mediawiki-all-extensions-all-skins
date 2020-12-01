<?php
/**
 * Class file for AnchorHandler.
 *
 * This file is part of AnchorHandler.
 * Copyright (C) 2014, Ike Hecht
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */
class AnchorHandlerHooks {
	/**
	 * AddAnchorHandler
	 * @param mixed &$parser parser
	 * @return mixed
	 */
	static function addAnchorHandler( Parser &$parser ) {
		$parser->setHook( 'a', 'AnchorHandlerHooks::anchorHandler' );

		return true;
	}

	/**
	 * If current namespace is an "anchor namespace" specified in $wgAnchorNamespaces
	 * then send out real HTML. Otherwise, just send out the escaped anchor text.
	 * @param mixed $text text
	 * @param mixed $args args
	 * @param mixed $parser parser
	 * @param mixed $frame frame
	 * @return mixed
	 */
	static function anchorHandler( $text, array $args, Parser $parser, PPFrame $frame ) {
		global $egAnchorNamespaces;

		$namespace = $frame->getTitle()->getNamespace();

		if ( !in_array( $namespace, $egAnchorNamespaces ) ) {
			return htmlspecialchars( $text );
		}

		$href = $args['href'];
		$output = Linker::makeExternalLink( $href, $text, false, '', $args );
		unset( $args['href'] );

		return $output;
	}
}
