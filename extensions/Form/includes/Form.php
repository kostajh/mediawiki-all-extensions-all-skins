<?php
/**
 * Form extension -- Use a form-based interface to start new articles
 * Copyright 2007 Vinismo, Inc. (http://vinismo.com/)
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @file
 * @ingroup Extensions
 * @author Evan Prodromou <evan@vinismo.com>
 */
class Form {
	public $name;
	public $title;
	public $template;
	public $instructions;
	public $fields;
	public $namePattern;

	function __construct( $name, $text ) {
		$this->name = $name;
		$this->title = wfMessage( 'form-title-pattern', $name )->inContentLanguage()->text();
		$this->template = [];
		$this->template[0] = wfMessage( 'form-template-pattern', $name )->inContentLanguage()->text();

		$this->fields = [];
		$this->namePattern = [];
		$this->instructions = null;

		# XXX: may be some faster ways to do this
		$lines = explode( "\n", $text );

		foreach ( $lines as $line ) {
			if ( preg_match( '/^(\w+)=(.*)$/', $line, $matches ) ) {
				if ( strcasecmp( $matches[1], 'template' ) == 0 ) {
					$this->template[0] = $matches[2];
				} elseif ( preg_match( '/template(\d+)/i', $matches[1], $tmatches ) ) {
					$this->template[intval( $tmatches[1] )] = $matches[2];
				} elseif ( strcasecmp( $matches[1], 'namePattern' ) == 0 ) {
					$this->namePattern[0] = $matches[2];
				} elseif ( preg_match( '/namePattern(\d+)/i', $matches[1], $tmatches ) ) {
					$this->namePattern[intval( $tmatches[1] )] = $matches[2];
				} elseif ( strcasecmp( $matches[1], 'title' ) == 0 ) {
					$this->title = $matches[2];
				} elseif ( strcasecmp( $matches[1], 'instructions' ) == 0 ) {
					$this->instructions = $matches[2];
					wfDebug( __METHOD__ . ": Got instructions: '" . $this->instructions . "'.\n" );
				} else {
					wfDebug( __METHOD__ . ": unknown form attribute '$matches[1]'; skipping.\n" );
				}
			} elseif ( preg_match( '/^(\w+)\|([^\|]+)\|(\w+)(\|([^\|]+)(\|(.*))?)?$/', $line, $matches ) ) {
				# XXX: build an inheritance tree for different kinds of fields
				$field = new FormField();
				$field->setName( $matches[1] );
				$field->setLabel( $matches[2] );
				$field->setFieldType( $matches[3] );
				if ( count( $matches ) > 4 && $matches[4] ) {
					$field->setDescription( $matches[5] );
					if ( count( $matches ) > 6 && $matches[6] ) {
						$rawOptions = explode( ',', $matches[7] );
						foreach ( $rawOptions as $rawOption ) {
							if ( preg_match( '/^(\w+)=(.+)/', $rawOption, $optMatches ) ) {
								$field->setOption( $optMatches[1], $optMatches[2] );
							} else {
								wfDebug( __METHOD__ . ": unrecognized form field option: '$rawOption'; skipping.\n" );
							}
						}
					}
				}
				$this->fields[$field->name] = $field;
			} else {
				wfDebug( __METHOD__ . ": unrecognized form line: '$line'; skipping.\n" );
			}
		}
	}
}
