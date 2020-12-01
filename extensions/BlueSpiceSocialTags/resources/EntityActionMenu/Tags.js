/*
* @author     Stefan Kühn
* @package    BluespiceSocial
* @subpackage BlueSpiceSocial
* @copyright  Copyright (C) 2020 Hallo Welt! GmbH, All rights reserved.
* @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
*/

bs.social = bs.social || {};
bs.social.EntityActionMenuTags = bs.social.EntityActionMenu || {};
bs.social.EntityActionMenuTags.Tags = function ( entityActionMenu, data ) {
	OO.EventEmitter.call( this );
	var me = this;
	me.data = data || {};
	me.entityActionMenu = entityActionMenu;
	var countTags = me.entityActionMenu.entity.data.get( 'tags', [] ).length;

	me.$element = null;
	me.$element = $( '<li><a class="dropdown-item bs-social-entity-action-tags">'
		+ '<span>' + mw.message( 'bs-socialtags-tagstext', countTags ).text() + '</span>'
		+ '</a></li>'
	);

	me.$element.on( 'click', function( e ) { me.click( e ); } );
	me.priority = 40;
};

OO.initClass( bs.social.EntityActionMenuTags.Tags );
OO.mixinClass( bs.social.EntityActionMenuTags.Tags, OO.EventEmitter );

bs.social.EntityActionMenuTags.Tags.prototype.click = function ( e ) {
	e.preventDefault();
	var me = this;
	var entity = me.entityActionMenu.entity;
	if( !entity.getConfig().IsTagable ) {
		return;
	}
	if( entity.hasParent() ) {
		return;
	}
	if( !entity.$tagEditor ) {
		entity.$tagEditor = $(
			'<div class="bs-social-entity-aftercontent-tageditor"></div>'
		).hide();
		entity.$tagEditor.insertAfter(
			entity.getEl().find('.bs-social-entity-aftercontent').first()
		);
		entity.$tagSelection = $(
			'<div><select style="width:100%"></select></div>'
		);
		entity.tagSubmitButton = new OO.ui.ButtonInputWidget( {
			label: mw.message( 'bs-social-editor-ok' ).plain(),
			flags: ['progressive', 'primary'],
			align: 'right'
		});
		entity.tagCancelButton = new OO.ui.ButtonInputWidget( {
			label: mw.message( 'bs-social-editor-cancel' ).plain(),
			align: 'right'
		});
		entity.tagSubmitButton.on( 'click', function() {
			var val = entity.$tagSelection.find( 'select' ).select2( "val" );
			if( !val ) {
				val = [];
			}
			entity.showLoadMask();
			bs.api.tasks.execSilent(
				'socialtags',
				'editTags',
				{ id: entity.id,
					type: entity.type,
					tags: val
				}
			).done( function( response ) {
				entity.replaceEL( response.payload.view );
			})
			.then(function(){
				entity.hideLoadMask();
			});
		});
		entity.tagCancelButton.on( 'click', function() {
			entity.$tagEditor.toggle();
			entity.$tagEditor.remove();
			entity.$tagEditor = null;
		});

		var items = [], tags = entity.data.get( 'tags' , []);
		for( var i = 0; i < tags.length; i++ ) {
			items.push( { id: tags[i], text: tags[i] });
		}

		entity.$tagEditor.append (
			entity.$tagSelection
		);
		entity.$tagEditor.append(
			entity.tagCancelButton.$element
		);
		entity.$tagEditor.append(
			entity.tagSubmitButton.$element
		);

		var $select2 = entity.$tagSelection.find( 'select' ).select2({
			multiple: true,
			allowClear: true,
			ajax: {
				url: mw.util.wikiScript( 'api' ),
				dataType: 'json',
				tape: 'POST',
				data: function (params) {
					return {
						action: 'bs-socialtitlequery-store',
						query: params.term
					};
				},
				processResults: function (data) {
					var results = [];
					$.each(data.results, function (index, result) {
						if( result.type && result.type === 'namespace' ) {
							return;
						}
						results.push({
							id: result.displayText,
							text: result.displayText
						});
					});
					return {
						results: results
					};
				}
			},
			minimumInputLength: 1
		});
		for( var i = 0; i < tags.length; i++ ) {
			$select2.append(
				new Option( tags[i], tags[i], true, true )
			);
		}
		$select2.trigger( 'change' );
	}

	entity.$tagEditor.toggle();
	return false;
};
