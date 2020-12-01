/**
 * InterWikiManager extension
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage InterWikiLinks
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

 /* Changelog
  * v0.1
  * - initial commit
  */
Ext.Loader.setPath(
	'BS.InterWikiLinks',
	bs.em.paths.get( 'BlueSpiceInterWikiLinks' ) + '/resources/BS.InterWikiLinks'
);

Ext.onReady( function(){
	var taskPermissions = mw.config.get( 'bsTaskAPIPermissions' );
	var operationPermissions = {
        "create": true, //should be connected to mw.config.get('bsTaskAPIPermissions').extension_xyz.task1 = boolean in derived class
        "update": true, //...
        "delete": true  //...
    };
	if ( taskPermissions !== null ) {
		if ( typeof taskPermissions.interwikilinks.editInterWikiLink === 'boolean' ) {
			operationPermissions.create = taskPermissions.interwikilinks.editInterWikiLink;
			operationPermissions.update = taskPermissions.interwikilinks.editInterWikiLink;
		}
		if ( typeof taskPermissions.interwikilinks.removeInterWikiLink === 'boolean' ) {
			operationPermissions.delete = taskPermissions.interwikilinks.removeInterWikiLink;
		}
	}
	Ext.create( 'BS.InterWikiLinks.Panel', {
		operationPermissions: operationPermissions,
		renderTo: 'InterWikiLinksGrid'
	} );
} );