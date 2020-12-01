/**
 * Statistics portlet number of pages
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
Ext.define( 'BS.Statistics.StatisticsPortletNumberOfPages', {
	extend: 'BS.Statistics.StatisticsPortlet',
	diagram: 'BsDiagramNumberOfPages',
	titleKey: 'bs-statistics-portlet-numberofpages'
} );
