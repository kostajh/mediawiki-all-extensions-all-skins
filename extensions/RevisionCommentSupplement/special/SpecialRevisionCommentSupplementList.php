<?php
/**
 * Copyright (C) 2012 Burthsceh <burthsceh@gmail.com>
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

$wgAutoloadClasses['ViewRevisionCommentSupplementList'] = __DIR__ . '/ViewRevisionCommentSupplementList.php';
$wgAutoloadClasses['ViewRevisionCommentSupplementHistory'] = __DIR__ . '/ViewRevisionCommentSupplementHistory.php';

class SpecialRevisionCommentSupplementList extends SpecialPage {

	public function __construct() {
		parent::__construct( 'RevisionCommentSupplementList' );
	}

	# from class SpecialAbuseFilter in SpecialAbuseFilter.php into extension AbuseFilter
	function execute( $subpage ) {
		$this->setHeaders();
		$out = $this->getOutput();
		$view = 'error';
		$par = '';

		$this->checkReadOnly();

		$params = explode( '/', $subpage );

		// Filter by removing blanks.
		foreach ( $params as $index => $param ) {
			if ( $param === '' ) {
				unset( $params[$index] );
			}
		}
		$params = array_values( $params );

		if ( count( $params ) == 2 && $params[0] == 'history' && is_numeric( $params[1] ) ) {
			$view = 'ViewRevisionCommentSupplementHistory';
			$par = $params[1];
		} elseif ( count( $params ) == 2 && $params[0] == 'list' && is_numeric( $params[1] ) ) {
			$view = 'ViewRevisionCommentSupplementList';
			$par = $params[1];
		} elseif ( count( $params ) == 1 && $params[0] == 'list' ) {
			$view = 'ViewRevisionCommentSupplementList';
		} elseif ( count( $params ) == 1 && is_numeric( $params[0] ) ) {
			$view = 'ViewRevisionCommentSupplementList';
			$par = $params[0];
		} elseif ( count( $params ) == 0 ) {
			$view = 'ViewRevisionCommentSupplementList';
		} else {
			$view = 'ViewRevisionCommentSupplementList';
			$par = 'error';
		}

		$v = new $view( $this, $par );
		$v->show();
	}

	protected function getGroupName() {
		return 'other';
	}
}
