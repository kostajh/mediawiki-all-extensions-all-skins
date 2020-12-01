<?php

use MediaWiki\MediaWikiServices;

/**
 * implements Special:Namespaces
 * @ingroup SpecialPage
 */
class SpecialNamespaces extends SpecialPage {
	function __construct() {
		parent::__construct( 'Namespaces' );
	}

	public function doesWrites() {
		return true;
	}

	function discard() {
		MediaWikiServices::getInstance()->getLocalServerObjectCache()
			->delete( wfMemcKey( 'SpecialNamespaces', 'names' ) );
	}

	function execute( $par ) {
		$admin = $this->getUser()->isAllowed( 'namespaces' );
		$this->setHeaders();
		$this->outputHeader();

		if ( $admin ) {
			$this->getOutput()->setPageTitle( wfMessage( 'namespaces' ) );
		} else {
			$this->getOutput()->setPageTitle( wfMessage( 'namespaces-title-norights' ) );
		}
		$req = $this->getRequest();
		$action = $req->getVal( 'action', $par );

		switch ( $action ) {
		case "delete":
		case "edit" :
		case "add" :
			if ( !$admin ) {
				throw new PermissionsError( 'namespaces' );
			}
			$this->showForm( $action );
			break;
		case "submit":
			if ( !$admin ) {
				throw new PermissionsError( 'namespaces' );
			}
			if ( !$req->wasPosted() || !$this->getUser()->matchEditToken( $req->getVal( 'wpEditToken' ) ) ) {
				$this->getOutput()->addWikiMsg( 'sessionfailure' );
				return;
			}
			$this->doSubmit();
			break;
		default:
			$this->showList( $admin );
			break;
		}
	}

