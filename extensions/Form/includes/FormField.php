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
class FormField {
	public $name;
	public $type;
	public $label;
	public $description;
	public $options;

	function __construct() {
		$this->name = null;
		$this->type = null;
		$this->label = null;
		$this->description = null;
		$this->options = [];
	}

	function setName( $name ) {
		$this->name = $name;
	}

	function setFieldType( $type ) {
		$this->type = $type;
	}

	function setLabel( $label ) {
		$this->label = $label;
	}

	function setDescription( $description ) {
		$this->description = $description;
	}

	function setOption( $key, $value ) {
		$this->options[$key] = $value;
	}

	function getOption( $key, $default = null ) {
		if ( array_key_exists( $key, $this->options ) ) {
			return $this->options[$key];
		} else {
			return $default;
		}
	}

	function isOptionTrue( $key, $default = false ) {
		$value = $this->getOption( $key, $default );
		return ( ( strcasecmp( $value, 'on' ) == 0 ) ||
				( strcasecmp( $value, 'yes' ) == 0 ) ||
				( strcasecmp( $value, 'true' ) == 0 ) ||
				( strcasecmp( $value, '1' ) == 0 ) );
	}

	function render( $def = null ) {
		global $wgOut;

		switch ( $this->type ) {
			case 'textarea':
				return Xml::openElement( 'h2' ) .
					Xml::element( 'label', [ 'for' => $this->name ], $this->label ) .
					Xml::closeElement( 'h2' ) .
					( ( $this->description ) ?
					( Xml::openElement( 'div' ) . $wgOut->parseAsContent( $this->description ) . Xml::closeElement( 'div' ) ) : '' ) .
					Xml::openElement( 'textarea',
						[
							'name' => $this->name,
							'id' => $this->name
						]
					) .
					( ( $def === null ) ? '' : $def ) .
					Xml::closeElement( 'textarea' );
			break;
			case 'text':
				return Xml::element( 'label', [ 'for' => $this->name ], $this->label ) . wfMessage( 'colon-separator' )->text() .
					Xml::element( 'input',
						[
							'type' => 'text',
							'name' => $this->name,
							'id' => $this->name,
							'value' => ( ( $def === null ) ? '' : $def ),
							'size' => $this->getOption( 'size', 30 )
						]
					);
			break;
			case 'checkbox':
				$attrs = [
					'type' => 'checkbox',
					'name' => $this->name,
					'id' => $this->name
				];
				if ( $def == 'checked' ) {
					$attrs['checked'] = 'checked';
				}
				return Xml::element( 'label', [ 'for' => $this->name ], $this->label ) . wfMessage( 'colon-separator' )->text() .
					Xml::element( 'input', $attrs );
			break;
			case 'radio':
				$items = [];
				$rawItems = explode( ';', $this->getOption( 'items' ) );
				foreach ( $rawItems as $item ) {
					$attrs = [
						'type' => 'radio',
						'name' => $this->name,
						'value' => $item
					];
					if ( $item == $def ) {
						$attrs['checked'] = 'checked';
					}
					$items[] = Xml::element( 'input', $attrs ) .
						Xml::element( 'label', null, $item );
				}
				return Xml::element( 'span', null, $this->label ) . Xml::element( 'br' ) . implode( '', $items );
			break;
			case 'select':
				$items = [];
				$rawItems = explode( ';', $this->getOption( 'items' ) );
				foreach ( $rawItems as $item ) {
					$items[] = Xml::element(
						'option',
						( $item == $def ) ? [ 'selected' => 'selected' ] : null,
						$item
					);
				}

				return Xml::element( 'label', [ 'for' => $this->name ], $this->label ) . wfMessage( 'colon-separator' )->text() .
					Xml::openElement( 'select', [ 'name' => $this->name, 'id' => $this->name ] ) .
					implode( '', $items ) .
					Xml::closeElement( 'select' );
			break;
			default:
				wfDebug( __METHOD__ . ": unknown form field type '$this->type', skipping.\n" );
				return '';
		}
	}
}
