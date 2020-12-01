/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.Entity = function( $el, type, data ) {
	bs.social.El.call( this, $el );
	var me = this;
	me.savetask = 'save';
	me.loadtask = 'reload';
	me.taskApi = 'social';
	me.storeApi = 'social';
	me.editmode = false;
	me.ActionMenu = null;
	me.editor = null;
	me.wasSpawned = data.wasSpawned || false;
	me.WRAPPER_CONTAINER = 'bs-social-entity-wrapper';
	me.CONTENT_CONTAINER = 'bs-social-entity-content';
	me.TITLE_CONTAINER = 'bs-social-entity-right';
	me.ACTIONS_CONTAINER = 'bs-social-entity-actions';
	me.BEFORE_CONTENT_CONTAINER  = 'bs-social-entity-beforecontent';
	me.AFTER_CONTENT_CONTAINER  = 'bs-social-entity-aftercontent';
	me.CHILDREN_CONTAINER  = 'bs-social-entitylist-children';

	me.dirty = false;

	me.setData( data );
	me.init();
};
OO.initClass( bs.social.Entity );
OO.inheritClass( bs.social.Entity, bs.social.El );

bs.social.Entity.prototype.getContainer = function( name ) {
	return this.getEl().find( '.' + name ).first();
};

bs.social.Entity.prototype.init = function() {
	this.ActionMenu = this.makeActionMenu();
	if( !this.exists() ) {
		this.makeEditMode();
	}
	$(document).trigger('BSSocialEntityInit', [
		this,
		this.$el,
		this.type,
		this.data
	]);
};

bs.social.Entity.prototype.exists = function() {
	return this.id && this.id > 0;
};

bs.social.Entity.prototype.makeActionMenu = function() {
	if( this.actionMenu ) {
		return this.actionMenu;
	}
	this.actionMenu = new bs.social.EntityActionMenu( this.$el, this );
	return this.actionMenu;
};
bs.social.Entity.prototype.reset = function() {
	this.removeEditMode();
	var data = JSON.parse( this.getEl().attr('data-entity') );
	this.setData( data );
	this.init();
	return this;
};
bs.social.Entity.prototype.render = function( type ) {
	var type = type || 'Default';
	var output = this.getEntityOutput();
	var $EL = output.render( type );
	this.getEl().html($EL.html());
	this.init();
	return this;
};
bs.social.Entity.prototype.getEntityOutput = function() {
	return bs.social.OutputFactory.create(
		this.getConfig()['OutputClass'],
		JSON.parse( this.getEl().attr( 'data-entity' ) )
	);
};
bs.social.Entity.prototype.getConfig = function() {
	return bs.social.config[ this.getType() ];
};
bs.social.Entity.prototype.getType = function() {
	return this.type;
};
bs.social.Entity.prototype.getData = function() {
	var data = {
		id: this.data.get( 'id', 0 ),
		type: this.data.get( 'type', 'base' ),
		parentid: this.data.get( 'parentid', 0 ),
		ownerid: this.data.get( 'ownerid', 0 ),
		outputtype: this.getEl().attr( 'data-output' )
	};
	return data;
};
bs.social.Entity.prototype.setData = function( data ) {
	this.data = new mw.Map();
	this.data.set( data );
	this.id = this.data.get( 'id', 0 );
	this.type = this.data.get( 'type', 'base' );
	this.parentid = this.data.get( 'parentid', 0 );
	return this;
};

bs.social.Entity.prototype.getSaveTask = function() {
	return this.saveTask || 'save';
};
bs.social.Entity.prototype.getLoadTask = function() {
	return this.loadTask || 'load';
};
bs.social.Entity.prototype.getTaskApi = function() {
	return this.taskApi;
};
bs.social.Entity.prototype.hasParent = function() {
	return this.parentid > 0;
};

