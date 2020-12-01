/**
 * BlueSpiceGroupManager extension
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    BlueSpiceGroupManager
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
Ext.Loader.setPath(
	'BS.GroupManager',
	bs.em.paths.get( 'BlueSpiceGroupManager' ) + '/resources/BS.GroupManager'
);
 
Ext.onReady( function(){
	Ext.create( 'BS.GroupManager.Panel', {
		id: 'bs-groupmanager-grid-panel',
		renderTo: 'bs-groupmanager-grid'
	} );
} );