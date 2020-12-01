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

class HierarchyFormInput extends PFFormInput {

	public function __construct( $inputNumber, $curValue, $inputName,
		$disabled, $otherArgs ) {
		parent::__construct( $inputNumber, $curValue, $inputName, $disabled,
			$otherArgs );
		$this->addJsInitFunctionData( 'editHierarchyInit',
			$this->setupJsInitAttribs() );
	}

	public static function getName() {
		return 'hierarchy';
	}

	/**
	 * Retrieves all the necessary information for the initialization of the 
	 * javascript Edit Hierarchy user interface.
	 *
	 * @return string JSON encoded parameters for calling editHierarchy JS code.
	 */
	protected function setupJsInitAttribs() {

		if ( array_key_exists( HierarchyBuilder::TITLEICONPROPERTY, $this->mOtherArgs ) ) {
			$titleiconProperty = $this->mOtherArgs[HierarchyBuilder::TITLEICONPROPERTY];
		} else {
			$titleiconProperty = '';
		}

		if ( array_key_exists( HierarchyBuilder::HIDEINFO, $this->mOtherArgs ) ) {
			$hideinfoProperty = $this->mOtherArgs[HierarchyBuilder::HIDEINFO];
		} else {
			$hideinfoProperty = 'false';
		}

		if ( array_key_exists( HierarchyBuilder::CATEGORY, $this->mOtherArgs ) ) {
			$this->mCategory = $this->mOtherArgs[HierarchyBuilder::CATEGORY];
		} else {
			$this->mCategory = null;
		}

		if ( array_key_exists( HierarchyBuilder::PROPERTYVALUE, $this->mOtherArgs ) ) {
			$propertyValue = $this->mOtherArgs[HierarchyBuilder::PROPERTYVALUE];
		} else {
			$propertyValue = null;
		}

		$pageArray = self::getUnusedPages($this->mCategory, $propertyValue);

		$pages = array();
		foreach ( $pageArray as $key => $value ) {
			$pages[$value] = HierarchyBuilder::getPageDisplayName( $value );
		}

		// This loop will removed pages from the unselected pages list if we can
		// find it in the hierarchy already.
		foreach ( $pages as $key => $value ) {
			if ( strpos( '[[' . $this->mCurrentValue . ']]', $key ) !== false ) {
				unset( $pages[$key] );
			}
		}

		$unusedpagesmessage = wfMessage( 'hierarchybuilder-unusedpages' )->text();
		$unusedpages = "<ul>" .
			"<li class='hierarchy_root'><a>$unusedpagesmessage</a>";
		if ( count($pages) > 0 ) {
			$unusedpages .=	"<ul>";
			foreach ( $pages as $key => $value ) {
				$name = $value;
				$namehtml = "<a>$name<span style=display:none>$key</span></a>";

				if ($titleiconProperty != '') {
					$pagetitleiconshtml = HierarchyBuilder::getPageTitleIconsHtml( $key, $titleiconProperty );
				} else {
					$pagetitleiconshtml = '';
				}

				$pagerowhtml = "<li>" . $pagetitleiconshtml . $namehtml . "</li>";
				$unusedpages .= $pagerowhtml;
			}
			$unusedpages .= "</ul>";
		}
		$unusedpages .= "</li></ul>";

		$hierarchyrootmessage = wfMessage ('hierarchybuilder-hierarchyroot' )->text();
		$rootedWikitextHierarchy = "[[$hierarchyrootmessage]]\n" . $this->mCurrentValue;
		$rootedHtmlHierarchy = HierarchyBuilder::parseWikitext2Html( $rootedWikitextHierarchy, $titleiconProperty );

		global $wgPageFormsFieldNum;
		$this->mDivId = "hierarchy_$wgPageFormsFieldNum";
		$this->mInputId = "input_$wgPageFormsFieldNum";
		$jsattribs = array(
			'divId' => $this->mDivId,
			'hierarchy' => $rootedHtmlHierarchy,
			'pages' => $unusedpages,
			'isDisabled' => $this->mIsDisabled,
			'hideinfo' => $hideinfoProperty,
			'isMandatory' => array_key_exists( 'mandatory', $this->mOtherArgs ),
			'revealEditMessage' =>
				wfMessage( 'hierarchybuilder-reveal-editmessage' )->text(),
			'message' =>
				wfMessage( 'hierarchybuilder-editmessage', $this->mCategory )->text(),
			'errormessage' =>
				wfMessage( 'hierarchybuilder-edit-error-message' )->text(),
			'hierarchyroot' =>
				wfMessage( 'hierarchybuilder-hierarchyroot' )->text(),
			'unusedpages' =>
				wfMessage( 'hierarchybuilder-unusedpages' )->text()
		);
		return json_encode( $jsattribs );
	}