bs.social.Entity.prototype.save = function( newdata ) {
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
	.done( function(response) {
		//ignore errors for now
		//me.replaceEL( response.payload.view );
		if( !response.success ) {
			if( response.message && response.message !== '' ) {
				OO.ui.alert( response.message );
			}
			dfd.resolve( me, response );
			return;
		}
		if( me.exists() ) {
			me.replaceEL( response.payload.view );
		} else {
			me.insertAfterEL( response.payload.view );
			me.reset();
		};

		dfd.resolve( me, response );
	})
	.then(function(){
		bs.social.init();
		$( ".bs-social-entityspawner-new" ).removeClass( "bs-social-entityspawner-new" );
		me.hideLoadMask();
	});

	return dfd;
};
bs.social.Entity.prototype.delete = function() {
	var me = this;
	me.showLoadMask();
	return bs.api.tasks.execSilent(
		'social',
		'deleteEntity',
		{ id: me.id, type: me.type, undelete: me.data.get( 'archived' ) ? true : false }
	).done( function( response ) {
		me.replaceEL( response.payload.view );
	})
	.then( function(){
		me.hideLoadMask();
	});
};
bs.social.Entity.prototype.reload = function() {
	var data = this.getData();
	var me = this;
	var dfd = $.Deferred();
	me.showLoadMask();
	return bs.api.tasks.execSilent(
		me.getTaskApi(),
		'getEntity',
		data
	).done( function(response) {
		//ignore errors for now
		//me.replaceEL( response.payload.view );
		if( !response.success ) {
			if( response.message && response.message !== '' ) {
				OO.ui.alert( response.message );
			}
			dfd.resolve( me );
			return;
		}
		me.replaceEL( response.payload.view );
		dfd.resolve( me );
	})
	.then(function(){
		bs.social.init();
		me.hideLoadMask();
	});
	return dfd;
};

bs.social.Entity.prototype.getEditorConfig = function() {
	return {
		shortMode: !this.exists() && !this.wasSpawned
	};
};

bs.social.Entity.prototype.makeEditMode = function() {
	var $wrapper = this.getContainer( this.WRAPPER_CONTAINER );
	var me = this;
	me.editmode = true;
	me.showLoadMask();
	mw.loader.using( this.getConfig().ModuleEditScripts ).done( function() {
		if( !me.editor ) {
			me.editor = me.makeEditor();
			me.editor.on( 'submit', function( editor, data ) {
				me.save( data ).done( function( entity, response ) {
					if( !entity.editmode || ( response && response.success ) ) {
						me.setDirty( false );
					}
				});
				return false;
			});
			me.editor.on( 'change', function( editor, field ) {
				me.setDirty( true );
			} );
			me.editor.on( 'cancel', function( editor, data ) {
				me.setDirty( false );
				if( !me.exists() ) {
					me.reset();
					return true;
				}
				me.removeEditMode( data );
				return true;
			});
			me.getEl().addClass( 'bs-social-entity-edit-mode' );
			me.editor.appendTo( $wrapper );
			me.hideLoadMask();
		} else {
			me.getEl().addClass( 'bs-social-entity-edit-mode' );
			me.editor.appendTo( $wrapper );
			me.hideLoadMask();
		}
	} );
};

bs.social.Entity.prototype.removeEditMode = function() {
	this.editmode = false;

		if( this.wasSpawned ) {
			this.removeEL();
			return;
		}

		this.getEl().removeClass( 'bs-social-entity-edit-mode' );

		if( this.editor ) {
			this.editor.$element.remove();
			delete this.editor;
		}

};

bs.social.Entity.prototype.makeEditor = function() {
	if( this.editor ) {
		return this.editor;
	}
	this.editor = new bs.social.EntityEditor( this.getEditorConfig(), this );
	return this.editor;
};

bs.social.Entity.prototype.update = function() {
	if( mw.user.options.get( 'bs-social-datedisplaymode' ) === 'age' ) {
		var $ts = this.getContainer( this.TITLE_CONTAINER ).find(
			'.timestampcreated'
		).first();
		if( $ts.length > 0 ) {
			this.updateTimestampCreated( $ts );
		}

		var $ts = this.getContainer( this.TITLE_CONTAINER ).find(
			'.timestamptouched'
		).first();
		if( $ts.length > 0 ) {
			this.updateTimestampTouched( $ts );
		}
	}
};

bs.social.Entity.prototype.updateTimestampCreated = function( $ts ) {
	var date = bs.util.convertMWTimestampToDate(
		this.data.get( 'timestampcreated' )
	);
	if( !date ) {
		return;
	}
	if( ( new Date() - date ) > 1000 * 60 * 60 * 24  ) {
		//older that 24 hours... so we do not need to update the ts, i guess
		return;
	}

	//timestampToAgeString requires some weird time format as a param o.0
	$ts.html( bs.util.timestampToAgeString(
		Math.round( ( date.getTime() - ( date.getTimezoneOffset() * 60000 ) ) / 1000 )
	));
};

bs.social.Entity.prototype.setDirty = function( dirty ) {
	this.dirty = dirty;
	if( this.dirty ) {
		this.getEl().addClass( 'dirty' );
	} else {
		this.getEl().removeClass( 'dirty' );
	}
	this.emit( 'dirty', this, this.dirty );
};
