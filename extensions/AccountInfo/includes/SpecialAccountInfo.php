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

namespace MediaWiki\AccountInfo;

use SpecialPage;
use UserNotLoggedIn;

/**
 * @todo figure out if this special page should require a token
 */
class SpecialAccountInfo extends SpecialPage {
	public function __construct() {
		parent::__construct( 'AccountInfo' );
	}

	/**
	 * @param string $xff
	 * @return string
	 */
	private function formatXFF( $xff ) {
		if ( !$xff ) {
			$xff = $this->msg( 'accountinfo-none' )->text();
		}

		return $xff;
	}

	/**
	 * @param string $ts
	 * @return string
	 */
	private function formatTime( $ts ) {
		return $this->getLanguage()->formatTimePeriod( $ts,
			[ 'avoid' => 'avoidminutes', 'noabbrevs' => true ]
		);
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $par ) {
		$user = $this->getUser();
		$out = $this->getOutput();
		$req = $this->getRequest();

		if ( !$user->isLoggedIn() ) {
			throw new UserNotLoggedIn;
		}

		$this->setHeaders();
		$this->outputHeader();
		$this->checkPermissions();
		$out->addModuleStyles( 'ext.AccountInfo.special' );

		$lookup = new InfoLookup();
		$info = $lookup->getUserInfo( $user );
		$out->addHTML( $this->msg( 'accountinfo-length' )
			->params( $this->formatTime( $info['length'] ) )
			->parseAsBlock()
		);

		$outRows = [];
		foreach ( $info['rows'] as $row ) {
			list( $ts, $ip, $ua, $xff ) = $row;
			$outRows[] = [
				$ts ? $this->getLanguage()->formatExpiry( $ts ) : '',
				$ip ?: '',
				'mw-accountinfo-useragent' => $ua ? htmlspecialchars( $ua ) : '',
				$xff ? $this->formatXFF( $xff ) : '',
			];
		}
		// Put current info on top.
		$outRows = array_merge( [ 'mw-accountinfo-current' => [
			$this->msg( 'accountinfo-now' )->parse(),
			$req->getIP(),
			'mw-accountinfo-useragent' => htmlspecialchars( $lookup->getUserAgent( $req ) ),
			$this->formatXFF( $lookup->getXFF( $req ) ),
		] ], $outRows );
		$out->addHTML( TableBuilder::buildTable( $outRows, [
			$this->msg( 'accountinfo-ts' )->parse(),
			$this->msg( 'accountinfo-ip' )->parse(),
			$this->msg( 'accountinfo-ua' )->parse(),
			$this->msg( 'accountinfo-xff' )->parse(),
		] ) );
	}

}
