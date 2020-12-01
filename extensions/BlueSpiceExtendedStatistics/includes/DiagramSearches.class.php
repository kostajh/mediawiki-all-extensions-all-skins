<?php
/**
 * Describes search query diagram for Statistics for BlueSpice.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 *
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

/**
 * Describes search query diagram for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 */
class BsDiagramSearches extends BsDiagram {

	/**
	 * Constructor of BsDiagramSearches class
	 */
	public function __construct() {
		parent::__construct();

		$this->sTitle = wfMessage( 'bs-statistics-diag-search-queries' )->text();
		$this->sDescription = wfMessage( 'bs-statistics-diag-search-queries-desc' )->text();
		$this->sTitlex = wfMessage( 'bs-statistics-label-time' )->text();
		$this->sTitley = wfMessage( 'bs-statistics-label-count' )->text();
		$this->sActualGrain = "m";
		$this->sModLabel = "M y";
		$this->iDataSource = BsDiagram::DATASOURCE_DATABASE;
		$this->bListable = true;
		$this->sSqlWhatForDiagram = "count(esh_term)";
		$this->sSqlWhatForList = "esh_term, count(esh_term) as x, max(esh_hits)";
		$this->sSqlFromWhere = "FROM #__bs_extendedsearch_history WHERE esh_timestamp @period";
		$this->sSqlOptionsForList = "GROUP BY esh_term";
		$this->sListLabel = [
			wfMessage( 'bs-statistics-label-searchterm' )->text(),
			wfMessage( 'bs-statistics-label-count' )->text(),
			wfMessage( 'bs-statistics-label-maxhits' )->text()
		];
		$this->sMode = BsDiagram::MODE_AGGREGATED;
	}
}
