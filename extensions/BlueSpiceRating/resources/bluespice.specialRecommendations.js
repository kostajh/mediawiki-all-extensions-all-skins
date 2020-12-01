/**
 * Js for Rating special page
 *
 * @author     Patric Wirth
 * @package    Bluespice_Extensions
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
( function( mw, $, bs ) {
	Ext.onReady( function() {
		Ext.Loader.setPath(
			'BS.Rating',
			bs.em.paths.get( 'BlueSpiceRating' ) + '/resources/BS.Rating'
		);
		Ext.require( 'BS.Rating.articlelike.grid.Panel', function(){
			new BS.Rating.articlelike.grid.Panel( {
				renderTo: 'bs-ratingarticlelike-grid'
			});
		});
	});
})( mediaWiki, jQuery, blueSpice );