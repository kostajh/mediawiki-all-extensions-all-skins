/**
 * Expiry extension
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage Reminder
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

Ext.onReady( function(){
	var taskPermissions = mw.config.get( 'bsTaskAPIPermissions' );
	var operationPermissions = {
		"create": true, //should be connected to mw.config.get('bsTaskAPIPermissions').extension_xyz.task1 = boolean in derived class
		"update": true, //...
		"delete": true  //...
	};
	if ( taskPermissions !== null ) {
		if ( typeof taskPermissions.expiry.saveExpiry === 'boolean' ) {
			operationPermissions.create = taskPermissions.expiry.saveExpiry;
			operationPermissions.update = taskPermissions.expiry.saveReminder;
		}
		if ( typeof taskPermissions.expiry.deleteExpiry === 'boolean' ) {
			operationPermissions.delete = taskPermissions.expiry.deleteExpiry;
		}
	}

	Ext.create( 'BS.Expiry.Panel', {
		renderTo: 'bs-expiry-overview-grid'
	} );
} );