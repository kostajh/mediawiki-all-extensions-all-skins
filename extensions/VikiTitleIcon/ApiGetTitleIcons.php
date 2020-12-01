<?php
/*
 * Copyright (c) 2014 The MITRE Corporation
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

class ApiGetTitleIcons extends ApiBase {
	public function __construct( $main, $action ) {
		parent::__construct( $main, $action );
	}

	public function execute() {
		$pageTitle = $this->getMain()->getVal( 'pageTitle' );

		global $wgTitleIcon_TitleIconPropertyName;
		$titleIconProperty = $wgTitleIcon_TitleIconPropertyName;

		$title = Title::newFromText( $pageTitle );

		$titleIconNames = $this->getPropertyValues( $title, $titleIconProperty );

		$categories = $title->getParentCategories();
		foreach( $categories as $category ) {
			$categoryTitle = Title::newFromText( $category );
			$names = $this->getPropertyValues( $categoryTitle, $titleIconProperty );
			foreach( $names as $name ) {
				if ( ! in_array( $name, $titleIconNames ) ) {
					$titleIconNames[] = $name;
				}
			}
		}

		if ( method_exists( MediaWikiServices::class, 'getRepoGroup' ) ) {
			// MediaWiki 1.34+
			$repoGroup = MediaWikiServices::getInstance()->getRepoGroup();
		} else {
			$repoGroup = RepoGroup::singleton();
		}
		$titleIconURLs = array();
		foreach ( $titleIconNames as $name ) {
			$url = $repoGroup->findFile( Title::newFromText( "File:" . $name ) )->getFullURL();
			$titleIconURLs[] = $url;
		}

		$this->getResult()->addValue( null, $this->getModuleName(),
			array(
				'pageTitle' => $pageTitle,
				'titleIcons' => $titleIconURLs
			) );

		return true;

	}

	private function getPropertyValues( Title $title, $propertyname ) {

		$store = \SMW\StoreFactory::getStore();

		// remove fragment
		$title = Title::newFromText( $title->getPrefixedText() );

		$subject = SMWDIWikiPage::newFromTitle( $title );
		$data = $store->getSemanticData( $subject );
		$property = SMWDIProperty::newFromUserLabel( $propertyname );
		$values = $data->getPropertyValues( $property );

		$strings = array();
		foreach ( $values as $value ) {
			if ( $value->getDIType() == SMWDataItem::TYPE_STRING ||
				$value->getDIType() == SMWDataItem::TYPE_BLOB ) {
				$strings[] = trim( $value->getString() );
			}
		}

		return $strings;
	}

	public function getDescription() {
		return "Get the URLs of all Title Icons for the page, if any exist.

Note that because the returned value is a JSON object, you must specify ".
"format=json in this query; the default xml format will return only an error.";
	}
	public function getAllowedParams() {
		return array(
			'pageTitle' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			)
		);
	}
	public function getParamDescription() {
		return array(
			'pageTitle' => 'title of the page whose title icons you wish to retrieve'
		);
	}
	public function getExamples() {
		return array(
			'api.php?action=getTitleIcons&pageTitle=Test_Page' );
	}
	public function getHelpUrls() {
		return '';
	}
}
