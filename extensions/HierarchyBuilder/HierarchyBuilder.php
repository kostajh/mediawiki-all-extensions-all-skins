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

use MediaWiki\MediaWikiServices;

class HierarchyBuilder {
	static protected $m_hierarchy_num = 1;

	// pattern to extract the pageName from a wikitext hierarchy row
	const PAGENAMEPATTERN = '/\[\[(.*)\]\]/';
	// pattern to extract the leading *s to determine the current row's depth
	// in the wikitext hierarchy.
	const DEPTHPATTERN = '/^(\**)/';

	// constants for arg names
	const PAGENAME = 'pagename';
	const PROPERTYNAME = 'propertyname';
	const SEPARATOR = 'sep';
	const TEMPLATE = 'template';
	const INTROTEMPLATE = 'introtemplate';
	const OUTROTEMPLATE = 'outrotemplate';
	const LINK = 'link';
	const FORMAT = 'format';
	const TITLEICONPROPERTY = 'titleiconproperty';
	const DISPLAYMODE = 'displaymode';
	const SHOWROOT = 'showroot';
	const COLLAPSED = 'collapsed';
	const PRUNED = 'pruned';
	const THREESTATE = 'threestate';
	const HIDEINFO = 'hideinfo';
	const WIDTH = 'width';
	const HEIGHT = 'height';
	const CATEGORY = 'category';
	const NUMBERED = 'numbered';
	const SELECTED = 'selected';
	const PROPERTYVALUE = 'propertyvalue';
	const HIERARCHYARGTYPE = 'hierarchyargtype';
	const HIERARCHY = 'hierarchy';

	// constants for arg values
	const HIERARCHYARGTYPE_PROPERTY = 'propertyname';
	const HIERARCHYARGTYPE_WIKITEXT = 'wikitext';

	/**
	 * This parser function will give the section number of a page in a hierarchy.
	 *
	 * The three required arguments are (in order):
	 *   - Full page name
	 *   - Full page name of the page containing the hierarchy
	 *   - Hierarchy (either a propertyname or a wikitext hierarchy)
	 *
	 * 1 optional named argument:
	 *   - hierarchyargtype = ["propertyname" | "wikitext"]
	 *
	 * Example invokation:
	 * @code
	 * {{#hierarchySectionNumber:{{FULLPAGENAME}}|Table of Contents|Hierarchy Data}}
	 * {{#hierarchySectionNumber:{{FULLPAGENAME}}|<wikitext hierarchy>|hierarchyargtype=wikitext}}
	 * @endcode
	 *
	 * @param $parser: Parser
	 *
	 * @return string: The section number of the specified page name within the
	 *  specified hierarchy.
	 */
	public static function hierarchySectionNumber( $parser ) {
		$parser->getOutput()->updateCacheExpiry( 0 );
		$params = func_get_args();
		if ( count( $params ) < 3 ) {
			$output = "";
		} else {
			// look for the hierarchyArgType parameter to determine how to parse the rest of the arguments
			$paramArray = array_slice( $params, 1 );
			$paramArray = self::parseParams( $paramArray );
			if  ( isset( $paramArray[HierarchyBuilder::HIERARCHYARGTYPE] ) ) {
				$hierarchyArgType = $paramArray[HierarchyBuilder::HIERARCHYARGTYPE];
			} else {
				$hierarchyArgType = HierarchyBuilder::HIERARCHYARGTYPE_PROPERTY;
			}

			// now that we know how hierarchyArgType looks, we can now apply the proper parameter parsing rules
			if ($hierarchyArgType == HierarchyBuilder::HIERARCHYARGTYPE_PROPERTY) {
				// perform "normal" parsing where the first three args are positional
				$pageName = $params[1];
				$hierarchyPageName = $params[2];
				$hierarchyProperty = $params[3];

				// at this point, we might be here by mistake (e.g. the user mispelled the named hierarchyargtype parameter) so we need to verify that we actually got 3 positional args, otherwise we return nothing
				if (strpos($pageName, '=') !== false || 
					strpos($hierarchyPageName, '=') !== false ||
					strpos($hierarchyProperty, '=') !== false) 
				{
					$output = '';
				}

				// go ahead and extract the actual wikitext hierarchyProperty based on the hierarchypagename and hierarchyproperty
				$hierarchy = self::getPropertyFromPage( $hierarchyPageName, $hierarchyProperty );
			} else {
				// perform "special" parsing where only the first two args are positional
				$pageName = $params[1];
				$hierarchy = $params[2];
			}

			// now that we've parsed the args properly, perform the logic
			$output = HierarchyBuilder::getSectionNumberFromHierarchy(
				$pageName,
				$hierarchy
			);
		}
		return $parser->insertStripItem( $output, $parser->mStripState );
		//return $parser->insertStripItem( "2.0", $parser->mStripState );
	}



	/**
	 * This parser function will return the immediate parent of a given page within
	 * a hierarchy on a page.
	 *
	 * 3 unnamed mandatory args:
	 *  - pagename
	 *  - hierarchy page
	 *  - hierarchy property
	 *
	 * 6 named optional args:
	 *  - hierarchyargtype = ['propertyname' | 'wikitext']
	 *  - sep = [, | ; | ... ]
	 *  - template = any template taking a single param
	 *  - introtemplate = any template with no params
	 *  - outrotemplate = any tempalte with no params
	 *  - link = [none]
	 *
	 * Example invokations:
	 * @code
	 * {{#hierarchyChildren:{{FULLPAGENAME}}|hierarchy page|hierarchy property}}
	 * {{#hierarchyChildren:{{FULLPAGENAME}}|hierarchy page|hierarchy property|sep=,}}
	 * {{#hierarchyChildren:{{FULLPAGENAME}}|hierarchy page|hierarchy property|sep=,|link=none}}
	 * {{#hierarchyChildren:{{FULLPAGENAME}}|hierarchy page|hierarchy property|sep=,|template=X|link=none}}
	 * {{#hierarchyChildren:{{FULLPAGENAME}}|hierarchy page|hierarchy property|sep=,|template=X|introtemplate=Y|outrotemplate=Z|link=none}}
	 * {{#hierarchyChildren:{{FULLPAGENAME}}|<wikitext hierarchy>|hierarchyargtype=wikitext}}
	 * @endcode
	 *
	 * @param $parser: Parser
	 *
	 * @return string: The list of children of the specified page within the specified
	 *  hierarchy. The list is returned as wikitext for formatted display according to
	 *  the various separator, template, and link parameters.
	 */
	public static function hierarchyChildren( $parser ) {
		$params = func_get_args();
		if ( count( $params ) < 3 ) {
			$output = '';
		} else {
			// first look for the hierarchyArgType parameter to determine how to parse the rest of the arguments
			$paramArray = array_slice( $params, 1 );
			$paramArray = self::parseParams( $paramArray );
			if  ( isset( $paramArray[HierarchyBuilder::HIERARCHYARGTYPE] ) ) {
				$hierarchyArgType = $paramArray[HierarchyBuilder::HIERARCHYARGTYPE];
			} else {
				$hierarchyArgType = HierarchyBuilder::HIERARCHYARGTYPE_PROPERTY;
			}

			// now that we know how hierarchyArgType looks, we can now apply the proper parameter parsing rules
			if ($hierarchyArgType == HierarchyBuilder::HIERARCHYARGTYPE_PROPERTY) {
				// perform "normal" parsing where the first three args are positional
				$pageName = $params[1];
				$hierarchyPageName = $params[2];
				$hierarchyProperty = $params[3];

				// at this point, we might be here by mistake (e.g. the user mispelled the named hierarchyargtype parameter) so we need to verify that we actually got 3 positional args, otherwise we return nothing
				if (strpos($pageName, '=') !== false || 
					strpos($hierarchyPageName, '=') !== false ||
					strpos($hierarchyProperty, '=') !== false) 
				{
					$output = '';
				}

				// go ahead and extract the actual wikitext hierarchyProperty based on the hierarchypagename and hierarchyproperty
				$hierarchy = self::getPropertyFromPage( $hierarchyPageName, $hierarchyProperty );
			} else {
				// perform "special" parsing where only the first two args are positional
				$pageName = $params[1];
				$hierarchy = $params[2];
			}

			// look for all the optional args in any order. We really don't care if
			// the right combination of optional parameters appears at this point.
			// The logic for handling different parameter combinations will happen
			// after pulling children when we attempt to return results.

			// look for the template parameter
			if ( isset( $paramArray[HierarchyBuilder::TEMPLATE] ) ) {
				$template = $paramArray[HierarchyBuilder::TEMPLATE];
			} else {
				$template = '';
			}
			// look for the introtemplate parameter
			if ( isset( $paramArray[HierarchyBuilder::INTROTEMPLATE] ) ) {
				$introTemplate = $paramArray[HierarchyBuilder::INTROTEMPLATE];
			} else {
				$introTemplate = '';
			}
			// look for the outrotemplate parameter
			if ( isset( $paramArray[HierarchyBuilder::OUTROTEMPLATE] ) ) {
				$outroTemplate = $paramArray[HierarchyBuilder::OUTROTEMPLATE];
			} else {
				$outroTemplate = '';
			}
			// look for the link parameter
			if ( isset( $paramArray[HierarchyBuilder::LINK] ) ) {
				$link = $paramArray[HierarchyBuilder::LINK];
			} else {
				$link = '';
			}
			// look for the delimiter parameter
			if ( isset( $paramArray[HierarchyBuilder::SEPARATOR] ) ) {
				$delimiter = $paramArray[HierarchyBuilder::SEPARATOR];
			} else {
				if ( $template != '' ) {
					$delimiter = '';
				} else {
					$delimiter = ',';
				}
			}

			// find the page children
			$children = HierarchyBuilder::getPageChildren( $pageName, $hierarchy );

			// format the output according to the optional params
			$output = '';
			if ( count( $children ) > 0 ) {
				if ( $template != '' ) {
					$intro = $introTemplate != '' ? "{{{$introTemplate}}}\n" : '';
					$outro = $outroTemplate != '' ? "\n{{{$outroTemplate}}}" : '';
					$templateChildrenString = implode(
						array_map(
							function( $child ) use ( $template, $link ) {
								if ( $link == 'none' ) {
									return "{{" . $template . "|$child}}";
								} else {
									return "{{" . $template . "|[[$child]]}}";
								}
							},
							$children
						),
						"$delimiter\n"
					);
					$output = $intro . $templateChildrenString . $outro;
				} else {
					$childrenString = implode(
						array_map(
							function( $child ) use ( $link ) {
								return $link == 'none' ? $child : "[[$child]]";
							},
							$children
						),
						$delimiter
					);

					$output = $childrenString;
				}
			}
			$output = $parser->recursiveTagParse( $output );
		}
		return $parser->insertStripItem( $output, $parser->mStripState );
	}

