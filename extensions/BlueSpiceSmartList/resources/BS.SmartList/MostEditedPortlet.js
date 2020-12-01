/**
 * SmartList extension
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage SmartList
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

Ext.define( 'BS.SmartList.MostEditedPortlet', {
	extend: 'BS.portal.APIPortlet',
	portletConfigClass: 'BS.SmartList.MostEditedPortletConfig',
	module: 'smartlist',
	task: 'getMostEditedPages',
	makeData: function() {
		return {
			count: this.portletItemCount,
			period: this.portletTimeSpan
		};
	}
} );