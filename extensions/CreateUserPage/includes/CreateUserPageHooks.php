<?php

class CreateUserPageHooks {

	/**
	 * Implements UserLoginComplete hook.
	 * See https://www.mediawiki.org/wiki/Manual:Hooks/UserLoginComplete
	 * Check for existence of user page if $wgCreateUserPage_OnLogin is true
	 *
	 * @param User &$user the user object that was create on login
	 * @param string &$inject_html any HTML to inject after the login success message
	 */
	public static function onUserLoginComplete( User &$user, &$inject_html ) {
		if ( $GLOBALS["wgCreateUserPage_OnLogin"] ) {
			self::checkForUserPage( $user );
		}
	}

	/**
	 * Implements OutputPageParserOutput hook.
	 * See https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageParserOutput
	 * Check for existence of user page if $wgCreateUserPage_OnLogin is fale
	 *
	 * @param OutputPage &$out the OutputPage object to which wikitext is added
	 * @param ParserOutput $parseroutput a PaerserOutput object
	 */
	public static function onOutputPageParserOutput( OutputPage &$out,
		ParserOutput $parseroutput ) {
		if ( !$GLOBALS["wgCreateUserPage_OnLogin"] ) {
			self::checkForUserPage( $out->getUser() );
		}
	}

	private static function checkForUserPage( User $user ) {
		if ( $GLOBALS["wgCreateUserPage_AutoCreateUser"] ) {
			wfDebugLog( 'CreateUserPage', 'AutoCreateUser: ' .
				$GLOBALS["wgCreateUserPage_AutoCreateUser"] );
			$autoCreateUser = User::newFromName( $GLOBALS["wgCreateUserPage_AutoCreateUser"] );
			if ( $autoCreateUser == false ) {
				wfDebugLog( 'CreateUserPage',
					'AutoCreateUser invalid, using logged in user instead.' );
				$autoCreateUser = $user;
			}
		} else {
			$autoCreateUser = $user;
		}
		$title = Title::newFromText( 'User:' . $user->mName );
		if ( $title !== null && !$title->exists() ) {
			$page = new WikiPage( $title );
			$pageContent = new WikitextContent( $GLOBALS['wgCreateUserPage_PageContent'] );
			$page->doEditContent( $pageContent, 'create user page', EDIT_NEW, false, $autoCreateUser );
		}
	}
}