	/**
	 * Returns the direct hierarchical children of a page in a hierarchy.
	 *
	 * This function searches a specified hierarchy for the direct children of
	 * a particular page. The search proceeds by searching the hierarchy top
	 * down to find the target page, and then looping further to identify the
	 * immediate children. Once the loop to find immediate children finds a row
	 * which has depth less than or equal to the target then the search is
	 * complete.
	 *
	 * If the target page is empty, then target page is assumed to be the
	 * invisible/imaginary super root node "Hierarchy Root" and all the root
	 * level nodes in the hierarchy will be returned.
	 *
	 * If there are no immediate children in the hierarchy for the
	 * specified target page, then this function returns an empty array.
	 *
	 * @param string $targetPageName: is the name of the target page for which
	 *  we want the list of immediate children. If this is empty, then we will
	 *  assume the target is the invisible super root node "Hierarchy Root" and
	 *  return all of the root level nodes from the hierarchy.
	 * @param string $hierarchyPageName: is the name of the page containing the
	 *  hierarchy from which to retrieve the list of children.
	 * @param string $hierarchy: is the name of the property on the
	 *  hierarchy page which contains the hierarchy data. ex: Hierarchy Data.
	 * @param string $hierarchyArgType: indicator if $hierarchy is a property
	 *  or wikitext. Possible values are "propertyname" and "wikitext".
	 *
	 * @return array: A list strings consisting of the hierarchical children of
	 *  the target page within the hierarchy.
	 */
	private static function getPageChildren( 
		$targetPageName,
		$hierarchy
	) {
		// handle the strange empty target case first
		if ( $targetPageName == '' ) {
			$rootRows = self::getHierarchyRowsByDepth( '*', $hierarchy );

			$children = array();
			foreach ( $rootRows as $rootRow ) {
				array_push( $children, self::getPageNameFromHierarchyRow( $rootRow ) );
			}

			return $children;
		}

		// handle the normal case where a target page is given

		$hierarchyRows = preg_split( '/\n/', $hierarchy );

		$currentPagePattern = '/\[\[' . $targetPageName . '\]\]/';

		$hierarchyRowsSize = count( $hierarchyRows );
		for ( $i = 0; $i < $hierarchyRowsSize; $i++ ) {
			$row = $hierarchyRows[$i]; // current row that we're looking at in the hierarchy
			// look to see if this row is the one with our page
			$numMatches = preg_match_all( $currentPagePattern, $row, $matches );
			if ( $numMatches > 0 ) { // found the current page on this row of the hierarchy
				$children = self::getChildren( $hierarchyRows, $row, $i );
				return $children;
			}
		}

		return array();
	}

	/**
	 * Helper function that find the direct hierarchical children of a row within
	 * a hierarchy represented as an array of rows.
	 *
	 * This function will find the immediate children of $row within $hierarchyRows
	 * and return the pageNames of those children. The immediate children are
	 * defined to be any rows which come after $row with depth exactly equal to
	 * one greater than the depth of $row AND which come before the first subsequent
	 * row with depth less than or equal to the depth of $row.
	 *
	 * Note: If no immediate children of $row are found, then an empty array is
	 * returned instead of a list of children;
	 *
	 * @param array $hierarchyRows: Array representation of a wikitext hierarchy
	 *  where each row in the hierarchy is a separate element of the array.
	 * @param string $row: A specific row of the hierarchy given by
	 *  $hierarchyRows.
	 * @param number $rowIdx: The index of $row in $hierarchyRows.
	 *
	 * @return array: List of string consisting of the hierarchical children of
	 *  $row within $hierarchyRows.
	 */
	private static function getChildren( $hierarchyRows, $row, $rowIdx ) {
		// figure out what the depth of the current page is. if we can't find
		// any depth (leading *s) then set the depth to 0 indicating failure.
		$currentDepth = self::getDepthOfHierarchyRow( $row );

		// figure out who the immediate children are based on depth being 1 more
		// than the current depth
		$children = array();
		// run backwards through all the previous rows
		$hierarchyRowsSize = count( $hierarchyRows );
		for ( $childIdx = $rowIdx + 1; $childIdx < $hierarchyRowsSize; $childIdx++ ) {
			$childRow = $hierarchyRows[$childIdx];
			$childDepth = self::getDepthOfHierarchyRow( $childRow );

			if ( $childDepth <= $currentDepth ) {
				// if we've run out of rows that are deeper than the currentrow
				// then it's impossible to find more direct children
				return $children;
			} elseif ( $childDepth > $currentDepth + 1 ) {
				// if the depth is more than 1 deeper than the current row then
				// this is not an immediate child and we should continue searching
				continue;
			} else {
				array_push( $children, self::getPageNameFromHierarchyRow( $childRow ) );
			}
		}

		return $children;
	}

	/**
	 * This parser function will return a list of the immediate children of a given
	 * page within a hierarchy on a page. The list of chilren will be delimited by
	 * a specified character or the ',' character by default if no delimiter is given.
	 *
	 * 3 unnamed mandatory args:
	 *  - pagename
	 *  - hierarchy page
	 *  - hierarchy (either property value or wikitext)
	 *
	 * 2 named optional arg:
	 *  - hierarchyargtype = ['propertyname' | 'wikitext']
	 *  - link = [none]
	 *
	 * Example invokations:
	 * @code
	 * {{#hierarchyParent:{{FULLPAGENAME}}|hierarchy page name|hierarchy property}}
	 * {{#hierarchyParent:{{FULLPAGENAME}}|hierarchy page name|hierarchy property|link=none}}
	 * {{#hierarchyParent:{{FULLPAGENAME}}|<wikitext hierarchy>|hierarchyargtype=wikitext}}
	 * @endcode
	 *
	 * @param $parser Parser
	 *
	 * @return string: The parent of the specified page within the specified hierarchy
	 *  as wikitext for formatted display.
	 */
	public static function hierarchyParent( $parser ) {
		$params = func_get_args();
		if ( count( $params ) < 3 ) {
			$output = "";
		} else {
			// first look for the hierarchyArgType parameter to determine how to parse the rest of the arguments
			$paramArray = array_slice( $params, 1 );
			$paramArray = self::parseParams( $paramArray );
			if  ( isset( $paramArray[HierarchyBuilder::HIERARCHYARGTYPE] ) ) {
				$hierarchyArgType = $paramArray[HierarchyBuilder::HIERARCHYARGTYPE];
			} else {
				$hierarchyArgType = HierarchyBuilder::HIERARCHYARGTYPE_PROPERTY;
			}

			// now that we know how hierarchyArgType looks, we can now apply the proper parameter parsing rules
			if ($hierarchyArgType == HierarchyBuilder::HIERARCHYARGTYPE_PROPERTY) {
				// perform "normal" parsing where the first three args are positional
				$pageName = $params[1];
				$hierarchyPageName = $params[2];
				$hierarchyProperty = $params[3];

				// at this point, we might be here by mistake (e.g. the user mispelled the named hierarchyargtype parameter) so we need to verify that we actually got 3 positional args, otherwise we return nothing
				if (strpos($pageName, '=') !== false || 
					strpos($hierarchyPageName, '=') !== false ||
					strpos($hierarchyProperty, '=') !== false) 
				{
					$output = '';
				}

				// go ahead and extract the actual wikitext hierarchyProperty based on the hierarchypagename and hierarchyproperty
				$hierarchy = self::getPropertyFromPage( $hierarchyPageName, $hierarchyProperty );
			} else {
				// perform "special" parsing where only the first two args are positional
				$pageName = $params[1];
				$hierarchy = $params[2];
			}

			// look for all the optional args in any order. We really don't care if
			// the right combination of optional parameters appears at this point.
			// The logic for handling different parameter combinations will happen
			// after pulling children when we attempt to return results.

			// look for the template parameter
			if ( isset( $paramArray[HierarchyBuilder::TEMPLATE] ) ) {
				$template = $paramArray[HierarchyBuilder::TEMPLATE];
			} else {
				$template = '';
			}
			// look for the introtemplate parameter
			if ( isset( $paramArray[HierarchyBuilder::INTROTEMPLATE] ) ) {
				$introTemplate = $paramArray[HierarchyBuilder::INTROTEMPLATE];
			} else {
				$introTemplate = '';
			}
			// look for the outrotemplate parameter
			if ( isset( $paramArray[HierarchyBuilder::OUTROTEMPLATE] ) ) {
				$outroTemplate = $paramArray[HierarchyBuilder::OUTROTEMPLATE];
			} else {
				$outroTemplate = '';
			}
			// look for the link parameter
			if ( isset( $paramArray[HierarchyBuilder::LINK] ) ) {
				$link = $paramArray[HierarchyBuilder::LINK];
			} else {
				$link = '';
			}
			// look for the delimiter parameter
			if ( isset( $paramArray[HierarchyBuilder::SEPARATOR] ) ) {
				$delimiter = $paramArray[HierarchyBuilder::SEPARATOR];
			} else {
				if ( $template != '' ) {
					$delimiter = '';
				} else {
					$delimiter = ',';
				}
			}

			// find the parents
			$parents = HierarchyBuilder::getPageParent( $pageName, $hierarchy );

			// format the parents for return according to the optional arg
			// this code is the same as below for children
			$output = '';
			if ( count( $parents ) > 0 ) {
				if ( $template != '' ) {
					$intro = $introTemplate != '' ? "{{{$introTemplate}}}\n" : '';
					$outro = $outroTemplate != '' ? "\n{{{$outroTemplate}}}" : '';
					$templateParentString = implode(
						array_map(
							function( $parent ) use ( $template, $link ) {
								if ( $link == 'none' ) {
									return "{{" . $template . "|$parent}}";
								} else {
									return "{{" . $template . "|[[$parent]]}}";
								}
							},
							$parents
						),
						"$delimiter\n"
					);
					$output = $intro . $templateParentString . $outro;
				} else {
					$parentString = implode(
						array_map(
							function( $parent ) use ( $link ) {
								return $link == 'none' ? $parent : "[[$parent]]";
							},
							$parents
						),
						$delimiter
					);

					$output = $parentString;
				}
			}
			$output = $parser->recursiveTagParse( $output );
		}
		return $parser->insertStripItem( $output, $parser->mStripState );
	}

