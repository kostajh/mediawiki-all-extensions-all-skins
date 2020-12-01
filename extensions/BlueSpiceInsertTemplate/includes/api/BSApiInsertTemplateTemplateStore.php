<?php
/**
 * This class serves as a backend for the data store of the InsertTemplate
 * extension
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Josef Konrad <konrad@hallowelt.com>
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */
class BSApiInsertTemplateTemplateStore extends BSApiExtJSStoreBase {

	protected $aData = [];

	/**
	 * The API call method to get templates.
	 *
	 * @param string $sQuery
	 * @return array Returns favorite templates or the result from the query.
	 */
	protected function makeData( $sQuery = '' ) {
		$aFavorites = $this->getConfig()->get( 'InsertTemplateFavorites' );

		if ( empty( $sQuery ) && count( $aFavorites ) > 0 ) {
			$this->loadFavs( $aFavorites );
		} else {
			$this->loadAllTemplatesFromDB( $sQuery );
		}

		return $this->aData;
	}

	/**
	 * Returns the selected favorites.
	 * @param string[] $aFavs
	 */
	private function loadFavs( $aFavs ) {
		foreach ( $aFavs as $sTitle ) {
			$oTemplateTitle = Title::makeTitle( NS_TEMPLATE, $sTitle );
			$oTemplate = new stdClass();

			$oTemplate->id = $oTemplateTitle->getArticleID();
			$oTemplate->name = $oTemplateTitle->getText();
			$oTemplate->code = $this->parseTemplate( $oTemplateTitle );

			$this->aData[] = $oTemplate;
		}
	}

	/**
	 * Returns all templates bei $sQuery.
	 *
	 * @param string $sQuery The query string.
	 */
	private function loadAllTemplatesFromDB( $sQuery = '' ) {
		$res = $this->getDB()->select(
			[ 'p' => 'page' ],
			'*',
			[
				'page_namespace' => NS_TEMPLATE,
			]
		);

		foreach ( $res as $row ) {
			if ( stripos( $row->page_title, str_replace( ' ', '_', $sQuery ) ) !== false || $sQuery == '' ) {
				$oTemplateTitle = Title::makeTitle( NS_TEMPLATE, $row->page_title );

				$oTemplate = new stdClass();

				$oTemplate->id = $oTemplateTitle->getArticleID();
				$oTemplate->name = $oTemplateTitle->getText();
				$oTemplate->code = $this->parseTemplate( $oTemplateTitle );

				$this->aData[] = $oTemplate;
			}
		}
	}

	/**
	 * Returns the template code with variables.
	 *
	 * @param Title $oTemplateTitle
	 * @return string
	 */
	private function parseTemplate( $oTemplateTitle ) {
		$oWikiPage = WikiPage::factory( $oTemplateTitle );
		$oContent = $oWikiPage->getContent();

		$sWikiText = '{{' . $oTemplateTitle->getDBkey() . '}}';

		if ( $oContent instanceof WikitextContent ) {
			$sWikiText = $oContent->getText();

			$aMatches = [];
			if ( preg_match_all( '/\{\{\{(.*?)\}\}\}/', $sWikiText, $aMatches ) !== false ) {

				$sParameterList = '';
				$aNamedParameters = [];
				foreach ( $aMatches[1] as $sMatch ) {
					$aMatch = explode( '|', $sMatch );

					// do not list indexed parameters as named parameters, but
					// as empty places
					if ( is_numeric( $aMatch[0] ) ) {
						$sParameterList .= "|\n";
						continue;
					}
					// do not list same parameter twice
					if ( in_array( $aMatch[0], $aNamedParameters ) ) {
						continue;
					}

					$aNamedParameters[] = $aMatch[0];
					if ( count( $aMatch ) > 1 ) {
						$sParameterList .= '|' . $aMatch[ 0 ] . '=' . $aMatch[ 1 ] . "\n";
					} else {
						$sParameterList .= '|' . $sMatch . "=\n";
					}
				}
				$sWikiText = '{{' . $oTemplateTitle->getDBkey();
				if ( !empty( $sParameterList ) ) {
					$sWikiText .= "\n" . $sParameterList;
				}
				$sWikiText .= '}}';
			}
		}

		return $sWikiText;
	}
}