	/**
	 * Find and return all of the pages that we wish to have available for use
	 * when creating/updating a hierarchy using the edit form.
	 *
	 * Pages are collected based on the provided category and property/value.
	 * Only those pages that satisfy BOTH parameters will be returned. 
	 * Specifically, if only one of category and property/value is provided,
	 * then all pages that satisfy the one provided argument will be returned.
	 * However, if both a category and a property/value are provided, then only
	 * those pages that belong to the specified category and have the property
	 * set with the correct value will be returned.
	 *
	 * @param string $category: The name of the category from which pages will
	 *  be drawn.
	 * @param string $propertyValue: The propertyname::value that pages must
	 *  contain to be returned. (eg: Display Name::A Cool Name)
	 *
	 * @return string[] List of qualified pages that should be available when
	 *  editing the hierarchy.
	 */
	private function getUnusedPages($category, $propertyValue) {
		$categoryPageArray = array();
		if ($category != null) {
			$params = array();
			$params[] = "[[Category:$this->mCategory]]";
			$params[] = "link=none";
			$params[] = "limit=2000";
			$output = SMWQueryProcessor::getResultFromFunctionParams( $params,
				SMW_OUTPUT_WIKI );
			$categoryPageArray = $output === "" ? array() : array_map( 'trim', explode( ',', $output ) );
		}
		
		$propertyValuePageArray = array();
		if ($propertyValue != null) {
			$params = array();
			$params[] = "[[$propertyValue]]";
			$params[] = "link=none";
			$params[] = "limit=2000";
			$output = SMWQueryProcessor::getResultFromFunctionParams( $params,
				SMW_OUTPUT_WIKI );
			$propertyValuePageArray = $output === "" ? array() : array_map( 'trim', explode( ',', $output ) );
		}

		if ($category != null && $propertyValue != null) {
			$pageArray = array_intersect($categoryPageArray, $propertyValuePageArray);
		} else {
			$pageArray = array_merge($categoryPageArray, $propertyValuePageArray);
		}

		return $pageArray;
	}

	/**
	 * Get error messages for display.
	 *
	 * @return string HTML formatted message for display.
	 */
	public function getHtmlText() {

		/*if ( $this->mCategory == null ) {
			return Html::element( 'b', array(),
				wfMessage( 'hierarchybuilder-missing-category' )->text() );
		}*/

		return Html::element( 'input', array(
			'type' => 'hidden',
			'id' => $this->mInputId,
			'name' => $this->mInputName,
			'value' => $this->mCurrentValue ) ) .
			Html::element( 'div', array( 'id' => $this->mDivId ) );
	}

	public static function getParameters() {
		$params = parent::getParameters();
		$params[HierarchyBuilder::CATEGORY] = array(
			'name' => HierarchyBuilder::CATEGORY,
			'type' => 'string',
			'description' =>
				wfMessage( 'hierarchybuilder-category-desc' )->text()
		);
	}

	public function getResourceModuleNames() {
        return array(
            'ext.HierarchyBuilder.edit',
            'ext.HierarchyBuilder.jstree'
        );
	} 
}