	/**
	 * Returns the hierarchical parent of a page within a hierarchy.
	 *
	 * This function searches a specified hierarchy for the direct parent of a
	 * particular page. The search proceeds by searching the hierarchy top down
	 * to find the target page, and then looping back upwards to identify the
	 * immediate parent. If there is no immediate parent in the hierarchy for
	 * the specified target page, or if the target page is not included in the
	 * hierarchy at all, then this function returns the empty string.
	 *
	 * @param string $targetPageName: The name of the target page for which we
	 *  want the immediate parent in the hierarchy.
	 * @param string $hierarchyPageName: The name of the page containing the
	 *  hierarchy from which to retrieve the immediate parent of the target page.
	 * @param string $hierarchy: Either, a wikitext hierarchy or, the name of
	 *  the property on the hierarchy page which contains the hierarhcy data.
	 *  (ex: Hierarchy Data)
	 * @param string $hierarchyArgType: indicator if $hierarchy is a property
	 *  or wikitext. Possible values are "propertyname" and "wikitext".
	 *
	 * @return array: The hierarchical parents of target page instances within
	 *  the hierarchy.
	 */

	private static function getPageParent( $targetPageName, $hierarchy) {
		$hierarchyRows = preg_split( '/\n/', $hierarchy );

		// array to store the parents of the target instances
		$parents = array();

		$currentPagePattern = '/\[\[' . $targetPageName . '\]\]/';
		// loop through the hierarchyRows looking for the row containing the currentPage
		$hierarchyRowsSize = count( $hierarchyRows );
		for ( $i = 0; $i < $hierarchyRowsSize; $i++ ) {
			$row = $hierarchyRows[$i]; // current row that we're looking at in the hierarchy
			// look to see if this row is the one with our page
			$numMatches = preg_match_all( $currentPagePattern, $row, $matches );
			if ( $numMatches > 0 ) { // found the current page on this row of the hierarchy
				// Get the parent of the current row in the hierarchy.
				// Note that if there is no hierarchical parent, then the parent
				// will be empty.
				$parent = self::getParent( $hierarchyRows, $row, $i );
				if ( $parent != '' ) {
					array_push( $parents, $parent );
				}
			}
		}

		return $parents;
	}

	/**
	 * This parser function will return only specific selected rows of a hierarchy
	 * in addition to any necessary contextual rows.
	 *
	 * The returned hierarchy is displayd similarly to the HierarchySelectFormInput,
	 * with each row preceeded by a checkbox. However, the checkboxes will be inactive.
	 *
	 * For a given set of selected rows, only those rows will be provided from the
	 * hierarchy in addition to the minimal necessary contextual rows needed to display
	 * the hierarchical relationships. For example, if a single selected row is given,
	 * but that row is a leaf node which is 5 levels deep within the hierarchy, then
	 * that row will be given along with each of its ancestors. This is conidered the
	 * "pruned" behavior.
	 *
	 * The "collapsed" behavior will not remove any branches of the hierarchy, even
	 * when those branches do not contain any of the specified selected rows. Instead,
	 * these unnecessary branches will be collapsed initially, allowing only the
	 * selected rows and their siblings to be shown.
	 *
	 * 3 unnamed mandatory args:
	 *  - pagename
	 *  - hierarchy page
	 *  - hierarchy (either property value or wikitext)
	 *
	 * 1 unnamed optional arg
	 *  - collapsed = ['collapsed' | 'pruned']
	 *
	 * 1 named optional arg:
	 *  - hierarchyargtype = ['propertyname' | 'wikitext']
	 *
	 * @param $parser: Parser
	 * @return string
	 *
	 * Example invokation:
	 * @code
	 * {{#hierarchySelected:<list of page names>|<hierarchy page name>|<hierarchy property>}}
	 * {{#hierarchySelected:<list of page names>|<hierarchy page name>|<hierarchy property>|pruned}}
	 * {{#hierarchySelected:<list of page names>|<hierarchy page name>|<hierarchy property>|collapsed}}
	 * {{#hierarchySelected:<list of page names>|<wikitext hierarchy>|hierarchyargtype=wikitext}}
	 * @endcode
	 *
	 * TODO: This function is a mess and should be refactored a lot probably
	 */
	public static function hierarchySelected( $parser ) {
		$params = func_get_args();
		if ( count( $params ) < 3) {
			$output = '';
		} else {
			// first look for the hierarchyArgType parameter to determine how to parse the rest of the arguments
			$paramArray = array_slice( $params, 1 );
			$paramArray = self::parseParams( $paramArray );
			if  ( isset( $paramArray[HierarchyBuilder::HIERARCHYARGTYPE] ) ) {
				$hierarchyArgType = $paramArray[HierarchyBuilder::HIERARCHYARGTYPE];
			} else {
				$hierarchyArgType = HierarchyBuilder::HIERARCHYARGTYPE_PROPERTY;
			}

			// now that we know how hierarchyArgType looks, we can now apply the proper parameter parsing rules
			if ($hierarchyArgType == HierarchyBuilder::HIERARCHYARGTYPE_PROPERTY) {
				// perform "normal" parsing where the first three args are positional
				$selectedPages = $params[1];
				$hierarchyPageName = $params[2];
				$hierarchyProperty = $params[3];

				// at this point, we might be here by mistake (e.g. the user mispelled the named hierarchyargtype parameter) so we need to verify that we actually got 3 positional args, otherwise we return nothing
				if (strpos($selectedPages, '=') !== false || 
					strpos($hierarchyPageName, '=') !== false ||
					strpos($hierarchyProperty, '=') !== false) 
				{
					$output = '';
				}

				// go ahead and extract the actual wikitext hierarchyProperty based on the hierarchypagename and hierarchyproperty
				$hierarchy = self::getPropertyFromPage( $hierarchyPageName, $hierarchyProperty );
			} else {
				// perform "special" parsing where only the first two args are positional
				$selectedPages = $params[1];
				$hierarchy = $params[2];
			}

			// look for all the optional args in any order. We really don't care if
			// the right combination of optional parameters appears at this point.
			// The logic for handling different parameter combinations will happen
			// after pulling children when we attempt to return results.
		
			// look for the collasped parameter
			if ( isset( $paramArray[HierarchyBuilder::COLLAPSED] ) ) {
				$displayMode = $paramArray[HierarchyBuilder::COLLAPSED];
			} else {
				$displayMode = 'pruned';
			}
			if ( isset( $paramArray[HierarchyBuilder::TITLEICONPROPERTY] ) ) {
				$titleiconproperty =
					htmlspecialchars( $paramArray[HierarchyBuilder::TITLEICONPROPERTY] );
			} else	{
				$titleiconproperty = '';
			}

			// this is where we ask HierarchyBuilder class to actually do the work for us.
			$hierarchyTree = HierarchyTree::fromWikitext( $hierarchy );

			$normalizedSelectedPages =
				array_map(
					function( $page ) {
						$pagename = HierarchyBuilder::getPageNameFromHierarchyRow( $page );
						if ( $pagename == '' ) {
							$pagename = trim( $page );
						}
						return trim( $pagename );
					},
					explode( ',', $selectedPages )
				);

			$mst = $hierarchyTree->getMST( $normalizedSelectedPages );

			// output formatting
			$flatNormalizedSelectedPages =
				array_reduce( $normalizedSelectedPages,
						function( $carry, $item ) {
							if ( $carry == '' ) {
								$carry = $item;
							} else {
								$carry .= ',' . $item;
							}
							return $carry;
						}
					);

			$selected = htmlspecialchars( str_replace( " ", "%20", $flatNormalizedSelectedPages	) );

			$output = '';
			if ( $titleiconproperty != '' ) {
					$titleiconproperty = "titleiconproperty=\"$titleiconproperty\"";
				}
			if ( $displayMode == 'collapsed') {
				$output =
					"<hierarchySelected collapsed selected=$selected $titleiconproperty>" .
					(string)$mst .
					'</hierarchySelected>';
			} else {
				$output =
					"<hierarchySelected selected=$selected $titleiconproperty>" .
					(string)$mst .
					'</hierarchySelected>';
			}

			$output = $parser->recursiveTagParse( $output );

		}
		return $parser->insertStripItem( $output, $parser->mStripState );
	}

