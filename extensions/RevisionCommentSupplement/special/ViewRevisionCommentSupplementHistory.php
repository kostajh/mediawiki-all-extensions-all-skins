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

$wgAutoloadClasses['RevisionCommentSupplement'] = __DIR__ . '/../RevisionCommentSupplement.class.php';
$wgAutoloadClasses['RevisionCommentSupplementHistory'] = __DIR__ . '/../RevisionCommentSupplement.class.php';

class ViewRevisionCommentSupplementHistory extends ContextSource {

	public $mPage;
	public $mParam;

	# from abstract class AbuseFilterView in AbuseFilterView.php into extension AbuseFilter
	/**
	 * @param SpecialPage $page
	 * @param array $params
	 */
	function __construct( $page, $param ) {
		$this->mPage = $page;
		$this->mParam = $param;
		$this->setContext( $this->mPage->getContext() );
	}

	function show() {
		$out = $this->getOutput();

		if ( !RevisionCommentSupplementSetting::getHistorySetting() ) {
			$out->setPageTitle( $this->msg( 'revcs-history-heading-error' )->plain() );
			$out->addHTML( $this->getErrorMessage( 'history-unuse' ) );
			return false;
		}

		$par = intval( $this->mParam );
		if ( $par == 0 ) {
			$out->setPageTitle( $this->msg( 'revcs-history-heading-error' )->plain() );
			$out->addHTML( $this->getErrorMessage( 'history-revision-id' ) );
			return false;
		}

		$request = $this->getRequest();

		if ( !RevisionCommentSupplement::isExistRow( $par ) ) {
			if (
				!$request->getCheck( 'unhide' ) ||
				!$this->getUser()->isAllowed( 'supplementcomment-restricted' ) ||
				!RevisionCommentSupplementHistory::isExistRow( $par )
			) {
				$out->setPageTitle( $this->msg( 'revcs-history-heading-error' )->plain() );
				$out->addHTML( $this->getErrorMessage( 'history-nosupplement' ) );
				return false;
			}
		}

		if ( !$request->getCheck( 'asc' ) ) {
			$request->setVal( 'desc', 1 );
		}

		$conds = array();
		$conds['rcsh_rev_id'] = $par;

		if (
			!$request->getCheck( 'unhide' ) ||
			!$this->getUser()->isAllowed( 'supplementcomment-restricted' )
		) {
			$conds[] = 'rcsh_hidden < ' . RevisionCommentSupplementHistory::HIDDEN_ROW;
		}

		$pager = new RevisionCommentSupplementHistoryTablePager( $this, $conds );

		$action = $this->getTitle()->getLocalURL();
		$form = Xml::openElement(
				'form',
				array( 'method' => 'get', 'action' => $action, 'id' => 'supplementarycommenthistory' )
			) .
			"\n";

		$form .= "<div>" .
			Xml::label( $this->msg( 'revcs-list-limit' )->plain(), 'limit' ) . '&#160;' .
			$pager->getLimitSelect() . '&#160;' .
			Xml::checkLabel(
				$this->msg( 'revcs-list-descending' )->plain(),
				'desc', 'desc', $request->getCheck( 'desc' )
			) . '&#160;' .
			Xml::submitButton(
				$this->msg( 'revcs-list-submit' )->plain(),
				array( 'accesskey' => 's' )
			) .
			"</div>\n";

		$form .= Xml::closeElement( 'form' );

		$output = Xml::fieldset( $this->msg( 'revcs-history-legend' )->plain(), $form );

		$output .=
			$pager->getNavigationBar() .
			$pager->getBody() .
			$pager->getNavigationBar();

		$out->setPageTitle(
			$this->msg( 'revcs-history-heading', $par )->numParams( $par )->plain()
		);
		$out->addHTML( $this->msg( 'revcs-history-desc' )->parseAsBlock() );
		$out->addHTML( $output );

		return true;
	}

