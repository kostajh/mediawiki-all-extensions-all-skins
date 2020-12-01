<?php

/*
 * Copyright (c) 2013 The MITRE Corporation
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

class HierarchySelectFormInput extends PFFormInput {

	public function __construct( $inputNumber, $curValue, $inputName,
		$disabled, $otherArgs ) {
		parent::__construct( $inputNumber, $curValue, $inputName, $disabled,
			$otherArgs );
		$this->addJsInitFunctionData( 'selectFromHierarchyInit',
			$this->setupJsInitAttribs() );
	}

	public static function getName() {
		return 'hierarchySelect';
	}

	/**
	 * Gets necessary information to initialize the parameters used to call the
	 * select from hierarchy JS user interface code.
	 *
	 * @return string: JSON encoded parameters for select from hierarchy JS code.
	 */
	protected function setupJsInitAttribs() {
		// global $HierarchyBuilder_LegacyMode;

		/*
		 * Argument parsing
		 */

		if ( array_key_exists( HierarchyBuilder::HIERARCHY, $this->mOtherArgs ) ) {
			$this->mHierarchy = $this->mOtherArgs[HierarchyBuilder::HIERARCHY];
		} else {
			// default is null instead of '' to distinguish not given vs empty
			$this->mHierarchy = null;
		}

		if ( array_key_exists( HierarchyBuilder::PAGENAME, $this->mOtherArgs ) ) {
			$this->mPageName = $this->mOtherArgs[HierarchyBuilder::PAGENAME];
		} else {
			$this->mPageName = null;
			return;
		}

		if ( array_key_exists( HierarchyBuilder::PROPERTYNAME, $this->mOtherArgs ) ) {
			$this->mHierarchyProperty = $this->mOtherArgs[HierarchyBuilder::PROPERTYNAME];
		} else {
			$this->mHierarchyProperty = null;
			return;
		}

		if ( array_key_exists( HierarchyBuilder::TITLEICONPROPERTY, $this->mOtherArgs ) ) {
			$this->mTitleiconProperty = $this->mOtherArgs[HierarchyBuilder::TITLEICONPROPERTY];
		} else {
			$this->mTitleiconProperty = null;
		}

		if ( array_key_exists( HierarchyBuilder::COLLAPSED, $this->mOtherArgs ) ) {
			$this->mCollapsed = $this->mOtherArgs[HierarchyBuilder::COLLAPSED];
			if ( $this->mCollapsed ) {
				$this->mCollapsed = 'true';
			}
			if ( $this->mCollapsed !== 'true' && $this->mCollapsed !== 'false' ) {
				$this->mCollapsed = null;
				return;
			}
		} else {
			$this->mCollapsed = 'false';
		}

		if ( array_key_exists( HierarchyBuilder::THREESTATE, $this->mOtherArgs ) ) {
			$this->mThreestate = $this->mOtherArgs[HierarchyBuilder::THREESTATE];
			if ( $this->mThreestate ) {
				$this->mThreestate = 'true';
			}
			if ( $this->mThreestate !== 'true' && $this->mThreestate !== 'false' ) {
				$this->mThreestate = null;
				return;
			}
		} else {
			$this->mThreestate = 'false';
		}

		if ( array_key_exists( HierarchyBuilder::WIDTH, $this->mOtherArgs ) ) {
			$this->mWidth = $this->mOtherArgs[HierarchyBuilder::WIDTH];
		} else {
			$this->mWidth = '';
		}

		if ( array_key_exists( HierarchyBuilder::HEIGHT, $this->mOtherArgs ) ) {
			$this->mHeight = $this->mOtherArgs[HierarchyBuilder::HEIGHT];
		} else {
			$this->mHeight = '';
		}

		/*
		 * Execute the logic
		 */

		// IFF a wikitext hierarchy was NOT provided as input, then we use pagename and hierarchyproperty
		if ( $this->mHierarchy == null ) {
			if ( $this->mPageName == '' || $this->mHierarchyProperty == '' ) {
				// TODO: log an error of some sort
				return;
			}
			$this->mHierarchy = HierarchyBuilder::getPropertyFromPage(
				$this->mPageName,
				$this->mHierarchyProperty
			);
		}

		// regardless of how we got this wikitext hierarchy, convert it to HTML
		$this->mHierarchy = HierarchyBuilder::parseWikitext2Html(
			$this->mHierarchy,
			$this->mTitleiconProperty
		);

		$this->mSelectedItems = array_map( 'trim', explode( ',', $this->mCurrentValue ) );

		global $wgPageFormsFieldNum;
		$this->mDivId = "hierarchy_$wgPageFormsFieldNum";
		$this->mInputId = "input_$wgPageFormsFieldNum";
		$jsattribs = array(
			'divId' => $this->mDivId,
			'hierarchy' => $this->mHierarchy,
			'selectedItems' => $this->mSelectedItems,
			'isDisabled' => $this->mIsDisabled,
			'isMandatory' => array_key_exists( 'mandatory', $this->mOtherArgs ),
			'collapsed' => $this->mCollapsed == 'true' ? true : false,
			'threestate' => $this->mThreestate == 'true' ? true : false,
			'width' => $this->mWidth,
			'height' => $this->mHeight// ,
			// 'legacyMode' => $HierarchyBuilder_LegacyMode
		);

		return json_encode( $jsattribs );
	}

	/**
	 * Gets eror messages for display.
	 */
	public function getHtmlText() {

		if ( $this->mHierarchy == null ) {
			if ( $this->mPageName == null ) {
				return Html::element( 'b', array(),
					wfMessage( 'hierarchybuilder-missing-page-name' )->text() );
			}

			if ( $this->mHierarchy == null ) {
				return Html::element( 'b', array(),
					wfMessage( 'hierarchybuilder-missing-property-name' )->text() );
			}
		}

		return Html::element( 'input', array(
			'type' => 'hidden',
			'id' => $this->mInputId,
			'name' => $this->mInputName,
			'value' => $this->mCurrentValue ) ) .
			Html::element( 'div', array( 'id' => $this->mDivId ) );
	}

	public static function getParameters() {
		$params = parent::getParameters();
		$params[HierarchyBuilder::HIERARCHY] = array(
			'name' => HierarchyBuilder::HIERARCHY,
			'type' => 'string',
			'description' =>
				wfMessage( 'hierarchybuilder-hierarchy-desc' )->text()
		);
		$params[HierarchyBuilder::PAGENAME] = array(
			'name' => HierarchyBuilder::PAGENAME,
			'type' => 'string',
			'description' =>
				wfMessage( 'hierarchybuilder-pagename-desc' )->text()
		);
		$params[HierarchyBuilder::PROPERTYNAME] = array(
			'name' => HierarchyBuilder::PROPERTYNAME,
			'type' => 'string',
			'description' =>
				wfMessage( 'hierarchybuilder-propertyname-desc' )->text()
		);
	}

	public function getResourceModuleNames() {
		return array(
			'ext.HierarchyBuilder.select',
			'ext.HierarchyBuilder.jstree'
		);
	}
}