	/**
	 * This parser function displays a breadcrumb for a page within a hierarchy.
	 *
	 * The breadcrumb display consists of three pages:
	 *  - previous page in the hierarchy
	 *  - next page in the hierarchy
	 *  - hierarchical parent page in the hierarchy.
	 *
	 * There are 3 required parameters for this parser function:
	 *  - pagename - that page who's breadcrumb you want to display.
	 *  - hierarchy page - the page that has the hierarchy on it.
	 *  - hierarchy - either a wikitext hierarchy or the property containing
	 *    the hierarchy data on the hierarchy page.
	 *
	 * 1 named optional parameter:
	 *  - hierarchyargtype = ["propertyname" | "wikitext"]
	 *
	 * Example Usage:
	 * @code
	 * {{#hierarchyBreadcrumb:{{FULLPAGENAME}}|Table of Contents|Hierarchy Data}}
	 * {{#hierarchyBreadcrumb:{{FULLPAGENAME}}|<wikitext hierarchy>|hierarchyargtype=wikitext}}
	 * @endcode
	 *
	 * @param $parser Parser
	 *
	 * @return string: Wikitext that displays the breadcrumb on the page.
	 */
	public static function hierarchyBreadcrumb( $parser ) {
		$params = func_get_args();

		if ( count( $params ) < 3 ) {
			$output = "";
		} else {
			// first look for the hierarchyArgType parameter to determine how to parse the rest of the arguments
			$paramArray = array_slice( $params, 1 );
			$paramArray = self::parseParams( $paramArray );
			if  ( isset( $paramArray[HierarchyBuilder::HIERARCHYARGTYPE] ) ) {
				$hierarchyArgType = $paramArray[HierarchyBuilder::HIERARCHYARGTYPE];
			} else {
				$hierarchyArgType = HierarchyBuilder::HIERARCHYARGTYPE_PROPERTY;
			}

			// now that we know how hierarchyArgType looks, we can now apply the proper parameter parsing rules
			if ($hierarchyArgType == HierarchyBuilder::HIERARCHYARGTYPE_PROPERTY) {
				// perform "normal" parsing where the first three args are positional
				$currentPage = $params[1];
				$hierarchyPageName = $params[2];
				$hierarchyProperty = $params[3];

				// at this point, we might be here by mistake (e.g. the user mispelled the named hierarchyargtype parameter) so we need to verify that we actually got 3 positional args, otherwise we return nothing
				if (strpos($currentPage, '=') !== false || 
					strpos($hierarchyPageName, '=') !== false ||
					strpos($hierarchyProperty, '=') !== false) 
				{
					$output = '';
				}

				// go ahead and extract the actual wikitext hierarchyProperty based on the hierarchypagename and hierarchyproperty
				$hierarchy = self::getPropertyFromPage( $hierarchyPageName, $hierarchyProperty );
			} else {
				// perform "special" parsing where only the first two args are positional
				$currentPage = $params[1];
				$hierarchy = $params[2];
			}

			$output = self::constructBreadcrumb( $currentPage,
				$hierarchy );
			$output = $parser->recursiveTagParse( $output );
		}

		$parser->getOutput()->updateCacheExpiry( 0 );
		return array( $parser->insertStripItem( $output, $parser->mStripState ),
			'noparse' => false );
	}

	/**
	 * Compute and return the breadcrumb for a given page within a hierarchy.
	 *
	 * This function will compute and return the breadcrumb information for a
	 * given page based on the position of that page within a specified hierarchy.
	 * The returned breadcrumb will show the display name for the pages it contains.
	 * The previous page in the beadcrumb will have a "back" arrow or an "up" arrow
	 * If it is the same as the immediate parent. The immediate parent will have an
	 * "up" arrow unless it is the same as the previous page, in which case the
	 * parent will not be displayed. The next page will have a "forward" arrow.
	 *
	 * @param string $currentPage: Name of the page in the hierarchy that is
	 *  currently being viewed.
	 * @param string $hierarchyPage: Name of the page that contains the hierarchy
	 *  to which $currentPage belongs.
	 * @param string $hierarchy: Either a wikitext hierarchy or the name of the
	 *  property on $hierarchyPage that actually stores the wikitext formatted
	 *  hierarchy.
	 * @param string $hierarchyArgType: indicator if $hierarchy is a propery name
	 *  or a wikitext hierarchy. Possible values are "propertyname" or "wikitext".
	 *
	 * @return string: Formatted wikitext that will format and display the
	 *  breadcrumb information on the page.
	 */
	private static function constructBreadcrumb( $currentPage, $hierarchy) {
		$hierarchyRows = preg_split( '/\n/', $hierarchy );

		$currentPagePattern = '/\[\[' . $currentPage . '\]\]/';
		// loop through the hierarchyRows looking for the row containing the currentPage
		$hierarchyRowsSize = count( $hierarchyRows );
		for ( $i = 0; $i < $hierarchyRowsSize; $i++ ) {
			// current row that we're looking at in the hierarchy
			$row = $hierarchyRows[$i];
			// look to see if this row is the one with our page
			$numMatches = preg_match_all( $currentPagePattern, $row, $matches );
			if ( $numMatches > 0 ) { // found the current page on this row of the hierarchy
				// go to the previous row and extract the page name if any
				// previous row exists. Otherwise the previous page name is empty.
				$prevIdx = $i -1;
				$previousRow = ( $prevIdx >= 0 ? $hierarchyRows[$prevIdx] : '' );
				$previous = self::getPageNameFromHierarchyRow( $previousRow );

				// go to the next row and extract the page name if any next row
				// exists. Otherwise the next page name is empty.
				$nextIdx = $i + 1;
				$nextRow = ( $nextIdx < count( $hierarchyRows ) ? $hierarchyRows[$nextIdx] : '' );
				$next = self::getPageNameFromHierarchyRow( $nextRow );

				// get the parent of the current row in the hierarchy.
				// Note that if there is no hierarchical parent, then the parent will be empty.
				$parent = self::getParent( $hierarchyRows, $row, $i );

				return self::formatBreadcrumb( $previous, $parent, $next );
			}
		}

		return '';
	}

	/**
	 * Return the wikitext formatted breadcrumb using the given information.
	 *
	 * This function gives formatted wikitext for rendering a breadcrumb trail
	 * on a wikipage including the previous page, the parent page, and the next
	 * page within a hierarchy.
	 *
	 * @param string $previous: The name of the previous page in the hierarchy.
	 * @param string $parent: The name of the hierarchical parent page in the
	 *  hierarchy.
	 * @param string $next: The name of the next page in the hierarchy.
	 *
	 * @return string: Formatted wikitext which renders breadcrumbs on a page.
	 */
	private static function formatBreadcrumb( $previous, $parent, $next ) {
		$breadcrumb = "{| width='100%'" . PHP_EOL;
		if ( $previous != null ) {
			if ( $previous == $parent ) {
				$arrow = "&uarr;";
			} else {
				$arrow = "&larr;";
			}
			$breadcrumb .= "| width='33%' | " . $arrow . " [[" . $previous . "| " .
				HierarchyBuilder::getPageDisplayName( $previous ) . "]]" . PHP_EOL;
		} else {
			$breadcrumb .= "| width='33%' | &nbsp;" . PHP_EOL;
		}
		if ( $parent != null && $parent != $previous ) {
			$breadcrumb .= "| align='center' width='33%' | &uarr; [[" . $parent .
				"| " . HierarchyBuilder::getPageDisplayName( $parent ) . "]]" . PHP_EOL;
		} else {
			$breadcrumb .= "| width='33%' | &nbsp;" . PHP_EOL;
		}
		if ( $next != null ) {
			$breadcrumb .= "| align='right' width='33%' | [[" . $next . "|" .
				HierarchyBuilder::getPageDisplayName( $next ) . "]] &rarr;" . PHP_EOL;
		} else {
			$breadcrumb .= "| width='33%' | &nbsp;" . PHP_EOL;
		}
		$breadcrumb .= "|}" . PHP_EOL;
		return $breadcrumb;
	}

	/**
	 * Find and return the hierarchical parent of a row within a hierarchy.
	 *
	 * This function will find the hierarchical parent of $row within $hierarchyRows
	 * and return the pageName of that parent. The hierarchical parent is defined
	 * to be the first row of $hierarchyRows which preceeds $row and has a depth
	 * that is equal to one less than the depth of $row.
	 *
	 * Note: If no hierarchical parent of $row is found, then the empty string
	 * is returned;
	 *
	 * @param array $hierarchyRows: Array representation of a wikitext hierarchy
	 *  where each row in the hierarchy is a separate element of the array.
	 * @param string $row: A specific row of the hierarchy given by $hierarchyRows.
	 * @param number $rowIdx: The index of $row in $hierarchyRows.
	 *
	 * @return string: The hierarchical parent of $row within @$hierarchyRows
	 *  or the empty string if there is no parent. Note that if the parent row
	 *  contains more than just a link to a page, then only the page name from
	 *  that row will be returned.
	 */
	private static function getParent( $hierarchyRows, $row, $rowIdx ) {
		// figure out what the depth of the current page is. if we can't find
		// any depth (leading *s) then set the depth to 0 indicating failure.
		$currentDepth = self::getDepthOfHierarchyRow( $row );

		// figure out who the parent is based on depth being 1 less than the current depth
		$parent = '';
		// run backwards through all the previous rows
		for ( $parentIdx = $rowIdx -1; $parentIdx >= 0; $parentIdx-- ) {
			$parentRow = $hierarchyRows[$parentIdx];
			$parentDepth = self::getDepthOfHierarchyRow( $parentRow );

			if ( $parentDepth == $currentDepth - 1 ) {
				$parent = self::getPageNameFromHierarchyRow( $parentRow );
				break;
			}
		}

		return $parent;
	}

