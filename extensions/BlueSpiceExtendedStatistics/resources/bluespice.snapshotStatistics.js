/**
 * Statistics extension
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Patric Wirth
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
Ext.onReady( function() {
	Ext.Loader.setPath(
		'BS.ExtendedStatistics',
		bs.em.paths.get('BlueSpiceExtendedStatistics') + '/resources/BS.ExtendedStatistics'
	);
	Ext.create( 'BS.ExtendedStatistics.panel.Main', {
		renderTo: 'bs-extendedstatistics-special-snapshotstatistics'
	} );
} );