	function showForm( $action ) {
		$actionUrl = $this->getPageTitle()->getLocalURL( 'action=submit' );
		$req = $this->getRequest();
		$token = $this->getUser()->getEditToken();
		$defaultreason = $req->getVal( 'wpNamespacesReason', wfMessage( 'namespaces_defaultreason' )->text() );

		switch ( $action ) {
		case "delete":

			$nsid = $req->getVal( 'prefix' );
			$nsoldname = $req->getVal( 'name' );
			$button = wfMessage( 'delete' )->text();
			$topmessage = wfMessage( 'namespaces_delquestion', $nsid )->text();
			$deletingmessage = wfMessage( 'namespaces_deleting', $nsoldname )->text();
			$reasonmessage = wfMessage( 'deletecomment' )->text();

			$this->getOutput()->addHTML(
				Xml::openElement( 'fieldset' ) .
				Xml::element( 'legend', null, $topmessage ) .
				Xml::openElement( 'form', array( 'id' => 'mw-namespaces-deleteform', 'method' => 'post', 'action' => $actionUrl ) ) .
				Xml::openElement( 'table' ) .
				"<tr><td>$deletingmessage</td></tr>" .
				'<tr><td class="mw-label">' . Xml::label( $reasonmessage, 'mw-namespaces-deletereason' ) . '</td>' .
				'<td class="mw-input">' .
				Xml::input( 'wpNamespacesReason', 60, $defaultreason, array( 'tabindex' => '1', 'id' => 'mw-namespaces-deletereason', 'maxlength' => '200' ) ) .
				'</td></tr>' .
				'<tr><td class="mw-submit">' . Xml::submitButton( $button, array( 'id' => 'mw-namespaces-submit' ) ) .
				Html::hidden( 'wpNamespacesID', $nsid ) .
				Html::hidden( 'wpNamespacesOldName', $nsoldname ) .
				Html::hidden( 'wpNamespacesAction', $action ) .
				Html::hidden( 'wpEditToken', $token ) .
				'</td></tr>' .
				Xml::closeElement( 'table' ) .
				Xml::closeElement( 'form' ) .
				Xml::closeElement( 'fieldset' )
			);
			break;
		case "edit" :
		case "add" :
			if ( $action == "edit" ) {
				$nsid = $req->getVal( 'prefix' );
				$nsoldname = $req->getVal( 'name' );
				$dbr = wfGetDB( DB_REPLICA );
				$row = $dbr->selectRow( 'namespace_names', '*', array( 'ns_name' => $nsoldname, 'ns_id' => $nsid ) );
				if ( !$row ) {
					$this->error( 'namespaces_editerror', $nsoldname );
					return;
				}
				$nsid = '<tt>' . htmlspecialchars( $row->ns_id ) . '</tt>';
				$defaultname = $row->ns_name;
				$nsdefault = $row->ns_default;
				$nscanonical = $row->ns_canonical;
				$old = Html::hidden( 'wpNamespacesID', $row->ns_id );
				$old .= Html::hidden( 'wpNamespacesOldName', $row->ns_name );
				$topmessage = wfMessage( 'namespaces_edittext' )->parse();
				$intromessage = wfMessage( 'namespaces_editintro' )->parse();
				$button = wfMessage( 'edit' )->text();
			} else {
				$nsid = $req->getVal( 'wpNamespacesID' ) ? $req->getVal( 'wpNamespacesID' ) : $req->getVal( 'prefix' );
				$nsid = Xml::input( 'wpNamespacesID', 20, $nsid, array( 'tabindex' => '1', 'id' => 'mw-namespaces-nsid', 'maxlength' => '20' ) );
				$nsdefault = $req->getCheck( 'wpNamespacesDefault' );
				$nscanonical = $req->getCheck( 'wpNamespacesCanonical' );
				$old = '';
				$defaultname = $req->getVal( 'wpNamespacesName' ) ? $req->getVal( 'wpNamespacesName' ) : wfMessage( 'namespaces_defaultname' )->text() ;
				$topmessage = wfMessage( 'namespaces_addtext' )->parse();
				$intromessage = wfMessage( 'namespaces_addintro' )->parse();
				$button = wfMessage( 'namespaces_addbutton' )->text();
			}

			$nsidmessage = wfMessage( 'namespaces_nsid' )->parse();
			$nsdefaultmessage = wfMessage( 'namespaces_default' )->text();
			$nscanonicalmessage = wfMessage( 'namespaces_canonical' )->text();
			$reasonmessage = wfMessage( 'namespaces_reasonfield' )->text();
			$nsnamemessage = wfMessage( 'namespaces_nsname' )->text();

			$this->getOutput()->addHTML(
				Xml::openElement( 'fieldset' ) .
				Xml::element( 'legend', null, $topmessage ) .
				$intromessage .
				Xml::openElement( 'form', array( 'id' => 'mw-namespaces-editform', 'method' => 'post', 'action' => $actionUrl ) ) .
				Xml::openElement( 'table', array( 'id' => "mw-namespaces-$action" ) ) .
				"<tr><td class='mw-label'>$nsidmessage</td><td><tt>$nsid</tt></td></tr>" .
				"<tr><td class='mw-label'>" . Xml::label( $nsdefaultmessage, 'mw-namespaces-nsdefault' ) . '</td>' .
				"<td class='mw-input'>" . Xml::check( 'wpNamespacesDefault', $nsdefault, array( 'id' => 'mw-namespaces-nsdefault' ) ) . '</td></tr>' .
				'<tr><td class="mw-label">' . Xml::label( $nscanonicalmessage, 'mw-namespaces-nscanonical' ) . '</td>' .
				'<td class="mw-input">' .  Xml::check( 'wpNamespacesCanonical', $nscanonical, array( 'id' => 'mw-namespaces-nscanonical' ) ) . '</td></tr>' .
				'<tr><td class="mw-label">' . Xml::label( $nsnamemessage, 'mw-namespaces-nsname' ) . '</td>' .
				'<td class="mw-input">' . Xml::input( 'wpNamespacesName', 60, $defaultname, array( 'tabindex' => '1', 'maxlength' => '200', 'id' => 'mw-namespaces-nsname' ) ) . '</td></tr>' .
				'<tr><td class="mw-label">' . Xml::label( $reasonmessage, 'mw-namespaces-editreason' ) . '</td>' .
				'<td class="mw-input">' . Xml::input( 'wpNamespacesReason', 60, $defaultreason, array( 'tabindex' => '1', 'id' => 'mw-namespaces-editreason', 'maxlength' => '200' ) ) .
				Html::hidden( 'wpNamespacesAction', $action ) .
				$old .
				Html::hidden( 'wpEditToken', $token ) .
				'</td></tr>' .
				'<tr><td class="mw-submit">' . Xml::submitButton( $button, array( 'id' => 'mw-namespaces-submit' ) ) . '</td></tr>' .
				Xml::closeElement( 'table' ) .
				Xml::closeElement( 'form' ) .
				Xml::closeElement( 'fieldset' )
			);
			break;
		}
	}

