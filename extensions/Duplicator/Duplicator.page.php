<?php

/**
 * Special page class for the Duplicator extension
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @file
 * @ingroup Extensions
 * @author Rob Church <robchur@gmail.com>
 * @author Vitaliy Filippov <vitalif@mail.ru>
 */

class SpecialDuplicator extends SpecialPage {

	/**
	 * Title of the page we are duplicating
	 */
	protected $source = '';
	protected $sourceTitle = null;

	/**
	 * Title of the page we are saving to
	 */
	protected $dest = '';
	protected $destTitle = null;

	/**
	 * Whether or not we're duplicating the talk page, subpages, and the history
	 */
	protected $talk = true;
	protected $subpages = true;
	protected $history = true;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'Duplicator' );
	}

	public function doesWrites() {
		return true;
	}

	/**
	 * Main execution function
	 *
	 * @param $title Title passed to the page
	 */
	public function execute( $title ) {
		$this->setHeaders();
		$user = $this->getUser();
		$out = $this->getOutput();
		$request = $this->getRequest();

		# Check permissions
		if( !$user->isAllowed( 'duplicate' ) ) {
			throw new PermissionsError( 'duplicate' );
		}

		# Check for database lock
		$this->checkReadOnly();

		$this->setOptions( $request, $title );
		$out->addWikiMsg( 'duplicator-header' );
		$out->addHTML( $this->buildForm() );

		# If the token doesn't match or the form wasn't POSTed, stop
		if( !$request->wasPosted() || !$user->matchEditToken( $request->getVal( 'token' ), 'duplicator' ) )
			return;

		# Check we've got a valid source title
		if( !is_object( $this->sourceTitle ) ) {
			# Invalid source title
			$out->addWikiMsg( 'duplicator-source-invalid' );
			return;
		}

		# Check the source exists
		if( !$this->sourceTitle->exists() ) {
			# Source doesn't exist
			$out->addWikiMsg( 'duplicator-source-notexist', $this->source );
			return;
		}

		# Check we've got a valid destination title
		if( !is_object( $this->destTitle ) ) {
			# Invalid destination title
			$out->addWikiMsg( 'duplicator-dest-invalid' );
			return;
		}

		# Check the destination *does not* exist
		if( $this->destTitle->exists() ) {
			# Destination exists
			$out->addWikiMsg( 'duplicator-dest-exists', $this->destTitle->getPrefixedText() );
			return;
		}

		# Attempt to perform the main duplicate op. first
		$num = $this->duplicate( $this->sourceTitle, $this->destTitle, $this->history );
		if( $num ) {
			$success = Html::openElement( 'ul' ) .
				Html::rawElement( 'li',
					array(),
					$this->msg( 'duplicator-success',
						$this->sourceTitle->getPrefixedText(),
						$this->destTitle->getPrefixedText()
					)->parse() . ' ' .
					$this->msg( 'duplicator-success-revisions' )->numParams( $num )->text()
				);

			# If there are subpages and we've been asked to duplicate them, do so
			if ( $this->subpages ) {
				$success .= $this->duplicateSubpages( $this->sourceTitle, $this->destTitle, $this->history );
			}
			# If there is a talk page and we've been asked to duplicate it, do so
			if( $this->talk && $this->dealWithTalk() ) {
				$st = $this->sourceTitle->getTalkPage();
				$dt = $this->destTitle->getTalkPage();
				$num = $this->duplicate( $st, $dt, $this->history );
				if ( $num ) {
					$success .= Html::rawElement( 'li',
						array(),
						$this->msg( 'duplicator-success',
							$st->getPrefixedText(),
							$dt->getPrefixedText()
						 	)->parse() . ' ' .
						$this->msg( 'duplicator-success-revisions' )->numParams( $num )->text()
					);

					if ( $this->subpages ) {
						$success .= $this->duplicateSubpages( $st, $dt, $this->history );
					}
				} else {
					$success .= Html::rawElement( 'li', array(), $this->msg( 'duplicator-success-talknotcopied' )->text() );
				}
			}

			$success .= Html::closeElement( 'ul' );
			$out->addHTML( $success );

		} else {
			# Something went wrong, abort the entire operation
			$out->addWikiMsg( 'duplicator-failed' );
		}

	}

	/**
	 * Determine various options and attempt initialisation of objects
	 *
	 * @param $request WebRequest we're running off
	 * @param $title Title passed to the page
	 */
	protected function setOptions( &$request, $title ) {
		$source = $request->getText( 'source' );
		$this->source = $source ? $source : ( $title ? $title : '' );
		$this->sourceTitle = Title::newFromText( $this->source );
		$this->dest = $request->getText( 'dest', '' );
		$this->destTitle = Title::newFromText( $this->dest );
		$this->talk = $request->getCheck( 'talk' );
		$this->subpages = $request->getCheck( 'subpages' );
		$this->history = $request->getCheck( 'history' );
	}

	/**
	 * Should we allow the user to see the talk page option?
	 * Don't bother if there is no talk page, or we're duplicating one
	 *
	 * @return bool
	 */
	protected function dealWithTalk() {
		if( is_object( $this->sourceTitle ) ) {
			if( $this->sourceTitle->isTalkPage() )
				return false;
			$talk = $this->sourceTitle->getTalkPage();
			return $talk->exists();
		} else {
			# We can't determine, but it doesn't matter; either the user
			# hasn't hit the submit button, or we'll be throwing up a bad title error
			return true;
		}
	}

	/**
	 * Build a form for entering the source and destination titles
	 *
	 * @return string
	 */
	protected function buildForm() {
		$self = SpecialPage::getTitleFor( 'Duplicator' );
		$source = is_object( $this->sourceTitle ) ? $this->sourceTitle->getPrefixedText() : $this->source;
		$dest = is_object( $this->destTitle ) ? $this->destTitle->getPrefixedText() : $this->dest;
		// FIXME: replace this to use Html or/and Xml class to generate HTML
		$form  = '<form method="post" action="' . $self->getLocalUrl() . '">';
		$form .= '<fieldset><legend>' . $this->msg( 'duplicator-options' )->escaped() . '</legend>';
		$form .= '<table>';
		$form .= '<tr>';
		$form .= '<td><label for="source">' . $this->msg( 'duplicator-source' )->escaped() . '</label></td>';
		$form .= '<td>' . Xml::input( 'source', 40, $source, array( 'id' => 'source' ) ) . '</td>';
		$form .= '</tr><tr>';
		$form .= '<td><label for="dest">' . $this->msg( 'duplicator-dest' )->escaped() . '</label></td>';
		$form .= '<td>' . Xml::input( 'dest', 40, $dest, array( 'id' => 'dest' ) ) . '</td>';
		$form .= '</tr><tr>';
		$form .= '<td>&#160;</td>';
		$form .= '<td>' . Xml::checkLabel( $this->msg( 'duplicator-dotalk' )->text(), 'talk', 'talk', $this->talk ) . '</td>';
		$form .= '</tr><tr>';
		$form .= '<td>&#160;</td>';
		$form .= '<td>' . Xml::checkLabel( $this->msg( 'duplicator-dosubpages' )->text(), 'subpages', 'subpages', $this->subpages ) . '</td>';
		$form .= '</tr><tr>';
		$form .= '<td>&#160;</td>';
		$form .= '<td>' . Xml::checkLabel( $this->msg( 'duplicator-dohistory' )->text(), 'history', 'history', $this->history ) . '</td>';
		$form .= '</tr><tr>';
		$form .= '<td>&#160;</td>';
		$form .= '<td>' . Xml::submitButton( $this->msg( 'duplicator-submit' )->escaped() ) . '</td>';
		$form .= '</tr>';
		$form .= '</table>';
		$form .= Html::Hidden( 'token', $this->getUser()->getEditToken( 'duplicator' ) );
		$form .= '</fieldset></form>';
		return $form;
	}

	/**
	 * Duplicate subpages of $source to $dest
	 *
	 * @param $source Title to duplicate subpages for
	 * @param $dest Title to save subpages to
	 * @return bool
	 */
	protected function duplicateSubpages( $source, $dest, $includeHistory ) {
		global $wgLang;
		$subpages = $source->getSubpages();
		$len = strlen( $source->getText() );
		$ns = $dest->getNamespace();
		$dest = $dest->getText();
		$success = '';
		foreach ( $subpages as $sub ) {
			$destSub = Title::makeTitleSafe( $ns, $dest . substr( $sub->getText(), $len ) );
			if ( $destSub ) {
				if ( $destSub->exists() ) {
					$success .= Html::rawElement( 'li',
						array(),
						$this->msg( 'duplicator-failed-dest-exists',
							$sub->getPrefixedText(),
							$destSub->getPrefixedText()
							)->parse()
						);
				} else {
					$num = $this->duplicate( $sub, $destSub, $this->history );
					$success .= Html::rawElement( 'li',
						array(),
						$this->msg( 'duplicator-success',
							$sub->getPrefixedText(),
							$destSub->getPrefixedText()
							)->parse() . ' ' .
						$this->msg( 'duplicator-success-revisions',
							$wgLang->formatNum( $num )
							)->text()
					);
				}
			} else {
				# Bad title error can only occur here because of the destination title being too long
				$success .= Html::rawElement( 'li', array(), $this->msg( 'duplicator-failed-toolong', $sub->getPrefixedText() )->text() );
			}
		}
		return $success;
	}

	/**
	 * Duplicate one page to another, including full histories
	 * Does some basic error-catching, but not as much as the code above [should]
	 *
	 * @param $source Title to duplicate
	 * @param $dest Title to save to
	 * @param $includeHistory Whether to copy full article history
	 * @return bool
	 */
	protected function duplicate( $source, $dest, $includeHistory ) {
		global $wgBot, $wgDuplicatorRevisionLimit;
		if( !$source->exists() || $dest->exists() ) {
			return 0; # Source doesn't exist, or destination does
		}
		$user = $this->getUser();
		$dbw = wfGetDB( DB_MASTER );
		$dbw->startAtomic( __METHOD__ );
		$sid = $source->getArticleID();
		# Create an article representing the destination page and save it
		$destArticle = new Article( $dest );
		$aid = $destArticle->insertOn( $dbw );
		# Perform the revision duplication
		# An INSERT...SELECT here seems to fuck things up
		$res = $dbw->select( 'revision', '*', array( 'rev_page' => $sid ), __METHOD__,
			array( 'ORDER BY' => 'rev_timestamp DESC',
				'LIMIT' => $includeHistory ? $wgDuplicatorRevisionLimit : 1 )
		);
		$comment = '';
		if( $res && ( $count = $dbw->numRows( $res ) ) > 0 ) {
			while( $row = $dbw->fetchObject( $res ) ) {
				if( !$comment ) {
					$comment = $this->msg( 'duplicator-summary', $source->getPrefixedText(), $row->rev_id )->inContentLanguage()->text();
				}
				$values['rev_page'] = $aid;
				$values['rev_text_id'] = $row->rev_text_id;
				$values['rev_comment'] = $includeHistory ? $row->rev_comment : $comment;
				$values['rev_user'] = $row->rev_user;
				$values['rev_user_text'] = $row->rev_user_text;
				$values['rev_timestamp'] = $row->rev_timestamp;
				$values['rev_minor_edit'] = $row->rev_minor_edit;
				$values['rev_deleted'] = $row->rev_deleted;
				$dbw->insert( 'revision', $values, __METHOD__ );
			}
			$dbw->freeResult( $res );
		}
		# Update page record
		$latest = $dbw->selectField( 'revision', 'MAX(rev_id)', array( 'rev_page' => $aid ), __METHOD__ );
		$nr = Revision::newFromId( $latest );
		if ( $includeHistory ) {
			# Create a null revision with an explanation; do cache clearances, etc.
			$destArticle->updateRevisionOn( $dbw, $nr );
			$nr = Revision::newNullRevision( $dbw, $aid, $comment, true, $user );
			$nid = $nr->insertOn( $dbw );
		}
		$destArticle->updateRevisionOn( $dbw, $nr );
		$destArticle->doEditUpdates( $nr, $user, array( 'created' => true ) );
		WikiPage::onArticleCreate( $dest );
		$bot = $user->isAllowed( 'bot' );
		RecentChange::notifyNew( $nr->getTimestamp(), $dest, true, $user, $comment, $bot );
		$dest->invalidateCache();
		$dbw->endAtomic( __METHOD__ );
		return $count;
	}
}
