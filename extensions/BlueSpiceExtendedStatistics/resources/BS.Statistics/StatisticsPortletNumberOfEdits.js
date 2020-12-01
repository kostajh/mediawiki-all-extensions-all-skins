/**
 * Statistics portlet number of edits
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

Ext.define( 'BS.Statistics.StatisticsPortletNumberOfEdits', {
	extend: 'BS.Statistics.StatisticsPortlet',
	diagram: 'BsDiagramNumberOfEdits',
	titleKey: 'bs-statistics-portlet-numberofedits'
} );