	/**
	 * Find and return the page name from a hierarchy row.
	 *
	 * This function will return the first pageName (link) found within $hierarchyRow
	 *
	 * @param string $hierarchyRow: Assumed to be a row of a hierarchy in wikitext
	 *  format, which is to say, leading *s and a page name within [[]] delimiters.
	 *
	 * @return string: The first page name found within $hierarchyRow.
	 */
	public static function getPageNameFromHierarchyRow( $hierarchyRow ) {
		$numMatches = preg_match_all( self::PAGENAMEPATTERN, $hierarchyRow, $matches );
		// give me the first subpattern match to be the name of the previous page
		$pageName = ( $numMatches > 0 ? $matches[1][0] : '' );
		return $pageName;
	}

	/**
	 * Returns the depth of a row within a hierarchy.
	 *
	 * This function will return the number of leading *s in $hierarchyRow as
	 * the depth of $hierarchyRow.
	 *
	 * @param string $hierarchyRow: Assumed to be a row of a hierarchy in wikitext
	 *  format, which is to say, leading *s and a page name within [[]] delimiters.
	 *
	 * @return number: The depth of $hierarchyRow.
	 */
	private static function getDepthOfHierarchyRow( $hierarchyRow ) {
		$numMatches = preg_match_all( self::DEPTHPATTERN, $hierarchyRow, $matches );
		$depth = ( $numMatches > 0 ? strlen( $matches[1][0] ) : 0 );
		return $depth;
	}

	/**
	 * Return all the rows of a specified depth from within the given hierarchy.
	 *
	 * @param string $depth: The depth of the rows which should be returned
	 * @param string $hierarchy: The wikitext hierarchy to use
	 *
	 * @return array: The list of rows that are at the specified depth within
	 *  the given hierarchy. Note that the returned list is a list of complete
	 *  rows from the hierarchy and not a list of page names extracted from those
	 *  rows.
	 */
	private static function getHierarchyRowsByDepth( $depth, $hierarchy) {
		$hierarchyRows = preg_split( '/\n/', $hierarchy );

		$rowsOfDepth = array();
		$hierarchyRowsSize = count( $hierarchyRows );
		for ( $i = 0; $i < $hierarchyRowsSize; $i++ ) {
			$row = $hierarchyRows[$i];
			$rowDepth = self::getDepthOfHierarchyRow( $row );

			if ( $rowDepth == strlen( $depth ) ) {
				array_push( $rowsOfDepth, $row );
			}
		}

		return $rowsOfDepth;
	}

	private static $renderHierarchies = array();

	/**
	 * Renders a wikitext formatted hierarchy on a page.
	 *
	 * This function implements the hierarchy tag extension for rendering a
	 * hierarchical representation of pages within a wiki. Hierarchy data is
	 * taken as wikitext within the <hierarchy> tag, parsed into HTML and passed
	 * to the javascript code for rendering.
	 *
	 * @param string $input: Wikitext hierarchy data
	 * @param array $attributes: List of optional attributes for customizing the
	 *  display of the hierarchy. The optional attributes are:
	 *   - collapsed - the hierarchy should initially be collapsed
	 *   - numbered - rows of the hierarchy should be given section numbers.
	 * @param $parser: Parser
	 * @param $frame: Frame
	 *
	 * @return string: Html div that will contain the rendered hierarchy.
	 */
	public static function renderHierarchy( 
		$input,
		$attributes,
		$parser,
		$frame
	) {
		$hierarchyName = 'HierarchyDiv' . self::$m_hierarchy_num;
		self::$m_hierarchy_num++;

		if ( isset( $attributes[HierarchyBuilder::COLLAPSED] ) ) {
			$collapsed = true;
		} else	{
			$collapsed = false;
		}

		if ( isset( $attributes[HierarchyBuilder::NUMBERED] ) ) {
			$numbered = true;
		} else {
			$numbered = false;
		}

		if ( isset( $attributes[HierarchyBuilder::TITLEICONPROPERTY] ) ) {
			$titleiconproperty =
				htmlspecialchars( $attributes[HierarchyBuilder::TITLEICONPROPERTY] );
		} else	{
			$titleiconproperty = '';
		}

		// this looks like it gets the property but it eats all the links.
		$input = $parser->recursiveTagParse( $input, $frame );
		$input = self::anchorLinkHolders( $input );
		$input = $parser->replaceLinkHoldersText( $input );
		$input = $parser->parse( $input,
			$parser->getTitle(),
			$parser->Options(),
			true,
			false )->getText();

		$hierarchy = HierarchyBuilder::parseHierarchy( $input,
			$titleiconproperty,
			function ( $pageName, $titleiconproperty ) {
				$pageLinkArray = array();
				$title = Title::newFromText( $pageName );
				if ( $title ) {
					$pageLinkArray['href'] = $title->getLinkURL();
				}

				$displayName = HierarchyBuilder::getPageDisplayName( $pageName );

				$iconElement = self::getPageTitleIconsHtml( $pageName, $titleiconproperty );

				return $iconElement . Html::element( 'a', $pageLinkArray, $displayName );
			} );

		$parser->getOutput()->addModules( 'ext.HierarchyBuilder.render' );

		self::$renderHierarchies[] = array(
			'div' => $hierarchyName,
			'hierarchy' => $hierarchy,
			'collapsed' => $collapsed,
			'numbered' => $numbered
		);
		$parser->getOutput()->addJsConfigVars( 'HierarchyBuilderRender', self::$renderHierarchies );

		$output = Html::element( 'div', array( 'id' => $hierarchyName ) );
		$parser->getOutput()->updateCacheExpiry( 0 );
		return array( $parser->insertStripItem( $output, $parser->mStripState ),
			'noparse' => false );
	}

	private static $renderHierarchiesSelected = array();

	/**
	 * TODO: Document this!!!
	 */
	public static function renderHierarchySelected(
		$input,
		$attributes,
		$parser,
		$frame
	) {
		$hierarchyName = 'HierarchyDiv' . self::$m_hierarchy_num;
		self::$m_hierarchy_num++;

		if ( isset( $attributes[HierarchyBuilder::COLLAPSED] ) ) {
			$collapsed = true;
		} else	{
			$collapsed = false;
		}

		if ( isset( $attributes[HierarchyBuilder::NUMBERED] ) ) {
			$numbered = true;
		} else {
			$numbered = false;
		}

		if ( isset( $attributes[HierarchyBuilder::TITLEICONPROPERTY] ) ) {
			$titleiconproperty =
				htmlspecialchars( $attributes[HierarchyBuilder::TITLEICONPROPERTY] );
		} else	{
			$titleiconproperty = '';
		}

		if ( isset( $attributes[HierarchyBuilder::SELECTED] ) ) {

			$selectedPages =
				array_map(
					function ($pageName){
						return HierarchyBuilder::getPageDisplayName( $pageName );
					},
					explode( ',', urldecode( $attributes[HierarchyBuilder::SELECTED] ) )
				);
		} else	{
			$selectedPages = '';
		}

		// this looks like it gets the property but it eats all the links.
		$input = $parser->recursiveTagParse( $input, $frame );
		$input = self::anchorLinkHolders( $input );
		$input = $parser->replaceLinkHoldersText( $input );
		$input = $parser->parse( $input,
			$parser->getTitle(),
			$parser->Options(),
			true,
			false )->getText();

		$hierarchy = HierarchyBuilder::parseHierarchy( $input,
			$titleiconproperty,
			function ( $pageName, $titleiconproperty ) {
				$pageLinkArray = array();
				$title = Title::newFromText( $pageName );
				if ( $title ) {
					$pageLinkArray['href'] = $title->getLinkURL();
				}

				$displayName = HierarchyBuilder::getPageDisplayName( $pageName );

				$iconElement = self::getPageTitleIconsHtml( $pageName, $titleiconproperty );

				return $iconElement . Html::element( 'a', $pageLinkArray, $displayName );
			} 
		);

		$parser->getOutput()->addModules( 'ext.HierarchyBuilder.renderSelected' );

		self::$renderHierarchiesSelected[] = array(
			'div' => $hierarchyName,
			'hierarchy' => $hierarchy,
			'collapsed' => $collapsed,
			'numbered' => $numbered,
			'selectedPages' => $selectedPages
		);
		$parser->getOutput()->addJsConfigVars( 'HierarchyBuilderRenderSelected', self::$renderHierarchiesSelected );

		$output = Html::element( 'div', array( 'id' => $hierarchyName ) );

		$parser->getOutput()->updateCacheExpiry( 0 );
		return array( $parser->insertStripItem( $output, $parser->mStripState ),
			'noparse' => false );
	}

	/**
	 * This function will find all link placeholders within a hierarchy and
	 * replace them with html anchors.
	 *
	 * @param string $hierarchy: Wikitext formatted hierarchy containing some
	 *  link placeholders (e.g. <!--LINK 0:0-->)
	 *
	 * @return string: Wikitext formatted hierarchy where with all the link
	 *  placeholders removed and replaced by html anchors.
	 */
	private static function anchorLinkHolders( $hierarchy ) {
		$pattern = '#<!--LINK\'" \d+:\d+-->#';
		$numMatches = preg_match_all( $pattern, $hierarchy, $matches );
		if ( $numMatches !== false ) {
			foreach ( $matches[0] as $link ) {
				$hierarchy = str_replace( "$link", "<a>$link</a>", $hierarchy );
			}
		}

		return $hierarchy;
	}

	/**
	 * Parse a hierarchy into html.
	 *
	 * @param string $input: WikiText hierarchy.
	 * @param function $callback: Function for processing links.
	 *
	 * @return string: Updated HTML formatted hierarchy with functional links.
	 */
	public static function parseHierarchy(
		$input,
		$titleIconProperty,
		$callback
	) {
		$hierarchy = htmlspecialchars_decode( $input );
		$newlines = array( "\n", "\r" );
		$hierarchy = str_replace( $newlines, '', $hierarchy );
		$pattern = '/<a>([^<]*)<\/a>/i';
		$numMatches = preg_match_all( $pattern, $hierarchy, $matches );
		if ( $numMatches !== false ) {
			foreach ( $matches[1] as $pageName ) {
				$link = $callback( trim( $pageName ), $titleIconProperty );
				$hierarchy = str_replace( "<a>$pageName</a>", $link, $hierarchy );
			}
		}
		return $hierarchy;
	}

