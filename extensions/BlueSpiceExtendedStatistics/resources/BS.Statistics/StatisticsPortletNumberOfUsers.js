/**
 * Statistics portlet number of users
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
Ext.define( 'BS.Statistics.StatisticsPortletNumberOfUsers', {
	extend: 'BS.Statistics.StatisticsPortlet',
	diagram: 'BsDiagramNumberOfUsers',
	titleKey: 'bs-statistics-portlet-numberofusers',
	filters: ['UserFilter']
} );
