( function( mw, $, d, bs ){
	OO = OO || window.OO;
	bs.social = bs.social || {};
	bs.social.factory = new OO.Factory();
	bs.social.OutputFactory = new OO.Factory();
	bs.social.entityStore = {};
	bs.social.config = {};
	bs.social.uuid = 0;
	bs.social.mmvInitialized = false;
	bs.social.warnOnLeave = null;

	bs.social.generateUniqueId = function() {
		return "bss-ui-" + ( ++bs.social.uuid );
	};
	bs.social.getUiID = function( $el ) {
		if( !$el ) {
			return false;
		}
		if( $el.attr('id') && $el.attr('id').length > 0 ) {
			return $el.attr('id');
		}
		return false;
	};
	bs.social.getFromStore = function( uiID ) {
		if( !uiID || !bs.social.entityStore[uiID] ) {
			return null;
		}
		return bs.social.entityStore[uiID];
	};

	bs.social.newFromEl = function( $el ) {
		if( !$el || $el.length < 1 ) {
			return null;
		}
		var uiID = bs.social.getUiID( $el );
		var item = bs.social.getFromStore( uiID );
		if( item ) {
			return item;
		}
		return bs.social.createFromEl( $el );
	};
	bs.social.createFromEl = function( $el ) {
		if( !$el || $el.length < 1 ) {
			return null;
		}
		var type = $el.attr( 'data-type' );
		if( !type || type === '' || typeof type === 'undefined' ) {
			type = 'entity';
		}
		try{
			if( typeof bs.social.config[type] === "undefined" ) {
				throw "Unregistered type: " + type;
			}
		} catch(e) {
			console.log(e);
			return null;
		}
		var data = {};
		try{
			data = JSON.parse( $el.attr('data-entity') );
		} catch(e) {
			console.log(e);
		}

		mw.loader.using( bs.social.config[type].ModuleScripts ).done( function() {
			var entity = bs.social.factory.create(
				bs.social.config[type].EntityClass,
				$el,
				type,
				data
			);

			bs.social.entityStore[entity.makeUiID()] = entity;
		} );
	};
	bs.social.init = function(){
		bs.social.config = mw.config.get( 'bsgSocialEntityConfigs', {} );
		var els = [], modules = [];
		$( ".bs-social-entity" ).each( function() {
			if( bs.social.getUiID( $(this) ) ) {
				//already exists
				return null;
			}
			// collect all elements first and initialize them later else this may
			// would lead in some browsers to a short stop of jquery 
			els.push( $(this) );
		} );
		for ( var i = 0; els.length > i; i++ ) {
			var type = els[i].attr( 'data-type' );
			if ( !type ) {
				continue;
			}
			if ( !bs.social.config[type] || !bs.social.config[type].ModuleScripts ) {
				continue;
			}
			for ( var y = 0; bs.social.config[type].ModuleScripts.length > y; y++ ) {
				modules.push( bs.social.config[type].ModuleScripts[y] );
			}
		}

		modules.push( "user.options" );
		modules.push( 'ext.bluespice.social.messages' );
		for ( var i = 0; i < mw.config.get( 'bsgSocialLegacyModules', [] ).length; i++ ) {
			modules.push( mw.config.get( 'bsgSocialLegacyModules' )[i] );
		}
		// pre-collect all required modules of already displayed entities to
		// reduce requests
		mw.loader.using( modules ).done( function() {
			for ( var i = 0; els.length > i; i++ ) {
				bs.social.createFromEl( els[i] );
			}

			$( d ).trigger( 'BSSocialInit', [
				bs.social
			] );

			if ( mw.user.options.get( 'bs-social-warnonleave', false ) === true ) {
				this.warnOnLeave = mw.confirmCloseWindow( {
					test: function () {
						// We use .textSelection, because editors might not have updated the form yet.
						return $.find( '.bs-social-entity.dirty' ).length > 0;
					},

					message: mw.msg( 'bs-social-editwarnonleave-confirmtext' )
				} );
			}

			if ( mw && mw.mmv && mw.mmv.bootstrap && !this.mmvInitialized ) {
				var mmv = mw.mmv.bootstrap;
				$( 'div:not(.mw-body-content) .bs-social-entity-attachment-image img' ).each( function() {
					mmv.processThumb( this );
				} );
				return;
			}
		} );
	};
	bs.social.updater = function(){
		$.each( bs.social.entityStore, function( k, entity ){
			if( !$( "#" + k ).get(0) ) {
				//remove old entities
				delete bs.social.entityStore[k];
				return;
			}
			entity.update();
		} );
		//every 10 seconds. may add setting
		setTimeout( bs.social.updater, 10000 );
	};
	/**
	 * Unfortunately we can not load the configs as RL module, because `load.php` has no user
	 * session, which is required by the `BlueSpice\Social\ResourceCollector`.
	 */
	bs.api.tasks.execSilent( 'social', 'getConfigs' ).done( function( response ) {
		mw.config.set( 'bsgSocialEntityConfigs', response.payload );
		bs.social.init();
		bs.social.updater();
	} );

})( mediaWiki, jQuery, document, blueSpice );