	/**
	 * This function will return the value of the specified property from the
	 * given page.
	 *
	 * @param string $page: Name of the page from which to retrieve a property.
	 * @param string $property: Name of the property that should be returned.
	 * @param boolean $firstonly: Determine if only the first value is returned.
	 *
	 * @return string: The value of the specified property from the given page
	 *  or the empty string if the property does not exist.
	 */
	public static function getPropertyFromPage( 
		$page, 
		$property, 
		$firstonly = true
	) {
		if ($page == '' || $property == '' || $property == null) {
			return '';
		}
		try {
			$store = smwfGetStore();
			$title = Title::newFromText( $page );
			$subject = SMWDIWikiPage::newFromTitle( $title );
			$data = $store->getSemanticData( $subject );
			$property = SMWDIProperty::newFromUserLabel( $property );
			$values = $data->getPropertyValues( $property );

			$strings = array();
			foreach ( $values as $value ) {
				if ( ( defined( 'SMWDataItem::TYPE_STRING' ) &&
					$value->getDIType() == SMWDataItem::TYPE_STRING ) ||
					$value->getDIType() == SMWDataItem::TYPE_BLOB ) {
					if ($firstonly){
						return trim( $value->getString() );
					} else {
						$strings[] = trim( $value->getString() );
					}
				}
			}
			if ( $firstonly ) {
				return '';
			} else {
				return $strings;
			}
		} catch (Exception $e) {
			wfLogWarning(
				"[HierarchyBuilder.class.php][getPropertyFromPage] " .
				"Something broke. Returning an empty string."
			);
			return '';
		}

	}

	/**
	 * This function gives the display name for the specified page when using
	 * content free page names.
	 *
	 * @param string $page: Name of the page.
	 *
	 * @return string: The display name of the specified page.
	 */
	public static function getPageDisplayName( $page ) {
		$displayname = $page;

		$title = Title::newFromText( $page );
		if ($title) {
			$id = $title->getArticleID();

			$dbr = wfGetDB( DB_REPLICA );
			$result = $dbr->select(
				'page_props',
				array( 'pp_value' ),
				array(
					'pp_page' => $id,
					'pp_propname' => 'displaytitle'
				),
				__METHOD__
			);

			if ( $result->numRows() > 0 ) {
				$row = $result->fetchRow();
				$displayname = $row['pp_value'];
			}
		}

		// note that if anything fails in the pipeline we return the pagename
		return htmlspecialchars_decode($displayname);
	}

	/**
	 * Returns all the page names found in the hierarchy.
	 *
	 * This function will search through the hierarchy to find all the page names
	 * (defined by [[]] syntax) and return them as an array without [[]] syntax.
	 *
	 * @param string $hierarchy: A wikitext formatted hierarchy.
	 *
	 * @return array: An array of all the page names found within the hierarchy.
	 *  If no page name are found, then an empty array is returned instead.
	 */
	public static function collectPageNamesFromHierarchy( $hierarchy ) {
		// use a regex to find all of the page names
		$numMatches = preg_match_all( HierarchyBuilder::PAGENAMEPATTERN, $hierarchy, $matches );
		return ( $numMatches > 0 ? $matches[1] : array() );
	}

	/**
	 * Updates a hierarchy so page links are shown using their display names.
	 *
	 * This function will run through the hierarchy and for each pageName link
	 * found, it will find the displayName for that pageName, and then update
	 * the link syntax so that the displayName will be shown instead.
	 *
	 * @param string $hierarchy: A wikitext formatted hierarchy.
	 *
	 * @return string: The wikitext formatted hierarchy after the links have
	 *  been updated to reflect the displayName instead of the page name.
	 *
	 * @example [[pageName]] -> [[pageName | displayName]]
	 */
	public static function updateHierarchyWithDisplayNames( $hierarchy ) {
		$hierarchyPageNames = self::collectPageNamesFromHierarchy( $hierarchy );
		foreach ( $hierarchyPageNames as $pageName ) {
			$displayName = self::getPageDisplayName( $pageName );
			$pageNameLink = '[[' . $pageName . ']]';
			$displayNameLink = '[[' . $pageName . ' | ' . $displayName . ']]';
			$hierarchy = str_replace( $pageNameLink, $displayNameLink, $hierarchy );
		}
		return $hierarchy;
	}

	/**
	 * Returns the section number for a page within a wikitext formatted hierarchy.
	 *
	 * This function will search a hierarchy for a target page name and will
	 * return the section number for the row which contains that page. The
	 * target is a simple page name and the requirement is that a matching row
	 * must consist only of a single link to the target page. (eg: [[$target]])
	 * We do not yet support non-page rows.
	 *
	 * @param string $targetPageName: name of the target page for which you want
	 *  the auto-number in the given hierarchyPage returned.
	 * @param string $hierarchyPageName: name of the page containing the hierarchy
	 *  from which to retrieve numberings.
	 * @param string $hierarchy: name of the property on the hierarchy
	 *  page which contains the hierarchy data. (ex: Hierarchy Data).
	 * @param string $hierarchyArgType: indicator if $hierarchy is either a
	 *  propertyname or a wikitext hierarchy.
	 *
	 * @return string: The section numer of the target page within the hierarchy.
	 */
	private static function getSectionNumberFromHierarchy(
		$targetPageName,
		$hierarchy
	) {
		$sectionNumber = self::getSectionNumberFromHierarchyHelper(
			'[[hierarchy_root]]' . "\n" . $hierarchy, '', '', $targetPageName );
		return $sectionNumber;
	}

	/**
	 * Helper function for traversing a wikitext formatted hierarchy and computing
	 * a page's section number.
	 *
	 * This function will recursively traverse the given hierarchy/subhierarchy
	 * and search for the given target row. The target is a simple page name and
	 * the requirement is that a matching row must consist only of a single link
	 * to the target page. (eg: [[$target]]) We do not yet support non-page rows.
	 *
	 * @param string $wikiTextHierarchy: The hierarchy in wikitext.
	 * @param string $depth: A string containing the number of stars equal to
	 *  the current depth in the hierarchy.
	 * @param string $sectionNumber: The section number of the root of the current
	 *  subhierarchy.
	 * @param string $target: The page name of the page we're searching the
	 *  hierarchy for.
	 *
	 * @return string: The section number of the page within the hierarchy. If
	 *  we cannot find the page within the hierarchy, then the empty string is
	 *  returned instead.
	 */
	private static function getSectionNumberFromHierarchyHelper(
		$wikiTextHierarchy,
		$depth,
		$sectionNumber,
		$target
	) {
		$rootAndChildren = self::splitHierarchy( $wikiTextHierarchy, $depth );
		// this is just the root row of this hierarchy (or subhierarchy)
		$root = $rootAndChildren[0];
		// this is a list of direct children hierarchies of the root. It might
		// be an empty list though
		$children = array_slice( $rootAndChildren, 1 );

		$rootPageName = self::getPageNameFromHierarchyRow( $root, false );

		// if we are staring at the target then return the current section number for the target
		if ( $rootPageName == $target ) {
			return $sectionNumber;
		}

		// if there are children, then recurse on each one searching for the target
		if ( count( $children ) > 0 ) {
			$numberSuffix = 1;
			foreach ( $children as $child ) {
				$childNumber = $sectionNumber == '' ? $numberSuffix++ : $sectionNumber . '.' . $numberSuffix++;
				$targetNumber = self::getSectionNumberFromHierarchyHelper( $child, $depth . '*',
					$childNumber, $target );
				// if we find the target in this child branch then we can return
				// the target section number
				if ( $targetNumber != '' ) {
					return $targetNumber;
				}
			}
		}

		// if we can't find the target then return an empty section number
		return '';
	}

	/**
	 * This function takes a hierarchy and splits it into the root, and each of
	 * of the root's child subhierarchies. These are returned as an array with
	 * the root first and each of the root's child subhierarchies in order.
	 *
	 * @param string $wikiTextHierarchy: The hierarchy that needs to be split
	 *  into root, and the root's child subhierarchies.
	 * @param string $depth: The string representing the current depth of the
	 *  hierarchy root. It can be interpreted as the depth of the root of the
	 *  wikiTextHierarchy when viewed as a subhierarchy within another hierarchy.
	 *
	 * @return array: An array with the root as the first element and each
	 *  child subhierarchy as a subsequent element.
	 */
	public static function splitHierarchy( $wikiTextHierarchy, $depth ) {
		$nextDepth = "\n" . $depth . "*";
		$r1 = "/\*/"; // this guy finds * characters
		// this is building the regex that will be used later
		$regex = preg_replace( $r1, "\\*", $nextDepth ) . "(?!\\*)";
		$regex = "/" . $regex . "/";
		// actually split the hierarchy into root and children
		$rootAndChildren = preg_split( $regex, $wikiTextHierarchy );

		return $rootAndChildren;
	}

