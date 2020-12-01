<?php

/*
 * Copyright (c) 2017 The MITRE Corporation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

class EmailAuthorizationConfig extends SpecialPage {

	function __construct() {
		parent::__construct( 'EmailAuthorizationConfig',
			'emailauthorizationconfig' );
	}

	function execute( $par ) {
		if ( !$this->userCanExecute( $this->getUser() ) ) {
			$this->displayRestrictionError();
			return;
		}

		$request = $this->getRequest();
		$this->setHeaders();
		$this->getOutput()->addModuleStyles( 'ext.EmailAuthorization' );

		$search = self::searchEmail( $request->getText( 'searchemail' ) );
		self::addEmail( $request->getText( 'addemail' ) );
		self::revokeEmail( $request->getText( 'revokeemail' ) );
		self::showAuthorizedUsers( $request->getText( 'authoffset' ) );
		self::showAllUsers( $request->getText( 'alloffset' ) );

		$title = Title::newFromText( 'Special:' . __CLASS__ );
		$url = $title->getFullURL();

		$html = Html::openElement( 'p' )
			. Html::openElement( 'b' )
			. wfMessage( 'emailauthorization-config-instructions' )->parse()
			. Html::closeElement( 'b' )
			. Html::closeElement( 'p' );
		$this->getOutput()->addHtml( $html );

		$defaultAddEmail = '';
		if ( $search === null ) {
			$defaultAddEmail = trim( $request->getText( 'revokeemail' ) );
		} elseif ( !$search ) {
			$defaultAddEmail = trim( $request->getText( 'searchemail' ) );
		}
		self::showAddForm( $url, $defaultAddEmail );

		$defaultRevokeEmail = '';
		if ( $search === null ) {
			$defaultRevokeEmail = trim( $request->getText( 'addemail' ) );
		} elseif ( $search ) {
			$defaultRevokeEmail = trim( $request->getText( 'searchemail' ) );
		}
		self::showRevokeForm( $url, $defaultRevokeEmail );

		self::showSearchForm( $url );
		self::showAuthorizedUsersForm( $url );
		self::showAllUsersForm( $url );
	}

	private function displayMessage( $message ) {
		$html = Html::openElement( 'p', [
				'class' => 'emailauth-message'
			] )
			. $message
			. Html::closeElement( 'p' );
		$this->getOutput()->addHtml( $html );
	}

	private function validateEmail( $email ) {
		$email = mb_strtolower( htmlspecialchars( trim( $email ), ENT_QUOTES ) );
		if ( $email[0] === '@' ) {
			if ( filter_var( 'a' . $email, FILTER_VALIDATE_EMAIL ) ) {
				return $email;
			}
		} else {
			if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				return $email;
			}
		}
		return false;
	}

	private function searchEmail( $email ) {
		if ( $email === null || strlen( $email ) < 1 ) {
			return null;
		}
		$validatedemail = $this->validateEmail( $email );
		if ( $validatedemail !== false ) {
			if ( EmailAuthorization::isEmailAuthorized( $validatedemail ) ) {
				$this->displayMessage(
					wfMessage( 'emailauthorization-config-authorized', $validatedemail )
				);
				return true;
			} else {
				$this->displayMessage(
					wfMessage( 'emailauthorization-config-notauthorized', $validatedemail )
				);
				return false;
			}
		}
		$this->displayMessage(
			wfMessage( 'emailauthorization-config-invalidemail', $email )
		);
		return null;
	}

	private function addEmail( $email ) {
		if ( $email === null || strlen( $email ) < 1 ) {
			return;
		}
		$validatedemail = $this->validateEmail( $email );
		if ( $validatedemail !== false ) {
			if ( self::insertEmail( $validatedemail ) ) {
				$this->displayMessage(
					wfMessage( 'emailauthorization-config-added', $validatedemail )
				);
				Hooks::run( 'EmailAuthorizationAdd', [ $validatedemail ] );
			} else {
				$this->displayMessage(
					wfMessage( 'emailauthorization-config-alreadyauthorized', $validatedemail )
				);
			}
		} else {
			$this->displayMessage(
				wfMessage( 'emailauthorization-config-invalidemail', $email )
			);
		}
	}

	private function revokeEmail( $email ) {
		if ( $email === null || strlen( $email ) < 1 ) {
			return;
		}
		$validatedemail = $this->validateEmail( $email );
		if ( $validatedemail !== false ) {
			if ( self::deleteEmail( $validatedemail ) ) {
				$this->displayMessage(
					wfMessage( 'emailauthorization-config-revoked', $validatedemail )
				);
				Hooks::run( 'EmailAuthorizationRevoke', [ $validatedemail ] );
			} else {
				$this->displayMessage(
					wfMessage( 'emailauthorization-config-notauthorized', $validatedemail )
				);
			}
		} else {
			$this->displayMessage(
				wfMessage( 'emailauthorization-config-invalidemail', $email )
			);
		}
	}

	private function showAuthorizedUsers( $authoffset ) {
		if ( $authoffset === null || strlen( $authoffset ) === 0 ||
			!is_numeric( $authoffset ) || $authoffset < 0 ) {
			return;
		}

		$limit = 20;

		$emails = self::getAuthorizedEmails( $limit + 1, $authoffset );
		$next = false;

		if ( !$emails->valid() ) {
			$authoffset = 0;
			$emails = self::getAuthorizedEmails( $limit + 1, $authoffset );
			if ( !$emails->valid() ) {
				$this->displayMessage(
					wfMessage( 'emailauthorization-config-noauthfound' )
				);
				return;
			}
		}

		$wikitext = '{| class="wikitable emailauth-wikitable"' . PHP_EOL;
		$wikitext .=
			'!' . wfMessage( 'emailauthorization-config-label-email' ) . PHP_EOL;
		$wikitext .=
			'!' . wfMessage( 'emailauthorization-config-label-username' ) . PHP_EOL;
		$wikitext .=
			'!' . wfMessage( 'emailauthorization-config-label-realname' ) . PHP_EOL;
		$wikitext .=
			'!' . wfMessage( 'emailauthorization-config-label-userpage' ) . PHP_EOL;

		$index = 0;
		$more = false;
		foreach ( $emails as $email ) {
			if ( $index < $limit ) {
				$wikitext .= '|-' . PHP_EOL;
				$email_addr = htmlspecialchars( $email->email, ENT_QUOTES );
				if ( strlen( $email_addr ) > 1 && $email_addr[0] === '@' ) {
					$wikitext .= '|'
						. wfMessage( 'emailauthorization-config-value-domain', $email_addr )
						. PHP_EOL;
					$wikitext .= '| &nbsp;' . PHP_EOL;
					$wikitext .= '| &nbsp;' . PHP_EOL;
					$wikitext .= '| &nbsp;' . PHP_EOL;
				} else {
					$wikitext .= '|' . $email_addr . PHP_EOL;
					$users = self::getUserInfo( $email_addr );
					if ( !$users->valid() ) {
						$wikitext .= '| &nbsp;' . PHP_EOL;
						$wikitext .= '| &nbsp;' . PHP_EOL;
						$wikitext .= '| &nbsp;' . PHP_EOL;
					} else {
						$first = true;
						$wikitext .= '|';
						foreach ( $users as $user ) {
							$user_name = htmlspecialchars( $user->user_name, ENT_QUOTES );
							if ( $first ) {
								$first = false;
							} else {
								$wikitext .= '<br />';
							}
							$wikitext .= $user_name;
						}
						$wikitext .= PHP_EOL;
						$first = true;
						$wikitext .= '|';
						foreach ( $users as $user ) {
							$real_name = htmlspecialchars( $user->user_real_name, ENT_QUOTES );
							if ( $first ) {
								$first = false;
							} else {
								$wikitext .= '<br />';
							}
							$wikitext .= $real_name;
						}
						$wikitext .= PHP_EOL;
						$first = true;
						$wikitext .= '|';
						foreach ( $users as $user ) {
							$user_name = htmlspecialchars( $user->user_name, ENT_QUOTES );
							if ( $first ) {
								$first = false;
							} else {
								$wikitext .= '<br />';
							}
							$wikitext .= '[[User:' . $user_name . ']]';
						}
						$wikitext .= PHP_EOL;
					}
				}
				$index++;
			} else {
				$more = true;
			}
		}

		$wikitext .= '|}' . PHP_EOL;
		$this->getOutput()->addWikiTextAsInterface( $wikitext );

		if ( $authoffset > 0 || $more ) {
			$this->addTableNavigation( $authoffset, $more, $limit, 'authoffset' );
		}

		$html = Html::element( 'hr' );
		$this->getOutput()->addHtml( $html );
	}

	private function showAllUsers( $alloffset ) {
		if ( $alloffset === null || strlen( $alloffset ) === 0 ||
			!is_numeric( $alloffset ) || $alloffset < 0 ) {
			return;
		}

		$limit = 20;

		$users = self::getUsers( $limit + 1, $alloffset );
		$next = false;

		if ( !$users->valid() ) {
			$alloffset = 0;
			$users = self::getUsers( $limit + 1, $alloffset );
			if ( !$users->valid() ) {
				$this->displayMessage(
					wfMessage( 'emailauthorization-config-nousersfound' )
				);
				return;
			}
		}

		$wikitext = '{| class="wikitable emailauth-wikitable"' . PHP_EOL;
		$wikitext .=
			'!' . wfMessage( 'emailauthorization-config-label-email' ) . PHP_EOL;
		$wikitext .=
			'!' . wfMessage( 'emailauthorization-config-label-username' ) . PHP_EOL;
		$wikitext .=
			'!' . wfMessage( 'emailauthorization-config-label-realname' ) . PHP_EOL;
		$wikitext .=
			'!' . wfMessage( 'emailauthorization-config-label-userpage' ) . PHP_EOL;
		$wikitext .=
			'!' . wfMessage( 'emailauthorization-config-label-authorized' ) . PHP_EOL;

		$index = 0;
		$more = false;
		foreach ( $users as $user ) {
			if ( $index < $limit ) {
				$email = htmlspecialchars( $user->user_email, ENT_QUOTES );
				$user_name = htmlspecialchars( $user->user_name, ENT_QUOTES );
				$real_name = htmlspecialchars( $user->user_real_name, ENT_QUOTES );
				$email = htmlspecialchars( $user->user_email, ENT_QUOTES );
				$wikitext .= '|-' . PHP_EOL;
				$wikitext .= '|' . $email . PHP_EOL;
				$wikitext .= '|' . $user_name . PHP_EOL;
				$wikitext .= '|' . $real_name . PHP_EOL;
				$wikitext .= '|[[User:' . $user_name . ']]' . PHP_EOL;
				if ( EmailAuthorization::isEmailAuthorized( $email ) ) {
					$wikitext .= '| style="text-align:center;" | '
						. wfMessage( 'emailauthorization-config-value-yes' )
						. PHP_EOL;
				} else {
					$wikitext .= '| style="text-align:center;" | '
						. wfMessage( 'emailauthorization-config-value-no' )
						. PHP_EOL;
				}
				$index++;
			} else {
				$more = true;
			}
		}

		$wikitext .= '|}' . PHP_EOL;
		$this->getOutput()->addWikiTextAsInterface( $wikitext );

		if ( $alloffset > 0 || $more ) {
			$this->addTableNavigation( $alloffset, $more, $limit, 'alloffset' );
		}

		$html = Html::element( 'hr' );
		$this->getOutput()->addHtml( $html );
	}

	private function addTableNavigation( $offset, $more, $limit, $paramname ) {
		$title = Title::newFromText( 'Special:EmailAuthorizationConfig' );
		$url = $title->getFullURL();

		$html = Html::openElement( 'table', [
				'class' => 'emailauth-navigationtable'
			] )
			. Html::openElement( 'tr' )
			. Html::openElement( 'td' );

		if ( $offset > 0 ) {
			$prevurl = $url . '?' . $paramname . '=' . ( $offset - $limit );
			$html .= Html::openElement( 'a', [
					'href' => $prevurl,
					'class' => 'emailauth-button'
				] )
				. wfMessage( 'emailauthorization-config-button-previous' )
				. Html::closeElement( 'a' );
		}

		$html .= Html::closeElement( 'td' )
			. Html::openElement( 'td', [
				'style' => 'text-align:right;'
			] );

		if ( $more ) {
			$nexturl = $url . '?' . $paramname . '=' . ( $offset + $limit );
			$html .= Html::openElement( 'a', [
					'href' => $nexturl,
					'class' => 'emailauth-button'
				] )
				. wfMessage( 'emailauthorization-config-button-next' )
				. Html::closeElement( 'a' );
		}

		$html .= Html::closeElement( 'td' )
			. Html::closeElement( 'tr' )
			. Html::closeElement( 'table' );
		$this->getOutput()->addHtml( $html );
	}

	private function showSearchForm( $url ) {
		$html = Html::openElement( 'form', [
				'method' => 'post',
				'action' => $url,
				'id' => 'SearchEmail'
			] )
			. Html::openElement( 'fieldset' )
			. Html::element( 'legend', null,
				wfMessage( 'emailauthorization-config-legend-search' ) . ':' );
		list( $label, $input ) =
			Xml::inputLabelSep( wfMessage( 'emailauthorization-config-label-email' ) . ':',
				'searchemail', 'searchemail', 50 );
		$html .= $label . ' ' . $input . ' '
			. Xml::submitButton(
				wfMessage( 'emailauthorization-config-button-search' ),
				[ 'class' => 'emailauth-button' ] )
			. Html::closeElement( 'fieldset' )
			. Html::closeElement( 'form' );
		$this->getOutput()->addHtml( $html );
	}

	private function showAddForm( $url, $default ) {
		$html = Html::openElement( 'form', [
				'method' => 'post',
				'action' => $url,
				'id' => 'AddEmail'
			] )
			. Html::openElement( 'fieldset' )
			. Html::element( 'legend', null,
				wfMessage( 'emailauthorization-config-legend-add' ) . ':' );
		list( $label, $input ) =
			Xml::inputLabelSep( wfMessage( 'emailauthorization-config-label-email' ) . ':',
				'addemail', 'addemail', 50, $default );
		$html .= $label . ' ' . $input . ' '
			. Xml::submitButton(
				wfMessage( 'emailauthorization-config-button-add' ),
				[ 'class' => 'emailauth-button' ] )
			. Html::closeElement( 'fieldset' )
			. Html::closeElement( 'form' );
		$this->getOutput()->addHtml( $html );
	}

	private function showRevokeForm( $url, $default ) {
		$html = Html::openElement( 'form', [
				'method' => 'post',
				'action' => $url,
				'id' => 'RevokeEmail'
			] )
			. Html::openElement( 'fieldset' )
			. Html::element( 'legend', null,
				wfMessage( 'emailauthorization-config-legend-revoke' ) . ':' );
		list( $label, $input ) =
			Xml::inputLabelSep( wfMessage( 'emailauthorization-config-label-email' ) . ':',
				'revokeemail', 'revokeemail', 50, $default );
		$html .= $label . ' ' . $input . ' '
			. Xml::submitButton(
				wfMessage( 'emailauthorization-config-button-revoke' ),
				[ 'class' => 'emailauth-button' ] )
			. Html::closeElement( 'fieldset' )
			. Html::closeElement( 'form' );
		$this->getOutput()->addHtml( $html );
	}

	private function showAuthorizedUsersForm( $url ) {
		$html = Html::openElement( 'form', [
				'method' => 'post',
				'action' => $url,
				'id' => 'ShowAuthorizedUsers',
				'style' => 'display:inline;'
			] )
			. Html::hidden( 'authoffset', 0 )
			. Xml::submitButton(
				wfMessage( 'emailauthorization-config-button-showauth' ),
				[ 'class' => 'emailauth-button' ] )
			. Html::closeElement( 'form' );
		$this->getOutput()->addHtml( $html );
	}

	private function showAllUsersForm( $url ) {
		$html = Html::openElement( 'form', [
				'method' => 'post',
				'action' => $url,
				'id' => 'ShowAllUsers',
				'style' => 'display:inline;'
			] )
			. Html::hidden( 'alloffset', 0 )
			. Xml::submitButton(
				wfMessage( 'emailauthorization-config-button-showall' ),
				[ 'class' => 'emailauth-button' ] )
			. Html::closeElement( 'form' );
		$this->getOutput()->addHtml( $html );
	}

	private static function getAuthorizedEmails( $limit, $authoffset ) {
		$dbr = wfGetDB( DB_REPLICA );
		$emails = $dbr->select(
			'emailauth',
			[
				'email'
			],
			[],
			__METHOD__,
			[
				'ORDER BY' => 'email',
				'LIMIT' => $limit,
				'OFFSET' => $authoffset
			]
		);
		return $emails;
	}

	private static function getUsers( $limit, $alloffset ) {
		$dbr = wfGetDB( DB_REPLICA );
		$users = $dbr->select(
			'user',
			[
				'user_name',
				'user_real_name',
				'user_email'
			],
			[],
			__METHOD__,
			[
				'ORDER BY' => 'user_email',
				'LIMIT' => $limit,
				'OFFSET' => $alloffset
			]
		);
		return $users;
	}

	private static function getUserInfo( $email ) {
		$dbr = wfGetDB( DB_REPLICA );
		$users = $dbr->select(
			'user',
			[
				'user_name',
				'user_real_name'
			],
			[
				'user_email' => $email
			],
			__METHOD__,
			[
				'ORDER BY' => 'user_name',
			]
		);
		return $users;
	}

	private static function insertEmail( $email ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->upsert(
			'emailauth',
			[
				'email' => $email
			],
			[ 'email' ],
			[
				'email' => $email
			],
			__METHOD__
		);
		if ( $dbw->affectedRows() === 1 ) {
			return true;
		} else {
			return false;
		}
	}

	private static function deleteEmail( $email ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'emailauth',
			[
				'email' => $email
			],
			__METHOD__
		);
		if ( $dbw->affectedRows() === 1 ) {
			return true;
		} else {
			return false;
		}
	}
}
