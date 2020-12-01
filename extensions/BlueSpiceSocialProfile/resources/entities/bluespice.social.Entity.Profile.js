/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BSSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityProfile = function( $el, type, data ) {
	var me = this;
	me.PROFILE_FIELDS_CONTAINER = 'bs-social-profile-fields';
	bs.social.Entity.call( this, $el, type, data );
};
OO.initClass( bs.social.EntityProfile );
OO.inheritClass( bs.social.EntityProfile, bs.social.Entity );

bs.social.EntityProfile.prototype.init = function() {
	this.ActionMenu = this.makeActionMenu();
	$(document).trigger('BSSocialEntityInit', [
		this,
		this.$el,
		this.type,
		this.data
	]);
};

bs.social.EntityProfile.prototype.makeEditor = function() {
	return new bs.social.EntityEditorProfile( this.getEditorConfig(), this );
};

bs.social.EntityProfile.prototype.getEditorConfig = function() {
	return {};
};

bs.social.EntityProfile.prototype.save = function( newdata ) {
	var me = this;
	var dfd = $.Deferred();
	if( !me.editmode ) {
		dfd.reject( me );
		return dfd;
	}

	var taskData = me.getData();
	for( var i in newdata ) {
		taskData[i] = newdata[i];
	};

	me.showLoadMask();
	bs.api.tasks.execSilent( 'social', 'editEntity', taskData )
	.done( function( response ) {
		if( !response.success ) {
			if( response.message && response.message !== '' ) {
				OO.ui.alert( response.message );
			}
			dfd.resolve( me );
			me.hideLoadMask();
			return;
		}
		if( me.getData().outputtype !== 'Page' ) {
			me.replaceEL( response.payload.view );
		}
		dfd.resolve( me, response );
	})
	.then( function() {
		bs.social.init();
		$( ".bs-social-entityspawner-new" ).removeClass( "bs-social-entityspawner-new" );
		if( me.getData().outputtype !== 'Page' ) {
			me.hideLoadMask();
		} else {
			window.location = mw.util.getUrl(
				mw.config.get( 'wgPageName' )
			);
		}
	});

	return dfd;
};

bs.social.EntityProfile.static.name = "\\BlueSpice\\Social\\Profile\\Entity\\Profile";
bs.social.factory.register( bs.social.EntityProfile );