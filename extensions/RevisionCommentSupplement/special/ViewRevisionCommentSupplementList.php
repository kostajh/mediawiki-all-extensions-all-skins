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

class ViewRevisionCommentSupplementList extends ContextSource {

	public $mPage;
	public $mParam;
	protected $dbr;

	protected $exOperators = array(
		'and-over' => '>=',
		'and-under' => '<=',
		'equal' => '=',
		'not-equal' => '!=',
		'over' => '>',
		'under' => '<'
	);

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
		$this->dbr = wfGetDB( DB_REPLICA );

		$request = $this->getRequest();
		$conds = array();
		$par = intval( $this->mParam );

		$exsupplement = $request->getText( 'exsupplement' );
		$exprop = $request->getText( 'exprop' );
		$excomp = $request->getText( 'excomp' );
		$exoffset = $request->getText( 'exoffset' );

		if ( $exsupplement == 'notempty' ) {
			$conds[] = "rcs_supplement != ''";
		} elseif ( $exsupplement == 'empty' ) {
			$conds[] = "rcs_supplement = ''";
		}

		if ( $exprop && $excomp && $exoffset ) {
			if ( $exprop == 'rcs_user_text' ) {
				$exoffset = ucfirst( $exoffset );
			}
			$cond = $this->getExtendedCond( $exprop, $excomp, $exoffset );
			if ( $cond != '' ) {
				$conds[] = $cond;
			}
		} elseif ( $par > 0 ) {
			$conds[] = $this->getExtendedCond( 'rcs_rev_id', 'equal', $par );
			$request->setVal( 'limit', 1 );
		}

		$pager = new RevisionCommentSupplementListTablePager( $this, $conds );

		$action = $this->getTitle()->getLocalURL();
		$form = Xml::openElement(
				'form',
				array( 'method' => 'get', 'action' => $action, 'id' => 'supplementarycommentlist' )
			) .
			"\n";

		$form .= "<div>" .
			Xml::label( $this->msg( 'revcs-list-extended-property' )->plain(), 'exprop' ) . '&#160;' .
			Xml::tags(
				'select', array( 'id' => 'exprop', 'name' => 'exprop' ),
				"\n" .
				Xml::option(
					'',
					'',
					$exprop == ''
				) . "\n" .
				Xml::option(
					$this->msg( 'revcs-list-rcs-rev-id' )->plain(),
					'rcs_rev_id',
					$exprop == 'rcs_rev_id'
				) . "\n" .
				Xml::option(
					$this->msg( 'revcs-list-rcs-user-text' )->plain(),
					'rcs_user_text',
					$exprop == 'rcs_user_text'
				) . "\n" .
				Xml::option(
					$this->msg( 'revcs-list-rcs-timestamp' )->plain(),
					'rcs_timestamp',
					$exprop == 'rcs_timestamp'
				) . "\n"
			) .
			"</div>\n";

		$form .= "<div>" .
			Xml::label( $this->msg( 'revcs-list-extended-comparison' )->plain(), 'excomp' ) . '&#160;' .
			Xml::tags(
				'select', array( 'id' => 'excomp', 'name' => 'excomp' ),
				"\n" .
				Xml::option(
					'',
					'',
					$excomp == ''
				) . "\n" .
				Xml::option(
					$this->msg( 'revcs-list-extended-comparison-over' )->plain(),
					'over',
					$excomp == 'over'
				) . "\n" .
				Xml::option(
					$this->msg( 'revcs-list-extended-comparison-and-over' )->plain(),
					'and-over',
					$excomp == 'and-over'
				) . "\n" .
				Xml::option(
					$this->msg( 'revcs-list-extended-comparison-equal' )->plain(),
					'equal',
					$excomp == 'equal'
				) . "\n" .
				Xml::option(
					$this->msg( 'revcs-list-extended-comparison-and-under' )->plain(),
					'and-under',
					$excomp == 'and-under'
				) . "\n" .
				Xml::option(
					$this->msg( 'revcs-list-extended-comparison-under' )->plain(),
					'under',
					$excomp == 'under'
				) . "\n" .
				Xml::option(
					$this->msg( 'revcs-list-extended-comparison-not-equal' )->plain(),
					'not-equal',
					$excomp == 'not-equal'
				) . "\n"
			) .
			"</div>\n";

		$form .= "<div>" .
			Xml::inputLabel(
				$this->msg( 'revcs-list-extended-offset' )->plain(), 'exoffset', 'exoffset', 60,
				$exoffset,
				array( 'maxlength' => '255' )
			) .
			"</div>\n";

		$form .= "<div>" .
			Xml::label( $this->msg( 'revcs-list-extended-supplement' )->plain(), 'exsupplement' ) . '&#160;' .
			Xml::tags(
				'select', array( 'id' => 'exsupplement', 'name' => 'exsupplement' ),
				"\n" .
				Xml::option(
					$this->msg( 'revcs-list-extended-supplement-all' )->plain(),
					'',
					$exsupplement == ''
				) . "\n" .
				Xml::option(
					$this->msg( 'revcs-list-extended-supplement-empty' )->plain(),
					'empty',
					$exsupplement == 'empty'
				) . "\n" .
				Xml::option(
					$this->msg( 'revcs-list-extended-supplement-notempty' )->plain(),
					'notempty',
					$exsupplement == 'notempty'
				) . "\n"
			) .
			"</div>\n";

		$form .= "<div>" .
			Xml::label( $this->msg( 'revcs-list-limit' )->plain(), 'limit' ) . '&#160;' .
			$pager->getLimitSelect() .
			"</div>\n";

