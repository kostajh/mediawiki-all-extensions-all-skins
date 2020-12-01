/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityOutput = function( rawEntity ) {
	OO.EventEmitter.call( this );
	var me = this;
	me.args = {};
	me.entity = rawEntity;
	me.args['type'] = rawEntity['type'] || '';
	me.args['id'] = rawEntity['id'] || 0;
	me.args['text'] = rawEntity['text'] || '';
	me.args['entity'] = JSON.stringify( rawEntity );
	me.args['userimage'] = rawEntity.ownerid || mw.config.get( 'wgUserId', 0 );
	me.args['timestampcreated'] = rawEntity.timestampcreated || bs.util.timestampToAgeString(
		Math.round( ( new Date() ).getTime() / 1000 )
	);
	me.args['timestamptouched'] = rawEntity.timestamptouched || bs.util.timestampToAgeString(
		Math.round( ( new Date() ).getTime() / 1000 )
	);
	me.args['title'] = rawEntity.header || mw.message(
		bs.social.config[me.args['type']].HeaderMessageKeyCreateNew
	).plain();
	me.args['preload'] = rawEntity.preload || '';
	me.args['beforecontent'] = '';
	me.args['aftercontent'] = '';
	me.args['content'] = '';
	me.args['beforechildren'] = '';
	me.args['children'] = '';
	me.args['afterchildren'] = '';
};

OO.initClass( bs.social.EntityOutput );
OO.mixinClass( bs.social.EntityOutput, OO.EventEmitter );

bs.social.EntityOutput.prototype.render = function( type ) {
	var tpl = mw.template.get(
		'ext.bluespice.social.entity.templates',
		this.getTemplateName( type )
	);
	var out = '';
	var $EL = tpl.render( this.getArgs( type ) );
	$EL.each( function( i, e ) {
		if( $(e) && $(e).prop( 'outerHTML' ) ) {
			out += $(e).prop( 'outerHTML' );
		}
	});

	return $(
		this.getEntityOpenTag( type )
		+ out
		+ this.getEntityCloseTag( type )
	);
};

bs.social.EntityOutput.prototype.getEntityOpenTag = function( type ) {
	var classes = [
		'bs-social-entity',
		'bs-social-entity-' + this.args['type'],
		'bs-social-entity-output-' + type
	];
	return '<div '
		+ 'class = "' + classes.join(' ') + '" '
		+ "data-entity = '" + this.args['entity'] + "' "
		+ 'data-type = "' + this.args['type'] + '" >';
};

bs.social.EntityOutput.prototype.getEntityCloseTag = function( type ) {
	return '</div>';
};

bs.social.EntityOutput.prototype.getTemplateName = function( type ) {
	return 'BlueSpiceSocial.Entity.' + type + '.mustache';
};

bs.social.EntityOutput.prototype.getArgs = function( type ) {
	this.args['output'] = type;
	for( var i in this.args ) {
		var renderfunction = "render_"+i;
		if( typeof this["render_"+i] === "function" ) {
			this.args[i] = this[renderfunction]( this.args[i], type );
		}
	}
	return this.args;
};

bs.social.EntityOutput.prototype.render_userimage = function( val, type ) {
	//not cool!
	var img = mw.config.get('wgScriptPath')
		+ '/dynamic_file.php'
		+ '?module=userprofileimage'
		+ '&username=' + mw.config.get( 'wgUserName', '' )
	;
	var html = '<img src="' + img + '" width="40px" height="40px" />';
	return html;
};

bs.social.EntityOutput.prototype.render_beforecontent = function( val, type ) {
	var html = '';
	$(document).trigger('BSSocialEntityOutputBeforeContent', [
		this,
		html,
		val,
		type
	]);
	return html;
};
bs.social.EntityOutput.prototype.render_aftercontent = function( val, type ) {
	var html = '';
	$(document).trigger('BSSocialEntityOutputAfterContent', [
		this,
		html,
		val,
		type
	]);
	return html;
};

bs.social.EntityOutput.static.name = "\\BlueSpice\\Social\\EntityOutput";
bs.social.OutputFactory.register( bs.social.EntityOutput );