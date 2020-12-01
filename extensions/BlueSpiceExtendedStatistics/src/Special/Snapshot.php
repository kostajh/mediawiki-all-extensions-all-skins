<?php

namespace BlueSpice\ExtendedStatistics\Special;

use FormatJson;
use Html;
use MediaWiki\MediaWikiServices;
use MWException;

class Snapshot extends \BlueSpice\SpecialPage {
	public function __construct() {
		parent::__construct( 'ExtendedStatisticsSnapshots', 'wikiadmin', false );
	}

	/**
	 *
	 * @param string $parameter
	 * @throws MWException
	 */
	public function execute( $parameter ) {
		parent::execute( $parameter );
		if ( empty( $parameter ) ) {
			throw new MWException( 'Requires a valid type and id in format "$type-$id"' );
		}
		list( $type, $id ) = explode( '-', $parameter );
		if ( empty( $type ) || empty( $id ) ) {
			throw new MWException( 'Requires a valid type and id in format "$type-$id"' );
		}
		$entity = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )->newFromID(
			$id,
			$type
		);
		if ( !$entity ) {
			throw new MWException( "invalid entity \"$type-$id\"" );
		}
		$this->getOutput()->addHtml( $this->rootValueTable( $entity->getFullData() ) );
	}

	/**
	 * Construct HTML table representation of any JSON value.
	 *
	 * See also valueCell, which is similar.
	 *
	 * @param mixed $val
	 * @return string HTML.
	 */
	protected function rootValueTable( $val ) {
		if ( is_object( $val ) ) {
			return $this->objectTable( $val );
		}

		if ( is_array( $val ) ) {
			// Wrap arrays in another array so that they're visually boxed in a container.
			// Otherwise they are visually indistinguishable from a single value.
			return $this->arrayTable( [ $val ] );
		}

		return Html::rawElement( 'table', [ 'class' => 'mw-json mw-json-single-value' ],
			Html::rawElement( 'tbody', [],
				Html::rawElement( 'tr', [],
					Html::element( 'td', [], $this->primitiveValue( $val ) )
				)
			)
		);
	}

	/**
	 * Create HTML table representing a JSON object.
	 *
	 * @param stdClass $mapping
	 * @return string HTML
	 */
	protected function objectTable( $mapping ) {
		$rows = [];
		$empty = true;

		foreach ( $mapping as $key => $val ) {
			$rows[] = $this->objectRow( $key, $val );
			$empty = false;
		}
		if ( $empty ) {
			$rows[] = Html::rawElement( 'tr', [],
				Html::element( 'td', [ 'class' => 'mw-json-empty' ],
					wfMessage( 'content-json-empty-object' )->text()
				)
			);
		}
		return Html::rawElement( 'table', [ 'class' => 'mw-json' ],
			Html::rawElement( 'tbody', [], implode( '', $rows ) )
		);
	}

	/**
	 * Create HTML table row representing one object property.
	 *
	 * @param string $key
	 * @param mixed $val
	 * @return string HTML.
	 */
	protected function objectRow( $key, $val ) {
		$th = Html::element( 'th', [], $key );
		$td = $this->valueCell( $val );
		return Html::rawElement( 'tr', [], $th . $td );
	}

	/**
	 * Create HTML table representing a JSON array.
	 *
	 * @param array $mapping
	 * @return string HTML
	 */
	protected function arrayTable( $mapping ) {
		$rows = [];
		$empty = true;

		foreach ( $mapping as $val ) {
			$rows[] = $this->arrayRow( $val );
			$empty = false;
		}
		if ( $empty ) {
			$rows[] = Html::rawElement( 'tr', [],
				Html::element( 'td', [ 'class' => 'mw-json-empty' ],
					$this->msg( 'content-json-empty-array' )->text()
				)
			);
		}
		return Html::rawElement( 'table', [ 'class' => 'mw-json' ],
			Html::rawElement( 'tbody', [], implode( "\n", $rows ) )
		);
	}

	/**
	 * Create HTML table row representing the value in an array.
	 *
	 * @param mixed $val
	 * @return string HTML.
	 */
	protected function arrayRow( $val ) {
		$td = $this->valueCell( $val );
		return Html::rawElement( 'tr', [], $td );
	}

	/**
	 * Construct HTML table cell representing any JSON value.
	 *
	 * @param mixed $val
	 * @return string HTML.
	 */
	protected function valueCell( $val ) {
		if ( is_object( $val ) ) {
			return Html::rawElement( 'td', [], $this->objectTable( $val ) );
		}

		if ( is_array( $val ) ) {
			return Html::rawElement( 'td', [], $this->arrayTable( $val ) );
		}

		return Html::element( 'td', [ 'class' => 'mw-json-value' ], $this->primitiveValue( $val ) );
	}

	/**
	 * Construct text representing a JSON primitive value.
	 *
	 * @param mixed $val
	 * @return string Text.
	 */
	protected function primitiveValue( $val ) {
		if ( is_string( $val ) ) {
			// Don't FormatJson::encode for strings since we want quotes
			// and new lines to render visually instead of escaped.
			return '"' . $val . '"';
		}
		return FormatJson::encode( $val );
	}
}