	function doSubmit() {
		$req = $this->getRequest();
		$nsoldname = $req->getVal( 'wpNamespacesOldName' );
		$nsid = $req->getVal( 'wpNamespacesID' );
		$do = $req->getVal( 'wpNamespacesAction' );
		$nsoldname = str_replace( '+', '_', $nsoldname );
		if ( preg_match( '/[\s:&=]/', $nsid ) ) {
			$this->error( 'namespaces-badprefix', htmlspecialchars( $nsid ) );
			$this->showForm( $do );
			return;
		}
		$reason = $req->getText( 'wpNamespacesReason' );
		$selfTitle = $this->getPageTitle();
		$dbw = wfGetDB( DB_MASTER );
		switch ( $do ) {
		case "delete":
			$dbw->delete( 'namespace_names', array( 'ns_name' => $nsoldname, 'ns_id' => $nsid ), __METHOD__ );

			if ( $dbw->affectedRows() == 0 ) {
				$this->error( 'namespaces_delfailed', $nsoldname );
				$this->showForm( $do );
			} else {
				$out = $this->getOutput();
				if ( method_exists( $out, 'addWikiTextAsInterface' ) ) {
					// MW 1.32+
					$out->addWikiTextAsInterface( wfMessage( 'namespaces_deleted', $nsoldname )->text() );
				} else {
					$out->addWikiText( wfMessage( 'namespaces_deleted', $nsoldname )->text() );
				}
				$out->returnToMain( false, $selfTitle );
				$log = new LogPage( 'namespaces' );
				$log->addEntry( 'ns_delete', $selfTitle, $reason, array( $nsoldname ), $this->getUser() );
			}
			$this->discard();
			break;
		case "edit":
		case "add":
			$newname = $req->getVal( 'wpNamespacesName' );
			$nsdefault = $req->getCheck( 'wpNamespacesDefault' ) ? 1 : 0;
			$nscanonical = $req->getCheck( 'wpNamespacesCanonical' ) ? 1 : 0;
			$data = array( 'ns_id' => $nsid, 'ns_name' => $newname,
				'ns_default'  => $nsdefault, 'ns_canonical'  => $nscanonical );

			if ( $do == 'add' ) {
				$dbw->insert( 'namespace_names', $data, __METHOD__, 'IGNORE' );
			} else {
				$dbw->update( 'namespace_names', $data, array( 'ns_name' => $nsoldname ), __METHOD__, 'IGNORE' );
			}

			if ( $dbw->affectedRows() == 0 ) {
				$this->error( "namespaces_{$do}failed", $nsid );
				$this->showForm( $do );
			} else {
				$this->getOutput()->addWikiMsg( "namespaces_{$do}ed", $nsid );
				$this->getOutput()->returnToMain( false, $selfTitle );
				$log = new LogPage( 'namespaces' );
				$log->addEntry(
					'ns_' . $do,
					$selfTitle,
					$reason,
					array( $nsid, $newname, $nsdefault, $nscanonical ),
					$this->getUser()
				);
			}
			$this->discard();
			break;
		}
	}

	function trans_default( $tl, $msg0, $msg1 ) {
		if ( $tl === '0' )
			return $msg0;
		if ( $tl === '1' )
			return $msg1;
		return htmlspecialchars( $tl );
	}

