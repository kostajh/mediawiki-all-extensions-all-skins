<?php

use MediaWiki\MediaWikiServices;

class SpecialUiFeedback extends SpecialPage {

	function __construct() {
		parent::__construct( 'UiFeedback' );
	}

	/**
	 * @param string|null $par
	 */
	function execute( $par ) {
		$request = $this->getRequest();
		$output  = $this->getOutput();
		$this->setHeaders();

		$user = $this->getUser();

		$title = SpecialPage::getTitleFor( 'UiFeedback' );

		/* Rights to read and write */
		$can_read  = $user->isAllowed( 'read_uifeedback' );
		$can_write = $user->isAllowed( 'write_uifeedback' );

		$output_text = '';

		if ( !$can_read ) {
			$output_text = $this->msg( 'ui-feedback-special-no-permission' )->escaped();
		} else { /* can read */
			/* Arrays for Output */
			$importance_array = [ '', '--', '-', '0', '+', '++' ];
			$happened_array   = [ '', $this->msg( 'ui-feedback-special-happened-1' )->escaped(), $this->msg( 'ui-feedback-special-happened-2' )->escaped(), $this->msg( 'ui-feedback-special-happened-3' )->escaped(), $this->msg( 'ui-feedback-special-happened-4' )->escaped() ];
			$bool_array       = [ $this->msg( 'ui-feedback-special-no' )->escaped(), $this->msg( 'ui-feedback-special-yes' )->escaped() ];
			$status_array     = [ $this->msg( 'ui-feedback-special-status-open' )->escaped(), $this->msg( 'ui-feedback-special-status-in-review' )->escaped(), $this->msg( 'ui-feedback-special-status-closed' )->escaped(), $this->msg( 'ui-feedback-special-status-declined' )->escaped() ];

			/* get Request-data */
			$id = $request->getInt( 'id', -1 );

			$filter_status     = $request->getIntArray( 'filter_status', [ 0, 1 ] ); // 0: open, 1: in review, 2: closed, 3: declined
			$filter_type       = $request->getIntArray( 'filter_type', [ 0, 1 ] ); // 0: Questionnaire, 1: Screenshot
			$filter_importance = $request->getIntArray( 'filter_importance', [ 0, 1, 2, 3, 4, 5 ] ); // 0: no, 1: -2, 2: -1, 3: 0, 4: 1, 5: 2

			$order         = ' uif_created DESC';
			$only_one_item = false;
			if ( $id >= 0 ) {
				$only_one_item = true;
				$order         = 'uif_created ASC';
			}

			$conditions = [];

			/* connect to the DB*/
			$dbr = wfGetDB( DB_REPLICA );
			/* get the rows from uifeedback-table */
			if ( $id !== -1 ) {
				$conditions[ 'uif_id' ] = $id;
			} else {
				/* if no checkbox is selected filter for -1 (which will not be found -> empty result) */
				if ( count( $filter_status ) ) {
					$conditions[ 'uif_status' ] = $filter_status;
				} else {
					$conditions[ 'uif_status' ] = '-1';
				}

				if ( count( $filter_status ) ) {
					$conditions[ 'uif_type' ] = $filter_type;
				} else {
					$conditions[ 'uif_type' ] = '-1';
				}

				if ( count( $filter_status ) ) {
					$conditions[ 'uif_importance' ] = $filter_importance;
				} else {
					$conditions[ 'uif_importance' ] = '-1';
				}

			}
			$res = $dbr->select(
				[ 'uifeedback' ],
				[
					'uif_id',
					'uif_url',
					'uif_type',
					'uif_created',
					'uif_task',
					'uif_done', // Have you been able to carry out your intended task successfully?
					'uif_text1', // some more details
					'uif_importance',
					'uif_happened',
					'uif_username',
					'uif_useragent',
					'uif_notify',
					'uif_status',
					'uif_comment'
				],
				$conditions,
				__METHOD__,
				[ 'ORDER BY' => $order ]
			);
			/* number of rows selected */
			$count = $res->numRows();

			/* add table with filters */
			if ( !$only_one_item && $count > 0 ) {
				/* Filter */
				$output_text .= '<div class="filters">';
				$output_text .= '<h2>' . $this->msg( 'ui-feedback-special-filter' )->escaped() . '</h2>';
				$output_text .= '<form action="' . $title->getFullURL() . '" method="GET">';
				$output_text .= '<table style="border-collapse: separate;border-spacing: 10px 5px;">';
				$output_text .= '<tr>';
				$output_text .= '<th>' . $this->msg( 'ui-feedback-special-table-head-status' )->escaped() . '</th>';
				$output_text .= '<th>' . $this->msg( 'ui-feedback-special-table-head-importance' )->escaped() . '</th>';
				$output_text .= '<th>' . $this->msg( 'ui-feedback-special-table-head-type' )->escaped() . '</th>';
				$output_text .= '</tr>';
				$output_text .= '<tr>';
				$output_text .= '<td><label><input type="checkbox" name="filter_status" value="0" ' . ( ( in_array( '0', $filter_status ) ) ? 'checked' : '' ) . '>' . $this->msg( 'ui-feedback-special-status-open' )->escaped() . '</label></td>';
				$output_text .= '<td><label><input type="checkbox" name="filter_importance" value="0" ' . ( ( in_array( '0', $filter_importance ) ) ? 'checked' : '' ) . '>' . $this->msg( 'ui-feedback-special-undefined' )->escaped() . '</label></td>';
				$output_text .= '<td><label><input type="checkbox" name="filter_type" value="1" ' . ( ( in_array( '1', $filter_type ) ) ? 'checked' : '' ) . '>' . $this->msg( 'ui-feedback-special-type-screenshot' )->escaped() . '</label></td>';
				$output_text .= '</tr>';
				$output_text .= '<tr>';
				$output_text .= '<td><label><input type="checkbox" name="filter_status" value="1" ' . ( ( in_array( '1', $filter_status ) ) ? 'checked' : '' ) . '>' . $this->msg( 'ui-feedback-special-status-in-review' )->escaped() . '</label></td>';
				$output_text .= '<td><label><input type="checkbox" name="filter_importance" value="1" ' . ( ( in_array( '1', $filter_importance ) ) ? 'checked' : '' ) . '>--</label></td>';
				$output_text .= '<td><label><input type="checkbox" name="filter_type" value="0" ' . ( ( in_array( '0', $filter_type ) ) ? 'checked' : '' ) . '>' . $this->msg( 'ui-feedback-special-type-questionnaire' )->escaped() . '</label></td>';
				$output_text .= '</tr>';
				$output_text .= '<tr>';
				$output_text .= '<td><label><input type="checkbox" name="filter_status" value="3" ' . ( ( in_array( '3', $filter_status ) ) ? 'checked' : '' ) . '>' . $this->msg( 'ui-feedback-special-status-declined' )->escaped() . '</label></td>';
				$output_text .= '<td><label><input type="checkbox" name="filter_importance" value="2" ' . ( ( in_array( '2', $filter_importance ) ) ? 'checked' : '' ) . '>-</label></td>';
				$output_text .= '<td>&nbsp;</td>';
				$output_text .= '</tr>';
				$output_text .= '<tr>';
				$output_text .= '<td><label><input type="checkbox" name="filter_status" value="2" ' . ( ( in_array( '2', $filter_status ) ) ? 'checked' : '' ) . '>' . $this->msg( 'ui-feedback-special-status-closed' )->escaped() . '</label></td>';
				$output_text .= '<td><label><input type="checkbox" name="filter_importance" value="3" ' . ( ( in_array( '3', $filter_importance ) ) ? 'checked' : '' ) . '>0</label></td>';
				$output_text .= '<td>&nbsp;</td>';
				$output_text .= '</tr>';
				$output_text .= '<tr>';
				$output_text .= '<td>&nbsp;</td>';
				$output_text .= '<td><label><input type="checkbox" name="filter_importance" value="4" ' . ( ( in_array( '4', $filter_importance ) ) ? 'checked' : '' ) . '>+</label></td>';
				$output_text .= '<td>&nbsp;</td>';
				$output_text .= '</tr>';
				$output_text .= '<tr>';
				$output_text .= '<td>&nbsp;</td>';
				$output_text .= '<td><label><input type="checkbox" name="filter_importance" value="5" ' . ( ( in_array( '5', $filter_importance ) ) ? 'checked' : '' ) . '>++</label></td>';
				$output_text .= '<td style="text-align:right;"><input type="button" value="filter" id="ui-feedback-set-filter-button"></td>';
				$output_text .= '</tr>';
				$output_text .= '</table>';
				$output_text .= '</form>';
				$output_text .= '</div>'; // end filters

				/* list of top5 contributers */
				$output_text .= '<div class="stats">';
				$output_text .= '<h2>' . $this->msg( 'ui-feedback-special-stats' )->escaped() . '</h2>';
				$output_text .= '<div style="float:left;">';
				/* get name and number of closed feedback-posts */
				$res_stats = $dbr->select(
					[ 'uifeedback' ],
					[ 'uif_username', 'count(uif_id) as count' ],
					'uif_username != \'\' and uif_status = \'2\'', /* no anon users, closed feedback */
					__METHOD__,
					[ 'GROUP BY' => 'uif_username', 'ORDER BY' => 'count DESC', 'LIMIT' => '5' ]
				);
				$output_text .= $this->msg( 'ui-feedback-special-top5-users' )->escaped();
				$output_text .= '<table style="text-align:right;border-collapse: separate;border-spacing: 10px 5px;">';
				/* add rows to table */
				foreach ( $res_stats as $row ) {
					$output_text .= Xml::tags( 'tr', null, Html::element( 'td', [], $row->uif_username ) . Html::element( 'td', [ 'style' => "text-align:right;" ], $row->count ) );
				}
				$output_text .= '</table>';
				$output_text .= '</div>'; /* end users */

				$output_text .= '</div>'; /* end stats */

			} elseif ( $count > 0 ) {
				/* add page-navigation */
				$output_text .= '<div class="page_navi">';
				/* previous */
				if ( $id > 1 ) {
					$output_text .= Html::element( 'a', [ 'href' => $title->getFullURL( [ 'id' => ( $id - 1 ) ] ) ], $this->msg( 'ui-feedback-special-navi-previous' )->escaped() );
					$output_text .= "&nbsp;|&nbsp;";
				}
				/* next */
				$output_text .= Html::element( 'a', [ 'href' => $title->getFullURL( [ 'id' => ( $id + 1 ) ] ) ], $this->msg( 'ui-feedback-special-navi-next' )->escaped() );
				$output_text .= "&nbsp;|&nbsp;";
				/* all */
				$output_text .= Html::element( 'a', [ 'href' => $title->getFullURL( [ 'id' => ( -1 ) ] ) ], $this->msg( 'ui-feedback-special-navi-all' )->escaped() );

				$output_text .= '</div>';
				/* end page-navi */
			}

			/* create the table */
			if ( $count > 0 ) {
				/* Browser and Operating-System-Icons */
				$internetexplorer_icon = '<div class="icon ie">1</div>';
				$firefox_icon          = '<div class="icon ff">2</div>';
				$chrome_icon           = '<div class="icon ch">3</div>';
				$safari_icon           = '<div class="icon sf">4</div>';
				$opera_icon            = '<div class="icon op">5</div>';
				$window_icon           = '<div class="icon win">1</div>';
				$mac_icon              = '<div class="icon mac">2</div>';
				$linux_icon            = '<div class="icon lin">3</div>';

				$output_text .= Html::element( 'h2', [ 'style' => 'clear:both;' ], $this->msg( 'ui-feedback-special-feedback' )->escaped() );
				/* if more than one item is visible add "found 37 items:" */
				if ( !$only_one_item ) {
					$output_text .= $this->msg( 'ui-feedback-special-found', $count )->escaped();
				}

				/* Result-Table */
				$output_text .= '<table class="wikitable sortable">';
				$output_text .= '<thead>';
				$output_text .= '<tr>';
				/* Headlines */
				/* id */
				$output_text .= Html::element( 'th', [ 'scope' => 'col', 'class' => 'headerSort' ], $this->msg( 'ui-feedback-special-table-head-id' )->escaped() );
				/* username */
				$output_text .= Html::element( 'th', [ 'scope' => 'col', 'class' => 'headerSort' ], $this->msg( 'ui-feedback-special-table-head-username' )->escaped() );
				/* browser */
				$output_text .= '<th scope="col" class="headerSort"></th>';
				/* OS */
				if ( $can_write ) {
					$output_text .= '<th scope="col" class="headerSort"></th>';
				}
				/* time */
				if ( $only_one_item ) {
					$output_text .= Html::element( 'th', [ 'scope' => 'col', 'class' => 'headerSort' ], $this->msg( 'ui-feedback-special-table-head-time' )->escaped() );
				}
				/* type */
				$output_text .= Html::element( 'th', [ 'scope' => 'col', 'class' => 'headerSort' ], $this->msg( 'ui-feedback-special-table-head-type' )->escaped() );
				/* importance */
				$output_text .= Html::element( 'th', [ 'scope' => 'col', 'class' => 'headerSort' ], $this->msg( 'ui-feedback-special-table-head-importance' )->escaped() );
				/* happened */
				$output_text .= Html::element( 'th', [ 'scope' => 'col', 'class' => 'headerSort' ], $this->msg( 'ui-feedback-special-table-head-happened' )->escaped() );
				/* task */
				$output_text .= Html::element( 'th', [ 'scope' => 'col', 'class' => 'headerSort' ], $this->msg( 'ui-feedback-special-table-head-task' )->escaped() );
				/* done */
				$output_text .= Html::element( 'th', [ 'scope' => 'col', 'class' => 'headerSort' ], $this->msg( 'ui-feedback-special-table-head-done' )->escaped() );
				if ( !$only_one_item ) { // Dont display the freetext-lines in one-entry-view
					/* text1 */
					$output_text .= Html::element( 'th', [ 'scope' => 'col', 'class' => 'headerSort' ], $this->msg( 'ui-feedback-special-table-head-details' )->escaped() );
				}
				/* status */
				$output_text .= Html::element( 'th', [ 'scope' => 'col', 'class' => 'headerSort' ], $this->msg( 'ui-feedback-special-table-head-status' )->escaped() );
				/* comment */
				$output_text .= Html::element( 'th', [ 'scope' => 'col', 'class' => 'headerSort' ], $this->msg( 'ui-feedback-special-table-head-notes' )->escaped() );
				/* Notify */
				if ( $can_write ) {
					$output_text .= Html::element( 'th', [ 'scope' => 'col', 'class' => 'headerSort', 'title' => $this->msg( 'ui-feedback-special-tooltip-notify' )->escaped() ] );
				}
				/* end Row*/
				$output_text .= '</tr>';
				$output_text .= '</thead>';
				$output_text .= '<tbody>';

				/* Rows */
				foreach ( $res as $row ) {
					$output_text .= '<tr>';
					/* id */
					$output_text .= Xml::tags( 'td', null, Html::element( 'a', [ 'href' => $title->getFullURL( [ 'id' => $row->uif_id ] ) ], $row->uif_id ) );
					/* username */
					if ( $row->uif_username === '' ) {
						$output_text .= Html::element( 'td', null, $this->msg( 'ui-feedback-special-anonymous' )->escaped() );
					} else {
						$output_text .= Xml::tags( 'td', null, Html::element( 'a', [ 'href' => Title::makeTitleSafe( NS_USER_TALK, $row->uif_username )->getFullURL() ], $row->uif_username ) );
					}
					/* browser */
					/* using Html::element here seems to be more confusing then having it this way */
					if ( $can_write ) {
						$output_text .= '<td title="' . htmlspecialchars( $row->uif_useragent ) . '">';
					} else {
						$output_text .= '<td>';
					}
					if ( strpos( '#' . $row->uif_useragent, 'Chrome' ) ) {
						$output_text .= $chrome_icon;
					} elseif ( strpos( '#' . $row->uif_useragent, 'Safari' ) ) {
						$output_text .= $safari_icon;
					} elseif ( strpos( '#' . $row->uif_useragent, 'Firefox' ) ) {
						$output_text .= $firefox_icon;
					} elseif ( strpos( '#' . $row->uif_useragent, 'MSIE' ) ) {
						$output_text .= $internetexplorer_icon;
					} elseif ( strpos( '#' . $row->uif_useragent, 'Opera' ) ) {
						$output_text .= $opera_icon;
					} else {
						$output_text .= '?';
					}
					$output_text .= '</td>';
					/* os - only visible to admins */
					if ( $can_write ) {
						$output_text .= '<td title="' . htmlspecialchars( $row->uif_useragent ) . '">';
						if ( strpos( '#' . $row->uif_useragent, 'Windows' ) ) {
							$output_text .= $window_icon;
						} elseif ( strpos( '#' . $row->uif_useragent, 'Mac OS' ) ) {
							$output_text .= $mac_icon;
						} elseif ( strpos( '#' . $row->uif_useragent, 'Linux' ) ) {
							$output_text .= $linux_icon;
						} else {
							$output_text .= '<div>?</div>';
						}
						$output_text .= '</td>';
					}
					/* time */
					if ( $only_one_item ) {
						$output_text .= Html::element( 'td', null, $row->uif_created );
					}
					/* type */
					if ( $row->uif_type === '1' ) {
						$output_text .= Xml::tags( 'td', null, Xml::tags( 'a', [ 'href' => $title->getFullURL( [ 'id' => htmlspecialchars( $row->uif_id ) ] ) ], Html::element( 'div', [ 'class' => 'icon screenshot-icon', 'title' => $this->msg( 'ui-feedback-special-type-screenshot' )->escaped() ], 1 ) ) );
					} else {
						$output_text .= Xml::tags( 'td', null, Xml::tags( 'a', [ 'href' => $title->getFullURL( [ 'id' => htmlspecialchars( $row->uif_id ) ] ) ], Html::element( 'div', [ 'class' => 'icon questionnaire-icon', 'title' => $this->msg( 'ui-feedback-special-type-questionnaire' )->escaped() ], 2 ) ) );
					}
					/* importance */
					if ( $row->uif_importance == 0 ) {
						$output_text .= '<td>&nbsp;</td>';
					} else {
						$output_text .= '<td>' . $importance_array[ $row->uif_importance ] . '</td>';
					}
					/* happened */
					$output_text .= '<td>' . $happened_array[ $row->uif_happened ] . '</td>';
					/* task */
					$output_text .= Html::element( 'td', null, $row->uif_task );
					/* done */
					if ( $row->uif_done === null ) {
						$output_text .= '<td></td>';
					} else {
						$output_text .= '<td>' . $bool_array[ $row->uif_done ] . '</td>';
					}
					if ( !$only_one_item ) { // dont display the freetext-fields in the one-entry-only-view
						/* text1 */
						if ( strlen( $row->uif_text1 ) > 50 ) {
							$output_text .= Html::element( 'td', null, $this->getContext()->getLanguage()->truncateForVisual( $row->uif_text1, 50, $this->msg( 'ellipsis' )->escaped() ) );
						} else {
							$output_text .= Html::element( 'td', null, $row->uif_text1 );
						}
					}
					/* status */
					$output_text .= '<td>';
					if ( $row->uif_status == 0 ) {
						$output_text .= Html::element( 'b', null, $this->msg( 'ui-feedback-special-status-open' )->escaped() );
						$output_text .= '<br/>';
						/* only admins can change the status */
						if ( $can_write ) {
							$output_text .= Html::element( 'a', [ 'href' => $title->getFullURL( [ 'id' => htmlspecialchars( $row->uif_id ) ] ) ], $this->msg( 'ui-feedback-special-status-in-review' )->escaped() );
						}
					} elseif ( $row->uif_status == 1 ) {
						/* only admins can change the status*/
						if ( $can_write ) {
							$output_text .= Xml::tags( 'a', [ 'href' => $title->getFullURL( [ 'id' => htmlspecialchars( $row->uif_id ) ] ) ], Html::element( 'b', null, $this->msg( 'ui-feedback-special-status-in-review' )->escaped() ) );
						} else {
							$output_text .= Html::element( 'b', null, $this->msg( 'ui-feedback-special-status-in-review' )->escaped() );
						}
					} elseif ( $row->uif_status == 2 ) {
						$output_text .= htmlspecialchars( $this->msg( 'ui-feedback-special-status-closed' )->escaped() ) . '<br/>';
					} elseif ( $row->uif_status == 3 ) {
						$output_text .= htmlspecialchars( $this->msg( 'ui-feedback-special-status-declined' )->escaped() ) . '<br/>';
					}
					$output_text .= '</td>';
					/* comment */
					$output_text .= Html::element( 'td', null, $row->uif_comment );
					/* notify - only admins see this */
					if ( $can_write ) {
						if ( $row->uif_notify ) {
							$output_text .= Xml::tags( 'td', [ 'title' => $this->msg( 'ui-feedback-special-tooltip-notify' )->escaped() ], Xml::tags( 'a', [ 'href' => Title::makeTitleSafe( NS_USER_TALK, $row->uif_username )->getFullURL() ], Html::element( 'div', [ 'class' => 'icon notify' ], 1 ) ) );
						} else {
							$output_text .= '<td></td>';
						}
					}
					/* end row */
					$output_text .= '</tr>';
				}
				$output_text .= '</tbody>';
				$output_text .= '</table>';
				/* end create table */

				/* statistics about presenting the different request-methods and how often they have been clicked */
				/* this information is not usefull for 'normal' users, so only admins will see it */
				if ( !$only_one_item && $can_write ) {
					$output_text .= '<div style="border:1px solid black;background:#FCFCFC;padding:5px;width:325px">';
					$type_array = [ $this->msg( 'ui-feedback-special-stats-type-1' )->escaped(),
						$this->msg( 'ui-feedback-special-stats-type-2' )->escaped(),
						$this->msg( 'ui-feedback-special-stats-type-3' )->escaped()
					];
					/*get rows from database*/
					$res_stats = $dbr->select( [ 'uifeedback_stats' ], [ 'uifs_type', 'uifs_shown', 'uifs_clicked', 'uifs_sent' ], '', __METHOD__, [ 'ORDER BY' => 'uifs_type DESC' ] );
					$output_text .= htmlspecialchars( $this->msg( 'ui-feedback-special-stats-head' )->escaped() );
					$output_text .= '<table style="text-align:right;border-collapse: separate;border-spacing: 10px 5px;">';
					$output_text .= '<tr><th>' . $this->msg( 'ui-feedback-special-stats-type' )->escaped() . '</th><th>' . $this->msg( 'ui-feedback-special-stats-shown' )->escaped() . '</th><th>' . $this->msg( 'ui-feedback-special-stats-clicked' )->escaped() . '</th><th>' . $this->msg( 'ui-feedback-special-stats-sent' )->escaped() . '</th></tr>';
					/* add rows to the table */
					foreach ( $res_stats as $row_stats ) {
						$output_text .= '<tr>';
						$output_text .= Html::element( 'td', [ 'style' => 'text-align:right;' ], $type_array[ $row_stats->uifs_type ] );
						$output_text .= Html::element( 'td', [ 'style' => 'text-align:right;' ], $row_stats->uifs_shown );
						$output_text .= Html::element( 'td', [ 'style' => 'text-align:right;' ], $row_stats->uifs_clicked );
						$output_text .= Html::element( 'td', [ 'style' => 'text-align:right;' ], $row_stats->uifs_sent );
						$output_text .= '</tr>';
					}
					$output_text .= '</table>';
					$output_text .= '</div>';
				} /* end show and click stats */

			} else {
				$output_text .= '<h2 style="clear:both;">' . $this->msg( 'ui-feedback-special-feedback' )->escaped() . '</h2>' . $this->msg( 'ui-feedback-special-nothing-found' )->escaped();
			}

			/* One-Feedback-Item-View */
			if ( $only_one_item ) {
				if ( $count < 1 ) {
					$output_text .= '<h2 style="clear:both;">' . $this->msg( 'ui-feedback-special-feedback' )->escaped() . '</h2>' . $this->msg( 'ui-feedback-special-nothing-found' )->escaped();
				} else {
					$output_text .= '<h2>URL</h2>';
					$output_text .= Html::element( 'a', [ 'href' => $row->uif_url ], $row->uif_url );

					if ( $row->uif_type === '1' ) { /* screenshot Feedback */
						$output_text .= Html::element( 'h2', null, $this->msg( 'ui-feedback-special-table-head-details' )->escaped() );
						$output_text .= htmlspecialchars( $row->uif_text1 );
						if ( strlen( $row->uif_text1 ) == 0 ) {
							$output_text .= Html::element( 'i', null, $this->msg( 'ui-feedback-special-table-head-none' )->escaped() );
						}
					} else { /* Questionnaire Feedback */
						$output_text .= Html::element( 'h2', null, $this->msg( 'ui-feedback-special-table-head-details' )->escaped() );
						$output_text .= htmlspecialchars( $row->uif_text1 );
						if ( strlen( $row->uif_text1 ) == 0 ) {
							$output_text .= Html::element( 'i', null, $this->msg( 'ui-feedback-special-table-head-none' )->escaped() );
						}
					}
					$output_text .= '<div>';

					/* Review Form - only for admins */
					if ( $can_write ) {
						$output_text .= '<div style = "float:left;">';
						$output_text .= '<h1>' . htmlspecialchars( $this->msg( 'ui-feedback-special-review' )->escaped() ) . '</h1>';
						if ( $row->uif_notify ) {
							$output_text .= '' . htmlspecialchars( $this->msg( 'ui-feedback-special-info' )->escaped() ) . ': <i>' . htmlspecialchars( $this->msg( 'ui-feedback-special-tooltip-notify' )->escaped() ) . '</i><br/>';
						}
						$output_text .= '<form name="review" method="post" id="ui-review-form" action="">';
						$output_text .= '<div style="float:left;">';
						$output_text .= '' . htmlspecialchars( $this->msg( 'ui-feedback-special-table-head-status' )->escaped() ) . ':<br/>';
						$output_text .= '<label><input type="radio" name="status" value="1" ' . ( ( $row->uif_status == 1 ) ? 'checked' : '' ) . '>' . htmlspecialchars( $this->msg( 'ui-feedback-special-status-in-review' )->escaped() ) . '</label><br/>';
						$output_text .= '<label><input type="radio" name="status" value="2" ' . ( ( $row->uif_status == 2 ) ? 'checked' : '' ) . '>' . htmlspecialchars( $this->msg( 'ui-feedback-special-status-closed' )->escaped() ) . '</label><br/>';
						$output_text .= '<label><input type="radio" name="status" value="3" ' . ( ( $row->uif_status == 3 ) ? 'checked' : '' ) . '>' . htmlspecialchars( $this->msg( 'ui-feedback-special-status-declined' )->escaped() ) . '</label><br/>';
						$output_text .= '</div>';
						$output_text .= '<div style="float:left;margin-left:20px">';
						$output_text .= '<label>' . htmlspecialchars( $this->msg( 'ui-feedback-special-table-head-notes' )->escaped() ) . ':<br/><textarea name="comment" rows="5" style="width:300px"></textarea></label>';
						$output_text .= '<input type="hidden" name="id" value="' . $id . '">';
						$output_text .= '<input type="hidden" name="method" value="review">';
						$output_text .= '<br/><input type="button" value="send" id="ui-feedback-send-review-button" >';
						$output_text .= '</div></form>';
						$output_text .= '</div>';
					}
					/* previous Comments/Reviews */
					$res = $dbr->select(
						[ 'uifeedback_reviews' ],
						[ 'uifr_created', 'uifr_reviewer', 'uifr_status', 'uifr_comment' ],
						[ 'uifr_feedback_id' => $id ],
						__METHOD__,
						[ 'ORDER BY' => 'uifr_created DESC' ]
					);
					if ( $res->numRows() > 0 ) {
						$output_text .= '<div style = "clear:both;">';
						$output_text .= '<h1>' . htmlspecialchars( $this->msg( 'ui-feedback-special-previous-notes' )->escaped() ) . '</h1>';
						$output_text .= '<ul>';
						foreach ( $res as $review_row ) {
							$output_text .= '<li>' . htmlspecialchars( $review_row->uifr_created ) . ' - ' . htmlspecialchars( $review_row->uifr_reviewer ) . ' - <b>' . $status_array[ $review_row->uifr_status ] . '</b>:<br/>' . htmlspecialchars( $review_row->uifr_comment ) . '</li>';
						}
						$output_text .= '</ul>';
						$output_text .= '</div>';

						$output_text .= '</div>';
					}
					/* end previous comments */

					/* Screenshot */
					if ( $row->uif_type == '1' ) {
						$output_text .= '<div style="clear: both;">';
						$output_text .= '<h2>' . $this->msg( 'ui-feedback-special-type-screenshot' )->escaped() . ':</h2>';

						/* add the screenshot or an error message if image not found */
						if ( method_exists( MediaWikiServices::class, 'getRepoGroup' ) ) {
							// MediaWiki 1.34+
							$file = MediaWikiServices::getInstance()->getRepoGroup()->findFile( 'UIFeedback_screenshot_' . $row->uif_id . '.png' );
						} else {
							$file = wfFindFile( 'UIFeedback_screenshot_' . $row->uif_id . '.png' );
						}
						if ( $file ) {
							$output_text .= Xml::tags( 'a', [ 'href' => $file->getFullUrl() ], Html::element( 'img', [ 'alt' => 'screenshot', 'src' => $file->createThumb( 600, 600 ) ] ) );
						} else {
							$output_text .= '<i>' . $this->msg( 'ui-feedback-special-screenshot-error' )->escaped() . '</i>';
						}
						$output_text .= '</div>';
					}
				}
			}
		}

		/* write to output */
		$output->addHTML( $output_text );
	}

}