	function getErrorMessage( $message ) {
		return Xml::element( 'p', array( 'class' => 'error' ),
				$this->msg( 'revcs-error', $this->msg( "revcs-error-{$message}" )->plain() )->plain()
			) .
			"\n";
	}
}

# from class AbuseFilterPager in AbuseFilterViewList.php into extension AbuseFilter
// Probably no need to autoload this class, as it will only be called from the class above.
class RevisionCommentSupplementHistoryTablePager extends TablePager {

	function __construct( $page, $conds = array() ) {
		$this->mPage = $page;
		$this->mConds = $conds;
		parent::__construct( $this->mPage->getContext() );
	}

	function getQueryInfo() {
		return array(
			'tables' => array( 'rev_comment_supp_history' ),
			'fields' => array(
				'*'
			),
			'conds' => $this->mConds,
		);
	}

	function getFieldNames() {
		static $headers = null;

		if ( !empty( $headers ) ) {
			return $headers;
		}

		$headers = array(
			'rcsh_id' => 'revcs-history-rcsh-id',
			'rcsh_user_text' => 'revcs-list-rcs-user-text',
			'rcsh_timestamp' => 'revcs-list-rcs-timestamp',
			'rcsh_supplement' => 'revcs-list-rcs-supplement',
			'rcsh_reason' => 'revcs-history-rcsh-reason'
		);

		foreach ( $headers as $row => $value ) {
			$headers[$row] = $this->msg( $value )->escaped();
		}

		return $headers;
	}

	function formatValue( $name, $value ) {
		switch( $name ) {
			case 'rcsh_id':
				$link = $value;
				if ( $this->getUser()->isAllowed( 'supplementcomment-restricted' ) ) {
					$hide = '(' . Linker::link(
						SpecialPage::getTitleFor( 'RevisionCommentSupplement', 'hidehistory/' . intval( $value ) ),
						$this->msg( 'rev-delundel' )->plain()
					) . ')';
					if ( RevisionCommentSupplementHistory::isHidden(
						'restricted', $this->mCurrentRow->rcsh_hidden
					) ) {
						$hide = '<strong>' . $hide . '</strong>';
					}
					$link .= $hide;
				}
				return $link;
			case 'rcsh_user_text':
				if ( RevisionCommentSupplementHistory::isHidden(
					'user', $this->mCurrentRow->rcsh_hidden
				) ) {
					return '<span class="history-deleted">'
						. $this->msg( 'revcs-history-hidden-user' )->escaped() . '</span>';
				} else {
					return Linker::userLink(
						$this->mCurrentRow->rcsh_user,
						$value
					) .
					Linker::userToolLinks(
						$this->mCurrentRow->rcsh_user,
						$value
					);
				}
			case 'rcsh_timestamp':
				return $this->getLanguage()->userTimeAndDate( $value, $this->getUser() );
			case 'rcsh_supplement':
				if ( RevisionCommentSupplementHistory::isHidden(
					'supplement', $this->mCurrentRow->rcsh_hidden
				) ) {
					return '<span class="history-deleted">'
						. $this->msg( 'revcs-history-hidden-supplement' )->escaped() . '</span>';
				} else {
					return htmlspecialchars( $value );
				}
			case 'rcsh_reason':
				if ( RevisionCommentSupplementHistory::isHidden(
					'reason', $this->mCurrentRow->rcsh_hidden
				) ) {
					return '<span class="history-deleted">'
						. $this->msg( 'revcs-history-hidden-reason' )->escaped() . '</span>';
				} else {
					return Linker::formatComment( $value );
				}
				break;
			default:
				throw new MWException( "Unknown row type $name!" );
		}
	}

	function getDefaultSort() {
		return 'rcsh_timestamp';
	}

	function isFieldSortable( $name ) {
		$sortable_fields = array(
			'rcsh_id',
			'rcsh_timestamp'
		);
		return in_array( $name, $sortable_fields );
	}
}
