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

class RevisionCommentSupplementLogFormatter extends LogFormatter {

	function getMessageParameters() {
		$params = parent::getMessageParameters();
		$params[4] = Message::rawParam( $this->getSupplementaryComment( $params[4] ) );
		$params[5] = Message::rawParam( $this->getSupplementaryComment( $params[5] ) );
		return $params;
	}

	/**
	 * @params string $supplement
	 * @return string HTML
	 */
	function getSupplementaryComment( $supplement ){
		$s = '';
		if ( $supplement ) {
			$s = $this->msg( 'revcs-log-supplement' )
				->rawParams( htmlspecialchars( $supplement ) )->escaped();
		} else {
			$s = $this->msg( 'revcs-log-nosupplement' )->escaped();
		}
		return $s;
	}
}

$wgAutoloadClasses['RevisionCommentSupplementHistory'] = __DIR__ . '/RevisionCommentSupplement.class.php';

class RevisionCommentSupplementHideHistoryLogFormatter extends LogFormatter {

	# references:
	# DeleteLogFormatter::getMessageParameters in LogFormatter.php
	# RevisionDeleter::getChanges in RevisionDeleter.php
	function getMessageParameters() {
		$params = parent::getMessageParameters();

		$params[7] = count( explode( ',', $params[6] ) );
		$diff = $params[4] ^ $params[5];
		$changes = array();
		foreach ( RevisionCommentSupplementHistory::$flags as $key => $value ) {
			if ( $diff & $key ) {
				if ( $params[5] & $key ) {
					$changes[] = $this->msg( $value['hidden'] )->plain();
				} else {
					$changes[] = $this->msg( $value['unhidden'] )->plain();
				}
			}
		}
		$params[8] = $this->context->getLanguage()->listToText( $changes );

		return $params;
	}
}