		$sort = $request->getText( 'sort' );
		$form .= "<div>" .
			Xml::label( $this->msg( 'revcs-list-sort' )->plain(), 'sort' ) . '&#160;' .
			Xml::tags(
				'select', array( 'id' => 'sort', 'name' => 'sort' ),
				"\n" .
				Xml::option(
					$this->msg( 'revcs-list-rcs-rev-id' )->plain(),
					'rcs_rev_id',
					( $sort == '' || $sort == 'rcs_rev_id' )
				) . "\n" .
				Xml::option(
					$this->msg( 'revcs-list-rcs-user-text' )->plain(),
					'rcs_user_text',
					$sort == 'rcs_user_text'
				) . "\n" .
				Xml::option(
					$this->msg( 'revcs-list-rcs-timestamp' )->plain(),
					'rcs_timestamp',
					$sort == 'rcs_timestamp'
				) . "\n"
			) . '&#160;' .
			Xml::checkLabel(
				$this->msg( 'revcs-list-descending' )->plain(),
				'desc', 'desc', $request->getCheck( 'desc' )
			) .
			"</div>\n";

		$form .= "<div>" .
			Xml::submitButton(
				$this->msg( 'revcs-list-submit' )->plain(),
				array( 'accesskey' => 's' )
			) . '&#160;' .
			Xml::element(
				'input',
				array( 'type' => 'reset', 'value' => $this->msg( 'revcs-list-reset' )->plain() )
			) .
			"</div>\n";

		$form .= Xml::closeElement( 'form' );

		$output = Xml::fieldset( $this->msg( 'revcs-list-legend' )->plain(), $form );

		$output .=
			$pager->getNavigationBar() .
			$pager->getBody() .
			$pager->getNavigationBar();

		$this->getOutput()->setPageTitle( $this->msg( 'revcs-list-heading' )->plain() );
		$this->getOutput()->addHTML( $this->msg( 'revcs-list-desc' )->parseAsBlock() );
		$this->getOutput()->addHTML( $output );
	}

	function getExtendedCond( $exProperty, $exComparison, $exOffset ) {
		if (
			$exProperty == 'rcs_rev_id'
			|| $exProperty == 'rcs_user_text'
			|| $exProperty == 'rcs_timestamp'
		) {
			if ( isset( $this->exOperators[$exComparison] ) ) {
				# from IndexPager::reallyDoQuery in Pager.php
				$exop = $this->exOperators[$exComparison];
				return $exProperty . $exop . $this->dbr->addQuotes( $exOffset );
			}
		}
		return '';
	}
}

# from class AbuseFilterPager in AbuseFilterViewList.php into extension AbuseFilter
// Probably no need to autoload this class, as it will only be called from the class above.
class RevisionCommentSupplementListTablePager extends TablePager {

	function __construct( $page, $conds = array() ) {
		$this->mPage = $page;
		$this->mConds = $conds;
		parent::__construct( $this->mPage->getContext() );
	}

	function getQueryInfo() {
		return array(
			'tables' => array( 'rev_comment_supp' ),
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
			'rcs_rev_id' => 'revcs-list-rcs-rev-id',
			'rcs_user_text' => 'revcs-list-rcs-user-text',
			'rcs_timestamp' => 'revcs-list-rcs-timestamp',
			'rcs_supplement' => 'revcs-list-rcs-supplement',
		);

		foreach ( $headers as $row => $value ) {
			$headers[$row] = $this->msg( $value )->escaped();
		}

		return $headers;
	}

	function formatValue( $name, $value ) {
		switch( $name ) {
			case 'rcs_rev_id':
				$link = Linker::linkKnown(
					$this->mPage->mPage->getTitleFor( 'Permalink', intval( $value ) ),
					$this->getLanguage()->formatNum( intval( $value ) )
				) . ' (';
				if ( $this->getUser()->isAllowed( 'supplementcomment-restricted' ) ) {
					$link .= Linker::linkKnown(
						$this->mPage->mPage->getTitleFor( 'RevisionCommentSupplement', 'edit/' . intval( $value ) ),
						$this->msg( 'revcs-list-rcs-rev-id-edit' )->plain()
					) . ' | ';
				}
				$link .= Linker::linkKnown(
					$this->mPage->mPage->getTitleFor( 'Log' ),
					$this->msg( 'revcs-list-rcs-rev-id-log' )->plain(),
					array(),
					array( 'page' => "Special:RevisionCommentSupplement/{$value}" )
				) . ')';
				return $link;
			case 'rcs_user_text':
				return Linker::userLink(
					$this->mCurrentRow->rcs_user,
					$value
				) .
				Linker::userToolLinks(
					$this->mCurrentRow->rcs_user,
					$value
				);
			case 'rcs_timestamp':
				if ( RevisionCommentSupplementSetting::getHistorySetting() ) {
					return Linker::linkKnown(
						$this->mPage->mPage->getTitleFor( 'RevisionCommentSupplementList', 'history/' . $this->mCurrentRow->rcs_rev_id ),
						$this->getLanguage()->userTimeAndDate( $value, $this->getUser() )
					);
				} else {
					return $this->getLanguage()->userTimeAndDate( $value, $this->getUser() );
				}
			case 'rcs_supplement':
				return htmlspecialchars( $value );
				break;
			default:
				throw new MWException( "Unknown row type $name!" );
		}
	}

	function getDefaultSort() {
		return 'rcs_rev_id';
	}

	function getRowClass( $row ) {
		if ( $row->rcs_supplement ) {
			return 'revcs-list-supplement-set';
		} else {
			return 'revcs-list-supplement-empty';
		}
	}

	function isFieldSortable( $name ) {
		$sortable_fields = array(
			'rcs_rev_id',
			'rcs_user_text',
			'rcs_timestamp'
		);
		return in_array( $name, $sortable_fields );
	}
}
