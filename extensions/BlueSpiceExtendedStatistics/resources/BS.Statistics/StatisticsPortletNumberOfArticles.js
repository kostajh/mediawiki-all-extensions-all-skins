/**
 * Statistics portlet number of articles
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Patric Wirth
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

Ext.define( 'BS.Statistics.StatisticsPortletNumberOfArticles', {
	extend: 'BS.Statistics.StatisticsPortlet',
	diagram: 'BsDiagramNumberOfArticles',
	titleKey: 'bs-statistics-portlet-numberofarticles'
} );