	/**
	 * This parser function will return the subhierarchy that is rooted at the
	 * specified node within a hierarchy.
	 *
	 * The three required arguments are (in order):
	 *   - The root node of the subhierarchy within the overall hierarchy. If
	 *     this argument is empty, then the entire hierarchy is returned.
	 *   - Full page name of the page containing the hierarchy
	 *   - hierarchy which is either a wikitext hierarchy or the name of the 
	 *     property containing the hierarchy data.
	 *
	 * The optional arguments are:
	 *   - hierarchyargtype = ["propertyname" | "wikitext"]
	 *   - Format to specify if the results should be returned as a bulleted
	 *     list as opposed to the default striped format.
	 *   - titleiconproperty to specify the property containing the titleicons
	 *	   that should be displayed for each page in the hierarchy.
	 *   - showroot to specify if the root of the subhierarchy should be
	 *     included in the display.
	 *   - collapsed to specify whether or not the subhierarchy should be
	 *     initialized in collapsed mode.
	 *
	 * Example invokation:
	 * @code
	 * {{#hierarchySubtree:|Main Page|Hierarchy Data}}
	 * {{#hierarchySubtree:Hierarchy Builder|Main Page|Hierarchy Data}}
	 * {{#hierarchySubtree:Hierarchy Builder|Main Page|Hierarchy Data|format=ul}}
 	 * {{#hierarchySubtree:Hierarchy Builder|Main Page|Hierarchy Data|showroot|collapsed|titleiconproperty=Logo Link}}
 	 * {{#hierarchySubtree:Hierarchy Builder|<wikitext hierarchy>|hierarchyargtype=wikitext}}
	 * @endcode
	 *
	 * @param $parser: Parser
	 *
	 * @return string: The string containing the specified subhierarchy as though
	 *  it were a standalone hierarchy.
	 */
	public static function hierarchySubtree( $parser ) {
		$params = func_get_args();
		if ( count( $params ) < 3 ) {
			$output = '';
		} else {
			// first look for the hierarchyArgType parameter to determine how to parse the rest of the arguments
			$paramArray = array_slice( $params, 1 );
			$paramArray = self::parseParams( $paramArray );
			if  ( isset( $paramArray[HierarchyBuilder::HIERARCHYARGTYPE] ) ) {
				$hierarchyArgType = $paramArray[HierarchyBuilder::HIERARCHYARGTYPE];
			} else {
				$hierarchyArgType = HierarchyBuilder::HIERARCHYARGTYPE_PROPERTY;
			}

			// now that we know how hierarchyArgType looks, we can now apply the proper parameter parsing rules
			if ($hierarchyArgType == HierarchyBuilder::HIERARCHYARGTYPE_PROPERTY) {
				// perform "normal" parsing where the first three args are positional
				$rootNode = $params[1];
				$hierarchyPageName = $params[2];
				$hierarchyProperty = $params[3];

				// at this point, we might be here by mistake (e.g. the user mispelled the named hierarchyargtype parameter) so we need to verify that we actually got 3 positional args, otherwise we return nothing
				if (strpos($rootNode, '=') !== false || 
					strpos($hierarchyPageName, '=') !== false ||
					strpos($hierarchyProperty, '=') !== false) 
				{
					$output = '';
				}

				// go ahead and extract the actual wikitext hierarchyProperty based on the hierarchypagename and hierarchyproperty
				$hierarchy = self::getPropertyFromPage( $hierarchyPageName, $hierarchyProperty );
			} else {
				// perform "special" parsing where only the first two args are positional
				$rootNode = $params[1];
				$hierarchy = $params[2];
			}

			// look for all the optional args in any order. We really don't care if
			// the right combination of optional parameters appears at this point.
			// The logic for handling different parameter combinations will happen
			// after pulling children when we attempt to return results.

			// look for the format parameter
			$format = '';
			if ( isset( $paramArray[HierarchyBuilder::FORMAT] ) ) {
				$format = $paramArray[HierarchyBuilder::FORMAT];
			}
			// look for the titleiconproperty parameter
			$titleiconproperty = '';
			if (isset( $paramArray[HierarchyBuilder::TITLEICONPROPERTY] ) ) {
				$titleiconproperty = $paramArray[HierarchyBuilder::TITLEICONPROPERTY];
			}
			// look for the showroot parameter
			$showroot = '';
			if ( isset( $paramArray[HierarchyBuilder::SHOWROOT] ) ) {
				$showroot = $paramArray[HierarchyBuilder::SHOWROOT];
			}
			if ( $rootNode == '' ) {
				$showroot = 'showroot';
			}
			// look for the collapsed parameter
			$collapsed = '';
			if ( isset( $paramArray[HierarchyBuilder::COLLAPSED] ) ) {
				$collapsed = $paramArray[HierarchyBuilder::COLLAPSED];
			}

			$output = HierarchyBuilder::getSubhierarchy(
				$rootNode,
				$hierarchy
			);

			// this is where we have to handle the default mode which is not showroot and not collapsed
			if ( $showroot == '' ) {
				// fix $output so only the children are given
				$hierarchyrows = preg_split( '/\n/', $output );
				$root = $hierarchyrows[0];
				$children = array_slice( $hierarchyrows, 1 );

				$depth = HierarchyBuilder::getDepthOfHierarchyRow( $root );
				$output = array_reduce( $children,
						function( $carry, $item ) use ( $depth ) {
							if ( $carry != '' ) {
								$carry .= "\n" . substr( $item, strlen( $depth ) );
							} else {
								$carry = substr( $item, strlen( $depth ) );
							}
							return $carry;
						}
					);
			}

			// this is the default output display format
			if ( $format != 'ul' ) {
				if ( $titleiconproperty != '' ) {
					$titleiconproperty = "titleiconproperty=\"$titleiconproperty\"";
				}
				$output = "<hierarchy $collapsed $titleiconproperty>$output</hierarchy>";
			}
			// otherwise it's the bulleted format and we don't modify output.

			$output = $parser->recursiveTagParse( PHP_EOL . $output );
		}
		return $parser->insertStripItem( $output, $parser->mStripState );
	}

	/**
	 * This function returns the subhierarchy defined by its root node within
	 * a specific hierarchy on a given page.
	 *
	 * The pagename and hierarchy arguments define the hierarchy from which
	 * to extract the subhierarchy. The root argument defines the root node of
	 * the subhierarchy within the overall hierarchy to extract. If the root
	 * argument is the empty string, then the entire hierarchy will be returned.
	 *
	 * If the hierarchyArgType isn't provided or has the value "propertyname",
	 * then it is the name of a property holding the wikitext hierarchy on the
	 * page called pagename.
	 *
	 * @param string $root: The node within the hierarchy which is the root of
	 *  the subhierarchy to be returned.
	 * @param string $pagename: The name of the page which contains the hierarchy.
	 * @param string $hierarchy: The name of the property that contains the
	 *  hierarchy data.
	 * @param string $hierarchyargtype: Indicator if $hierarchy is a wikitext
	 *  hierarchy or a propertyname. Possible values or "propertyname" or 
	 *  "wikitext".
	 *
	 * @return string: The depth corrected wikitext representation of the
	 *  subhierarchy within the overall hierarchy who's root is $root. Otherwise,
	 *  if no such subhierarchy exists, the empty string is returned instead.
	 */
	private static function getSubhierarchy( $root, $hierarchy ) {
		if ( $root == '' ) {
			return $hierarchy;
		} else {
			return HierarchyBuilder::getSubhierarchyHelper(
				$root,
				"[[Hierarchy_Root]]\n" . $hierarchy,
				''
			);
		}
	}

	/**
	 * This is single step look ahead recursive helper for finding a subhierarchy
	 * with matching root.
	 *
	 * The root of the wikitextHierarchy passed in is the immediate parent of the
	 * subhierarchies which are actually being tested for matching the targeted
	 * root. Once we find the correct subhierarchy, we need to update it before
	 * it is returned so that the depths are corrected so that it appears to be
	 * a standalone hierarchy. That is, if the root of the subhierarchy is at a
	 * depth of 4 and it's children are at a depth of 5, we must correct those
	 * depths such that the root will have a depth of 1 and the children have a
	 * depth of 2.
	 *
	 * @param string $root: The target root that we are searching for.
	 * @param string $wikitextHierarchy: The string wikitext representation of
	 *  the current hierarchy who's immediate subhieraries are being tested.
	 * @param string $depth: The depth of the current hierarchy who's
	 *  subhierarchies are being tested.
	 *
	 * @return string: The depth corrected wikitext representaion of the
	 *  subhierarchy who's root is $root if such a subhierarchy exists within
	 *  the hierarchy $wikitextHierarchy. Otherwise, the empty string is returned.
	 */
	private static function getSubhierarchyHelper( 
		$root,
		$wikitextHierarchy,
		$depth
	) {
		$curRootAndSubHierarchies = HierarchyBuilder::splitHierarchy( $wikitextHierarchy, $depth );
		$subHierarchies = array_slice( $curRootAndSubHierarchies, 1 );

		foreach ( $subHierarchies as $subHierarchy ) {
			$subHierarchyRows = preg_split( '/\n/', $subHierarchy );
			$subHierarchyRoot = HierarchyBuilder::getPageNameFromHierarchyRow( $subHierarchyRows[0] );
			if ( $subHierarchyRoot == $root ) {
				// put the stars on the root row to start
				$subHierarchyRows[0] =
					str_repeat( '*', strlen( $depth ) + 1 ) . $subHierarchyRows[0];
				$result = array_reduce( $subHierarchyRows,
					function( $carry, $item ) use ( $depth ) {
						if ( $carry != '' ) {
							$carry .= "\n" . substr( $item, strlen( $depth ) );
						} else {
							$carry = substr( $item, strlen( $depth ) );
						}
						return $carry;
					}
				);
				return $result;
			} else {
				$subHierarchyCandidate = HierarchyBuilder::getSubhierarchyHelper(
					$root, $subHierarchy, $depth . '*'
				);
				if ( $subHierarchyCandidate != '' ) {
					return $subHierarchyCandidate;
				}
			}
		}

		return '';
	}

