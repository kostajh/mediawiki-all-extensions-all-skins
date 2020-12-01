/**
 *
 * @author     Patric Wirth
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityListMenu = function( $el, entityList ) {
	bs.social.El.call( this, $el );
	var me = this;
	me.BUTTON_SECTION = 'bs-social-entitylist-menu-section-button';
	me.MENU_SECTION = 'bs-social-entitylist-menu-section-menu';
	me.entityList = entityList;
	me.data = {};
	me.filters = {};
	me.options = {};
	me.makeUiID();
	me.buttons = [];

	me.initOptions();
	me.initFilters();

	$(document).trigger( 'BSSocialEntityListMenuInit', [ me, $el ] );

	me.makeButtons();

	me.$optionContent = me.makeOptionContent();
	me.$optionButton.on( 'click', function() {
		me.$optionContent.toggle();
		if( me.$optionContent.is( ":visible" ) ) {
			me.$optionButton.addClass( 'active' );
		} else {
			me.$optionButton.removeClass( 'active' );
		}
	});

	me.getEl().find( '.' + this.MENU_SECTION ).append( me.$optionContent );

	me.$filterContent = me.makeFilterContent();
	me.$filterButton.on( 'click', function() {
		me.$filterContent.toggle();
		if( me.$filterContent.is( ":visible" ) ) {
			me.$filterButton.addClass( 'active' );
		} else {
			me.$filterButton.removeClass( 'active' );
		}
	});
	me.getEl().find( '.' + this.MENU_SECTION ).append( me.$filterContent );

	me.entityList.on( 'load', function() {
		me.setLoading();
	});

	me.entityList.on( 'loadcomplete', function() {
		me.removeLoading();
	});
};
OO.initClass( bs.social.EntityListMenu );
OO.inheritClass( bs.social.EntityListMenu, bs.social.El );

bs.social.EntityListMenu.prototype.initOptions = function() {
	if( this.entityList.getData().limit && this.entityList.getData().useendlessscroll ) {
		this.initOption( 'limit', this.entityList.getData().limit );
	}
	if( this.entityList.getData().sort ) {
		this.initOption( 'sort', this.entityList.getData().sort );
		this.initOption( 'direction', this.entityList.getData().sort );
		this.entityList.persistSetting( 'sort' );
	}
};

bs.social.EntityListMenu.prototype.initFilters = function() {
	var filters = this.entityList.getData().availablefilterfields || [];
	if( filters.length < 1 ) {
		return;
	}
	for( var i = 0; i < filters.length; i++ ) {
		var appliedFilter = false;
		for( var y = 0; y < this.entityList.getData().filter.length; y ++ ) {
			if( this.entityList.getData().filter[y].property !== filters[i] ) {
				continue;
			}
			appliedFilter = this.entityList.getData().filter[y];
			break;
		}

		this.initFilter( filters[i], appliedFilter );
	}

	this.entityList.persistSetting( 'filter' );
};

bs.social.EntityListMenu.prototype.getData = function() {
	var data = {};
	for( var i in this.filters ) {
		data = this.filters[i].getData( data );
	}
	for( var i in this.options ) {
		data = this.options[i].getData( data );
	}

	var filter = this.entityList.getData().filter;
	//add all the filters, that currently have to client side implementation
	for( var i = 0; i < filter.length; i++ ) {
		var add = true;
		for( var y  = 0; y < data.filter.length; y++ ) {
			if( filter[i].property === data.filter[y].property ) {
				add = false;
				break;
			}
		}
		if( !add ) {
			continue;
		}

		data.filter.push( filter[i] );
	}

	return data;
};

bs.social.EntityListMenu.prototype.initOption = function( key, mVal ) {
	if( !bs.social.EntityListMenuOptions[key] ) {
		return;
	}
	try {
		this.options[key] = new bs.social.EntityListMenuOptions[key](
			key,
			mVal,
			this
		);
		this.options[key].on( 'change', this.onOptionChange, [], this );
		this.options[key].init( mVal );
		var locked = this.entityList.getData().lockedoptionnames || [];
		for( var i = 0; i < locked.length; i++ ) {
			if( locked[i] !== key ) {
				continue;
			}
			this.filters[key].deactivate();
		}
	} catch( e ) {
		console.log( e );
		return;
	}
};

bs.social.EntityListMenu.prototype.initFilter = function( key, mVal ) {
	if( !bs.social.EntityListMenuFilters[key] ) {
		return;
	}
	try {
		this.filters[key] = new bs.social.EntityListMenuFilters[key](
			key,
			mVal,
			this
		);
		this.filters[key].on( 'change', this.onFilterChange, [], this );
		this.filters[key].init( mVal );
		var locked = this.entityList.getData().lockedfilternames || [];
		for( var i = 0; i < locked.length; i++ ) {
			if( locked[i] !== key ) {
				continue;
			}
			this.filters[key].deactivate();
		}
	} catch( e ) {
		console.log( e );
		return;
	}
};

bs.social.EntityListMenu.prototype.makeOptionButton = function() {
	var tpl = mw.template.get(
		'ext.bluespice.social.timeline.templates',
		'BlueSpiceSocial.EntityListMenuButton.mustache'
	);
	var $button = $(tpl.render( {
		classes: 'bs-entitylist-menu-item-options',
		tooltip: mw.message( 'bs-social-sort-button-tooltip' ).plain()
	}));
	return $button;
};

bs.social.EntityListMenu.prototype.makeFilterButton = function() {
	var tpl = mw.template.get(
		'ext.bluespice.social.timeline.templates',
		'BlueSpiceSocial.EntityListMenuButton.mustache'
	);
	var $button = $(tpl.render( {
		classes: 'bs-entitylist-menu-item-filters',
		tooltip: mw.message( 'bs-social-filter-button-tooltip' ).plain()
	}));
	return $button;
};

bs.social.EntityListMenu.prototype.makeOptionContent = function() {
	var tpl = mw.template.get(
		'ext.bluespice.social.timeline.templates',
		'BlueSpiceSocial.EntityListMenuContent.mustache'
	);

	var $content = $(tpl.render( {
		classes: 'bs-social-entitylist-menu-content-options'
	}));
	for( var i in this.options ) {
		if( this.options[i].$element ) {
			$content.append( this.options[i].$element );
		}
	}
	return $content.hide();
};

bs.social.EntityListMenu.prototype.makeFilterContent = function() {
	var tpl = mw.template.get(
		'ext.bluespice.social.timeline.templates',
		'BlueSpiceSocial.EntityListMenuContent.mustache'
	);

	var $content = $(tpl.render( {
		classes: 'bs-social-entitylist-menu-content-filters'
	}));
	for( var i in this.filters ) {
		if( this.filters[i].$element ) {
			$content.append( this.filters[i].$element );
		}
	}
	return $content.hide();
};

bs.social.EntityListMenu.prototype.onOptionChange = function( option, mVal, oldValue, res ) {
	var me = this;
	var execute = function() {
		me.emit( 'optionchange', me, option, mVal );
		me.setLoading();
		var action = 'replace';
		if( option.key === 'limit' ) {
			action = 'add';
		}
		me.entityList.getEntities( action, me.getData() );
	};
	if ( option.key !== 'limit' && this.entityList.getDirtyEntities().length > 0
		&& mw.user.options.get( 'bs-social-warnonleave', false ) === true ) {
		var msg = 'bs-social-entitylistmenu-editwarnonchange-confirmtext';
		me.$optionContent.hide();
		me.$filterContent.hide();
		OO.ui.confirm( mw.message( msg ).plain() ).done( function ( confirmed ) {
			if ( confirmed ) {
				execute();
			}
		});
		res.result = false;
		return false;
	}
	execute();
	return true;
};

bs.social.EntityListMenu.prototype.onFilterChange = function( filter, mVal, oldValue, res ) {
	var me = this;
	var execute = function() {
		me.emit( 'filterchange', me, filter, mVal );
		me.setLoading();
		me.entityList.getEntities( 'replace', me.getData() );
	};
	if ( this.entityList.getDirtyEntities().length > 0
		&& mw.user.options.get( 'bs-social-warnonleave', false ) === true ) {
		var msg = 'bs-social-entitylistmenu-editwarnonchange-confirmtext';
		me.$optionContent.hide();
		me.$filterContent.hide();
		OO.ui.confirm( mw.message( msg ).plain() ).done( function ( confirmed ) {
			if ( confirmed ) {
				execute();
			}
		});
		res.result = false;
		return false;
	}
	execute();
	return true;
};

bs.social.EntityListMenu.prototype.makeButtons = function() {
	this.$optionButton = this.makeOptionButton();
	this.getEl().find( '.bs-social-entitylist-menu-section-button' ).append(
		this.$optionButton
	);
	this.buttons.push( this.$optionButton );

	this.$filterButton = this.makeFilterButton();
	this.getEl().find( '.bs-social-entitylist-menu-section-button' ).append(
		this.$filterButton
	);
	this.buttons.push( this.$filterButton );

	this.emit( 'registerbutton', this, this.buttons );

	for( var i = 0; i < this.buttons.length; i++) {
		this.getEl().find( '.bs-social-entitylist-menu-section-button' ).append(
			this.buttons[i]
		);
	}
};

bs.social.EntityListMenu.prototype.setLoading = function() {
	for( var i in this.filters ) {
		this.filters[i].deactivate();
	}
	for( var i in this.options ) {
		this.options[i].deactivate();
	}
};

bs.social.EntityListMenu.prototype.removeLoading = function() {
	var locked = this.entityList.getData().lockedfilternames || [];
	for( var i in this.filters ) {
		var activate = true;
		for( var y = 0; y < locked.length; y++ ) {
			if( locked[y] === i ) {
				activate = false;
				break;
			}
		}
		if( !activate ) {
			continue;
		}
		this.filters[i].activate();
	}
	var locked = this.entityList.getData().lockedoptionnames || [];
	for( var i in this.options ) {
		var activate = true;
		for( var y = 0; y < locked.length; y++ ) {
			if( locked[y] === i ) {
				activate = false;
				break;
			}
		}
		if( !activate ) {
			continue;
		}
		this.options[i].activate();
	}
};