	function showList( $admin ) {
		global $wgScriptPath;

		$this->getOutput()->addModuleStyles( "ext.specialnamespaces" );

		$nsidmessage = wfMessage( 'namespaces_nsid' )->parse();
		$nsnamemessage = wfMessage( 'namespaces_nsname' )->parse();
		$nsdefaultmessage = wfMessage( 'namespaces_default' )->parse();
		$nscanonicalmessage = wfMessage( 'namespaces_canonical' )->parse();
		$message_0 = wfMessage( 'namespaces_0' )->parse();
		$message_1 = wfMessage( 'namespaces_1' )->parse();

		$out = '
<table width="100%" cellspacing="0" cellpadding="0" border="0" style="border:0" class="mw-namespacestable wikitable intro">
<tr><th class="mw-align-left">' . $nsidmessage . '</th><td>' . wfMessage( 'namespaces_nsid_intro' )->parse() . '</td></tr>
<tr><th class="mw-align-left">' . $nsnamemessage . '</th><td>' . wfMessage( 'namespaces_nsname_intro' )->parse() . '</td></tr>
<tr><th class="mw-align-left">' . $nsdefaultmessage . '</th><td>' . wfMessage( 'namespaces_default_intro' )->parse() . '</td></tr>
<tr><th class="mw-align-right">' . $message_1 . '</th><td>' . wfMessage( 'namespaces_default_1_intro' )->parse() . '</td></tr>
<tr><th class="mw-align-right">' . $message_0 . '</th><td>' . wfMessage( 'namespaces_default_0_intro' )->parse() . '</td></tr>
<tr><th class="mw-align-left">' . $nscanonicalmessage . '</th><td>' . wfMessage( 'namespaces_canonical_intro' )->parse() . '</td></tr>';
		$out .= '
<tr><th class="mw-align-right">' . $message_1 . '</th><td>' . wfMessage( 'namespaces_canonical_1_intro' )->parse() . '</td></tr>
<tr><th class="mw-align-right">' . $message_0 . '</th><td>' . wfMessage( 'namespaces_canonical_0_intro' )->parse() . '</td></tr>
';
		$out .= '</table>
';
		$this->getOutput()->addWikiMsg( 'namespaces_intro' );
		$this->getOutput()->addHTML( $out );
		$this->getOutput()->addWikiMsg( 'namespaces_intro_footer' );
		$selfTitle = $this->getPageTitle();

		if ( $admin ) {
			$skin = $this->getSkin();
			$addtext = wfMessage( 'namespaces_addtext' )->parse();
			$addlink = Linker::link( $selfTitle, $addtext, array(), array( 'action' => 'add' ) );
			$this->getOutput()->addHTML( '<p>' . $addlink . '</p>' );
		}

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select( 'namespace_names', '*', '1', __METHOD__, array( 'ORDER BY' => 'ns_id' ) );
		$numrows = $res->numRows();
		if ( $numrows == 0 ) {
			$this->error( 'namespaces_error' );
			return;
		}

		$out = "
		<table width='100%' class='mw-namespacestable wikitable body'>
		<tr id='namespacestable-header'><th>$nsidmessage</th> <th>$nsnamemessage</th> <th>$nsdefaultmessage</th> <th>$nscanonicalmessage</th>";
		if ( $admin ) {
			$deletemessage = wfMessage( 'delete' )->parse();
			$editmessage = wfMessage( 'edit' )->parse();
			$out .= '<th>' . wfMessage( 'namespaces_edit' )->parse() . '</th>';
		}
		$out .= "</tr>\n";

		while ( $s = $res->fetchObject() ) {
			$nsid = htmlspecialchars( $s->ns_id );
			$nsname = htmlspecialchars( $s->ns_name );
			$nsdefault = $this->trans_default( $s->ns_default, $message_0, $message_1 );
			$nscanonical = $this->trans_default( $s->ns_canonical, $message_0, $message_1 );
			$out .= "<tr class='mw-namespacestable-row'>
				<td class='mw-namespacestable-nsid'>$nsid</td>
				<td class='mw-namespacestable-nsname'>$nsname</td>
				<td class='mw-namespacestable-nsdefault'>$nsdefault</td>
				<td class='mw-namespacestable-nscanonical'>$nscanonical</td>";
			if ( $admin ) {
				$out .= '<td class="mw-namespacestable-modify">';
				$out .= Linker::link( $selfTitle, $editmessage, array(),
					array( 'action' => 'edit', 'prefix' => $nsid, 'name' => $nsname ) );
				$out .= ', ';
				$out .= Linker::link( $selfTitle, $deletemessage, array(),
					array( 'action' => 'delete', 'prefix' => $nsid, 'name' => $nsname ) );
				$out .= '</td>';
			}

			$out .= "\n</tr>\n";
		}
		$res->free();
		$out .= "</table><br />";
		$this->getOutput()->addHTML( $out );
	}

	function error() {
		$args = func_get_args();
		$this->getOutput()->wrapWikiMsg( "<p class='error'>$1</p>", $args );
	}

	protected function getGroupName() {
		return 'wiki';
	}
}
