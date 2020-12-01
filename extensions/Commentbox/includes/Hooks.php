<?php

namespace MediaWiki\Extension\Commentbox;

use MediaWiki\MediaWikiServices;
use OutputPage;
use Title;

/**
 * Hook handlers for the Commentbox extension.
 *
 * @package MediaWiki\Extension\Commentbox
 */
class Hooks {

	/**
	 * @param OutputPage &$op
	 * @param string &$text
	 * @return bool
	 */
	public static function onOutputPageBeforeHTML( OutputPage &$op, &$text ) {
		global $wgRequest,
			   $wgCommentboxNamespaces, $wgCommentboxRows,
			   $wgCommentboxColumns;

		$title = $op->getTitle();
		if ( !$title->exists() )
			return true;

		if ( class_exists( 'MediaWiki\Permissions\PermissionManager' ) ) {
			// MW 1.33+
			if ( !MediaWikiServices::getInstance()
				->getPermissionManager()
				->userCan( 'edit', $op->getUser(), $title )
			) {
				return true;
			}
		} else {
			if ( !$title->userCan( 'edit', true ) )
				return true;
		}

		if ( !array_key_exists( $title->getNamespace(), $wgCommentboxNamespaces )
			|| !$wgCommentboxNamespaces[ $title->getNamespace() ] )
			return true;

		$action = $wgRequest->getVal( 'action', 'view' );
		if ( !( $action == 'view' || $action == 'purge' || $action == 'submit' ) )
			return true;
		if ( $wgRequest->getCheck( 'wpPreview' )
			|| $wgRequest->getCheck( 'wpLivePreview' )
			|| $wgRequest->getCheck( 'wpDiff' ) )
			return true;
		if ( !is_null( $wgRequest->getVal( 'preview' ) ) )
			return true;
		if ( !is_null( $wgRequest->getVal( 'diff' ) ) )
			return true;

		$newaction = htmlspecialchars( Title::newFromText( 'AddComment', NS_SPECIAL )->getFullURL() );
		$name = '';
		if ( !$op->getUser()->isLoggedIn() ) {
			$namecomment = $op->msg( 'commentbox-name-explanation' )->parse();
			$namelabel = $op->msg( 'commentbox-name' )->parse();
			$name = '<br />' . $namelabel;
			$name .= ' <input name="wpAuthor" tabindex="2" type="text" size="30" maxlength="50" /> ';
			$name .= $namecomment;
		}
		$inhalt = $op->msg( 'commentbox-prefill' )->plain();
		$save = $op->msg( 'commentbox-savebutton' )->parse();
		$texttitle = htmlspecialchars( Title::makeName( $title->getNamespace(), $title->getText() ) );

		$intro = $op->msg( 'commentbox-intro' )->parse();

		$text .= <<<END
	<form id="commentform" name="commentform" method="post"
              action="$newaction" enctype="multipart/form-data">
	$intro
	<textarea tabindex='1' accesskey="," name="wpComment" id="wpComment"
	          rows='$wgCommentboxRows' cols='$wgCommentboxColumns'
		  >$inhalt</textarea>
	$name
	<br />
	<input type="hidden" name="wpPageName" value="$texttitle" />
	<input id="wpSave" name="wpSave" type="submit" tabindex="3" value="$save"
	       accesskey="s" title="$save [alt-s]" />
	</form>
END;
		return true;
	}
}
