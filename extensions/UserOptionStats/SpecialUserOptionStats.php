<?php

/**
 * Special page for generating pie charts of user options
 *
 * @ingroup SpecialPages
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class SpecialUserOptionStats extends SpecialPage {
	public function __construct() {
		parent::__construct( 'UserOptionStats' );
	}

	public $blacklist = [ 'nickname', 'watchlisttoken' ];

	public function execute( $par ) {
		$this->setHeaders();
		$this->outputHeader();

		if ( !class_exists( 'PHPlot' ) ) {
			$this->getOutput()->addWikiMsg( 'uos-warn' );
			return;
		}

		$par = trim( strtolower( $par ) );

		foreach ( $this->blacklist as $b ) {
			if ( $b === $par ) {
				$par = false;
				break;
			}
		}

		$lang = $this->getLanguage();

		if ( !$par ) {
			$opts = [];
			$hiddenoptions = $this->getHiddenOptions();
			$name = SpecialPage::getTitleFor( 'UserOptionStats' )->getPrefixedText();
			foreach ( $this->getOptions() as $k ) {
				if ( in_array( $k, $hiddenoptions ) ) {
					continue; # List hidden options separately (see below)
				}
				$opts[] = "[[$name/$k|$k]]";
			}
			$this->getOutput()->addWikiMsg( 'uos-choose', $lang->commaList( $opts ) );
			if ( count( $hiddenoptions ) > 0 ) {
				$hiddenopts = [];
				foreach ( $hiddenoptions as $hk ) {
					$hiddenopts[] = "[[$name/$hk|$hk]]";
				}
				$this->getOutput()->addWikiMsg( 'uos-choose-hidden', $lang->commaList( $hiddenopts ) );
			}
			return;
		}

		$dbr = wfGetDB( DB_REPLICA );

		$total = $dbr->selectField( 'user', 'count(*)', '', __METHOD__ );

		$data = [];
		$props = $dbr->select(
			'user_properties',
			[ 'up_value', 'count(up_value) as c' ],
			[ 'up_property' => $par ],
			__METHOD__,
			[ 'GROUP BY' => 'up_value' ]
		);

		foreach ( $props as $row ) {
			$total -= $row->c;
			$data[$row->up_value] = $row->c;
		}
		$data[$this->msg( 'uos-unknown' )->text()] = $total;

		$realdata = [];
		$labels = [];

		// Most popular first, barring other
		arsort( $data );

		// After more than 7 the default colors start to loop :(
		// So use the last free color for "other" which includes the rest
		$max = 7;
		$rest = array_slice( $data, $max );
		$data = array_slice( $data, 0, $max, true );
		foreach ( $data as $k => $d ) {
			$labels[] = "$k ($d)";
			$realdata[] = [ $k, $d ];
		}
		if ( count( $rest ) ) {
			$other = 0;
			foreach ( $rest as $v ) {
				$other += $v;
			}
			$labels[] = $this->msg( 'uos-other' )->text() .
				$this->msg( 'word-separator' )->text() .
				$this->msg( 'parentheses', $other )->text();
			$realdata[] = [ 'other', $other ];
		}

		$request = $this->getRequest();
		$title = $request->getText( 'pietitle', $this->msg( 'uos-title', $par )->text() );
		$width = $request->getInt( 'width', 700 );
		$height = $request->getInt( 'height', 500 );
		$width = max( 200, min( 1000, $width ) );
		$height = max( 200, min( 1000, $height ) );
		$shading = $request->getInt( 'shading', 10 );
		$height = max( 0, min( 1000, $height ) );

		// Define the object
		$plot = new PHPlot( $width, $height );
		$plot->SetDataType( 'text-data-single' );
		$plot->setDataValues( $realdata );
		$plot->SetPlotType( 'pie' );
		$plot->SetLegend( $labels );
		$plot->SetShading( $shading );
		$plot->SetLabelScalePosition( 0.3 );
		$plot->SetTitle( $title );

		// Better fonts
		$font = FCFontFinder::findFile( $lang->getCode() );
		if ( $font ) {
			$plot->SetDefaultTTFont( $font );
		}

		$this->getOutput()->disable();
		$plot->DrawGraph();
	}

	public function getOptions() {
		global $wgDefaultUserOptions;

		$opts = [];
		foreach ( $wgDefaultUserOptions as $k => $v ) {
			$opts[$k] = true;
		}

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select( 'user_properties', 'DISTINCT(up_property) as value', '', __METHOD__ );
		foreach ( $res as $r ) {
			$opts[$r->value] = true;
		}

		foreach ( $this->blacklist as $b ) {
			unset( $opts[$b] );
		}

		$opts = array_keys( $opts );
		sort( $opts );

		return $opts;
	}

	public function getHiddenOptions() {
		global $wgHiddenPrefs;
		if ( isset( $wgHiddenPrefs ) && is_array( $wgHiddenPrefs ) ) {
			return $wgHiddenPrefs;
		}
		return [];
	}

	protected function getGroupName() {
		return 'wiki';
	}
}