	/**
	 * Parse a standard wikitext formatted hierarchy into HTML.
	 * 
	 * The wikitext hierarchy is assumed to be in the standard format without
	 * specified displaynames. (eg: [[pagename]] not [[pagename|displayname]])
	 * The parsing will correctly handle displaynames and titleicons (assuming
	 * the titleiconproperty is specified as an argument).
	 *
	 * This parser is utilized by the both the HierarchyFormInput for formedits
	 * and the HierarchySelectFormInput for "select from hierarchy".
	 *
	 * Note: as of version 5.2.0 the returned HTML hierarchy is not guaranteed
	 * to be rooted with hierarchybuilder-hierarchyroot. Instead, the returned
	 * HTML hierarchy will be exactly refelctive of the supplied wikitext.
	 *
	 * @param string $hierarchy: The wikitext formatted hieararchy to be parsed.
	 * @param string $titleiconpropery: (Optional) The name of the property 
	 *  containing the titleicons to be displayed for pages in the hierarchy.
	 *
	 * @return string: The HTML formatted hierarchy with both displaynames and
	 *  titleicons properly handled.
	 */
	public static function parseWikitext2Html(
		$hierarchy,
		$titleiconproperty = ''
	) {
		if ( $titleiconproperty == null ) {
			$titleiconproperty = '';
		}

		return
			HierarchyBuilder::parseWikitext2HtmlHelper($hierarchy, 0, $titleiconproperty);
	}

	/**
	 * Helper function for parsing wikitext hierarchies to HTML.
	 *
	 * @param string $subhierarchy: The current subhierarchy being processed.
	 * @param number $depth: The depth of the current subhierarchy within the
	 *  original overall hierarchy being parsed.
	 * @param string $titleiconproperty: The name of the property containing
	 *  the titleicons to be displayed for pages in the hierarchy.
	 *
	 * @return string: The HTML formatted subhierarchy hierarchy with both
	 *  displaynames and titleicons properly handled.
	 */
	private static function parseWikitext2HtmlHelper(
		$subhierarchy,
		$depth,
		$titleiconproperty
	) {
		/* Check if we can find any rows with the specified depth which will
		 * serve as our "roots" for this subhierarchy. We say "roots" because
		 * if we're at depth 1 in a hierarchy_root-less hierarcy then we could
		 * be looking at a multi-root hierarchy and find a bunch of root level
		 * nodes. But in every other case we're actually only going to have 1
		 * root.
		 * Generally, if we don't find anything at this depth, we are done, as
		 * this would be the base case. However, if we find nothing and we are
		 * at depth 0 we should increment depth to 1 and try again because this
		 * could be a case where we are given a hierarchy that has no special
		 * hierarchy_root node but is otherwise a well-formed hierarchy to be
		 * parsed into HTML.
		 */
		$depthpattern = '/^' . '\*'.'{'.$depth.'}' . '([^\*]+)' . '/m';
		$numRoots = preg_match_all( $depthpattern, $subhierarchy, $matches );
		if ($numRoots < 1) {
			if ( $depth == 0 ) { // maybe we just don't have a hierarchy_root node and should try again at depth 1
				return HierarchyBuilder::parseWikitext2HtmlHelper( 
					$subhierarchy, $depth+1, $titleiconproperty
				);
			} else { // this denotes one base case for our recursion
				return '';
			}
		}
		$rootrows = $matches[1];

		// Define the child forests (each index is the child forest for a single root at the corresponding index in $rootrows)
		$childforests = array_slice(
			preg_split( $depthpattern, $subhierarchy ), 1
		); // chop off element 0 which is the root

		// For each root, do shit (by this point we know there are roots)
		$html = "<ul>";
		for ( $i = 0; $i < $numRoots; $i++ ) { // yes we DO need to loop this way because we have to access corresponding indicies of both $rootrows and $childforests
			// grab the root available at index $i
			$rootrow = $rootrows[$i];
			$numMatches = preg_match_all( HierarchyBuilder::PAGENAMEPATTERN, $rootrow, $matches ); 	//extract the root pagename
			if ( $numMatches > 0 ) {
				$rootpagename = $matches[1][0]; // this is just the pagename excluding the [[]] formatting
			} else {
				$rootpagename = $rootrow; // if we can't find a link pattern just assume the whole row is the page name31
			}

			// build the root html list element
			if ($depth == 0) {
				$rootHtml = "<a>$rootpagename<span style=display:none>$rootpagename</span></a>";
			} else {
				if ($titleiconproperty != '') {
					$roottitleiconshtml = HierarchyBuilder::getPageTitleIconsHtml(
						$rootpagename, $titleiconproperty
					);
				} else {
					$roottitleiconshtml = '';
				}
				$rootdisplayname = HierarchyBuilder::getPageDisplayName($rootpagename);
				$roottexthtml = "$rootdisplayname<span style=display:none>$rootpagename</span>";
				$rootHtml = "<a>" . $roottitleiconshtml . $roottexthtml . "</a>";
			}

			// update $html to contain the root html list element (but we don't close that list element yet because we may need to nest the html for a child forest)
			$html .= $depth == 0 ? "<li class='hierarchy_root'>" : '<li>';
			$html .= $rootHtml;	

			// Recurse on this root's' child forest if it exists and update $html with the results
			$childforest = $childforests[$i];
			if ( strlen($childforest) > 0 ) { // if this test case fails then that denotes the second base case (Sort of. I'm stretching the definition of base case)
				$html .= HierarchyBuilder::parseWikitext2HtmlHelper($childforest, $depth+1, $titleiconproperty);
			}

			// update $html to close this root's list element
			$html .= "</li>";
		}
		$html .= "</ul>";

		return $html;
	}

	/**
	 * This function constructs the img html elements to display each of the
	 * given titleicons.
	 *
	 * @param array $icons: The array of pagename, titleicon pairs to be shown.
	 *
	 * @return string: The html for rendering all of the titleicons.
	 */
	private static function getIconsHTML( $icons ) {
		$iconhtmls = array();
		if ( method_exists( MediaWikiServices::class, 'getRepoGroup' ) ) {
			// MediaWiki 1.34+
			$repoGroup = MediaWikiServices::getInstance()->getRepoGroup();
		} else {
			$repoGroup = RepoGroup::singleton();
		}
		foreach ( $icons as $iconinfo ) {

			$page = $iconinfo["page"];
			$icon = $iconinfo["icon"];

			$filetitle = Title::newFromText( "File:" . $icon );
			$imagefile = $repoGroup->findFile( $filetitle );

			if ( $imagefile !== false ) {

				$tooltip = $page;

				$frameParams = array();
				//$frameParams['link-title'] = $page;
				$frameParams['alt'] = $tooltip;
				$frameParams['title'] = $tooltip;
				// this is where we might specify default height and width
				$handlerParams = array();

				$parser = \MediaWiki\MediaWikiServices::getInstance()->getParser();
				$iconhtmls[] = Linker::makeImageLink( $parser,
					$filetitle, $imagefile, $frameParams, $handlerParams );
			}

		}

		$finaliconshtml = '';
		foreach ( $iconhtmls as $iconhtml ){
			//extract just the guts of just the img part of the html
			$imgpattern = '/\<img (.*) \/\>/';
			$numMatches = preg_match_all( $imgpattern, $iconhtml, $matches );

			// build the new image html thing
			$finaliconshtml .= '<img class="hierarchy_row_titleicon" ' . $matches[1][0] . '/>';

		}

		return $finaliconshtml;
	}

	/**
	 * This function gives the titleicons for the specified page when using
	 * titleicons.
	 *
	 * @param string $page: Name of the page.
	 * @param string $titleIconProperty: Name of the property that stores
	 *  titleicon urls for pages when titleicons are active.
	 *
	 * @return array: The pagename, titleiconname pairs for the specified page.
	 */
	public static function getPageTitleIcons( $page, $titleIconProperty ) {
		// get the title icons for this page
		$discoveredIcons =
			HierarchyBuilder::getPropertyFromPage( $page, $titleIconProperty, false );

		$icons = array();
		if ( $discoveredIcons ) {

			foreach ( $discoveredIcons as $icon ) {

				$found = false;
				foreach ( $icons as $foundIcon ) {

					if ( $foundIcon["icon"] === $icon ) {
						$found = true;
						break;
					}

				}

				if ( $found == false ) {
					$entry = array();
					$entry["page"] = $page;
					$entry["icon"] = $icon;
					$icons[] = $entry;

				}

			}

		}

		return $icons;
	}

	/**
	 * This function gives the titleicons in formatted displayable HTML for
	 * the specified page when using titleicons.
	 *
	 * @param string $page: Name of the page.
	 * @param string $titleIconProperty: Name of the property that stores
	 *  titleicon urls for pages when titleicons are active.
	 *
	 * @return array: The pagename, titleiconname pairs for the specified page.
	 */
	public static function getPageTitleIconsHtml( $page, $titleIconProperty) {
		if ($page == '' || $titleIconProperty == '') {
			return '';
		}
		$icons = self::getPageTitleIcons( $page, $titleIconProperty );
		return count($icons) > 0 ? self::getIconsHtml( $icons ) : '';
	}

	/**
	 * Helper function for parsing a list of named parser function parameters.
	 *
	 * @param array $params: A list of named parameters (e.g. "array('sep=|', 'link=none'))
	 *
	 * @return array: Associative array of named parameters.
	 */
	private static function parseParams( $params ) {
		$paramsArray = array();
		foreach ( $params as $param ) {
			$paramsArray += self::parseParam( $param );
		}
		return $paramsArray;
	}

	/**
	 * Helper function for parsing a single named parser function parameter.
	 *
	 * @param string $param: A single named parameter (e.g. 'link=none')
	 *
	 * @param array: A single element associative array containing the named parameter.
	 */
	private static function parseParam( $param ) {
		$paramArray = array();
		$ret = preg_split( '/=/', $param, 2 );
		if ( count( $ret ) > 1 ) {
			$paramArray[$ret[0]] = $ret[1];
		} else {
			$paramArray[$ret[0]] = $ret[0];
		}
		return $paramArray;
	}
}
