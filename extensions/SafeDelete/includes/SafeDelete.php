<?php

/*
 * Copyright (c) 2015 The MITRE Corporation
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

use MediaWiki\MediaWikiServices;

class SafeDelete extends UnlistedSpecialPage {

	public function __construct() {
		parent::__construct( 'SafeDelete' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $par ) {
		$this->setHeaders();
		$this->outputHeader();

		if ( !$this->getUser()->isAllowed( 'delete' ) ) {
			$this->displayRestrictionError();
			return;
		}

		if ( $par === null ) {
			$this->getOutput()->addHTML(
				$this->msg( 'safedelete-missingpagename' ) );
			return;
		}

		if ( !isset( $GLOBALS['SafeDeleteNamespaces'] ) ) {
			$this->getOutput()->addHTML(
				$this->msg( 'safedelete-missingnamespaces' ) );
			return;
		}

		$title = Title::newFromText( $par );
		$this->getSkin()->setRelevantTitle( $title );

		if ( class_exists( "SemanticTitle" ) ) {
			$displaytitle = SemanticTitle::getText( $title );
		} else {
			$displaytitle = $title->getPrefixedText();
		}
		$this->getOutput()->setPageTitle( $this->msg( 'safedelete-title',
			$displaytitle ) );

		$this->getOutput()->addBacklinkSubtitle( $title );

		$done = false;

		$result = [];

		if ( isset( $GLOBALS['SafeDeleteSemantic'] ) &&
			$GLOBALS['SafeDeleteSemantic'] ) {

			$result = $this->querySemantic( $title );
			$done = true;
		}

		if ( isset( $GLOBALS['SafeDeleteCargo'] ) &&
			is_array( $GLOBALS['SafeDeleteCargo'] ) &&
			count( $GLOBALS['SafeDeleteCargo'] ) > 0 ) {

			$result = array_merge( $result,
				$this->queryCargo( $title, $GLOBALS['SafeDeleteCargo'] ) );
			$done = true;
		}

		if ( !$done ) {

			$result = $this->queryNonSemantic( $title );

		}

		$count = count( $result );
		if ( $count > 0 ) {
			$this->getOutput()->addHTML(
				$this->msg( 'safedelete-cannotdelete', $displaytitle )->numParams( $count ) );

			$this->getOutput()->addHTML( Html::element( 'br' ) );
			$this->getOutput()->addHTML( Html::openElement( 'ul' ) );
			foreach ( $result as $row ) {
				$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
				$link = $linkRenderer->makeLink( $row );
				$element = Xml::tags( 'li', null, "$link" );
				$this->getOutput()->addHTML( $element );
			}

			$this->getOutput()->addHTML( Html::closeElement( 'ul' ) );

		} else {

			$this->getOutput()->redirect(
				$title->getLocalURL( 'action=delete' ) );

		}
	}

	private function querySemantic( $title ) {
		$thispage = SMWDIWikiPage::newFromTitle( $title );

		$store = \SMW\StoreFactory::getStore();

		$properties = $store->getInProperties( $thispage );

		$result = [];

		foreach ( $properties as $property ) {
			$subjects = $store->getPropertySubjects( $property, $thispage );
			foreach ( $subjects as $page ) {

				// links from subobjects are treated as links from the
				// containing page
				$namespace = $page->getNamespace();
				$text = $page->getDBkey();
				$pagetitle = Title::makeTitle( $namespace, $text );

				$pagename = $pagetitle->getPrefixedText();
				if ( !$title->equals( $pagetitle ) &&
					!array_key_exists( $pagename, $result ) ) {
					$result[$pagename] = $pagetitle;
				}
			}
		}

		return $result;
	}

	private function queryCargo( $title, $cargo_fields ) {
		$targetpage = $title->getPrefixedText();

		$result = [];

		foreach ( $cargo_fields as $field ) {

			if ( isset( $field[2] ) && $field[2] == true ) {
				$operator = ' HOLDS ';
			} else {
				$operator = ' = ';
			}

			$query = CargoSQLQuery::newFromValues( $field[0], '_pageName',
				$field[1] . $operator . '"' . $targetpage . '"', null,
				'_pageName', null, null, '' );
			$rows = $query->run();

			foreach ( $rows as $row ) {

				$sourcepage = $row['_pageName'];

				if ( ( $sourcepage != $targetpage ) &&
					!array_key_exists( $targetpage, $result ) ) {
					$result[$sourcepage] = Title::newFromText( $sourcepage );
				}
			}
		}

		return $result;
	}

	private function queryNonSemantic( $title ) {
		$dbr = wfGetDB( DB_REPLICA );

		$queryLimit = 1000;

		$sql1 = $dbr->selectSQLText(
			[
				'pagelinks',
				'page'
			],
			[
				'page_namespace',
				'page_title'
			],
			[
				'pl_namespace' => $title->getNamespace(),
				'pl_title' => $title->getText(),
				'page_namespace' => $GLOBALS['SafeDeleteNamespaces'],
				'pl_from=page_id'
			],
			__METHOD__,
			[
				'DISTINCT',
				'LIMIT' => $queryLimit
			]
		);

		$sql2 = $dbr->selectSQLText(
			[
				'redirect',
				'page'
			],
			[
				'page_namespace',
				'page_title'
			],
			[
				'rd_namespace' => $title->getNamespace(),
				'rd_title' => $title->getText(),
				'page_namespace' => $GLOBALS['SafeDeleteNamespaces'],
				'rd_from=page_id'
			],
			__METHOD__,
			[
				'DISTINCT',
				'LIMIT' => $queryLimit
			]
		);

		$sql = $dbr->unionQueries( [ $sql1, $sql2 ], false );

		// @codingStandardsIgnoreStart
		$rows = $dbr->query( $sql );
		// @codingStandardsIgnoreEnd

		$result = [];

		foreach ( $rows as $row ) {
			$result[] = Title::makeTitle( $row->page_namespace,
				$row->page_title );
		}

		return $result;
	}

	/**
	 * @param SkinTemplate &$sktemplate
	 * @param array &$links
	 */
	public static function checkLink( SkinTemplate &$sktemplate,
		array &$links ) {
		if ( isset( $GLOBALS['SafeDeleteNamespaces'] ) &&
			isset( $links['actions']['delete'] ) ) {

			$title = $sktemplate->getTitle();
			$pagename = $title->getPrefixedText();

			$sd = "Special:SafeDelete/";
			if ( substr( $pagename, 0, strlen( $sd ) ) === $sd ) {

				unset( $links['actions']['delete'] );

			} elseif ( $title->inNamespaces( $GLOBALS['SafeDeleteNamespaces'] ) ) {

				$links['actions']['delete']['href'] =
					SpecialPage::getTitleFor( 'SafeDelete', $pagename )->
					getLocalURL();
			}

		}
	}
}
