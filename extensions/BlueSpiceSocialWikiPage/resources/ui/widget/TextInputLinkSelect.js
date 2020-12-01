bs = bs || {};
bs.ui = bs.ui || {};
bs.ui.widget = bs.ui.widget || {};

/**
 * CURRENTLY BROKEN!!!
 * @param {Object} config
 * @returns {bs.ui.widget.TextInputLinkSelect}
 */
bs.ui.widget.TextInputLinkSelect = function ( config ) {
	OO.ui.MultilineTextInputWidget.call( this, config );
	var me = this;
	me.field = config.field || false;
};
OO.initClass( bs.ui.widget.TextInputLinkSelect );
OO.inheritClass( bs.ui.widget.TextInputLinkSelect, OO.ui.MultilineTextInputWidget );

bs.ui.widget.TextInputLinkSelect.prototype.init = function() {
	var me = this;
	mw.loader.using( [ 'ext.bluespice.insertlink' ] ).done( function () {
		var id = me.field.$element.attr( 'id' ) || 'bs-textinput-insertlink';
		id += '-' + Ext.id();

		me.field.$element.append(
			'<div id="' + id + '" style = "min-height: 40px; text-align: center; cursor: pointer; margin: 5px 0px; border: 3px dashed; padding: 2% 5px; overflow: hidden; white-space: normal;">'
			+ '</div>'
		);
		$(document).on( 'click', '#' + id, function() {
			mw.loader.using( 'ext.bluespice.extjs' ).done( function(){
				Ext.require('BS.InsertLink.Window', function() {
					BS.InsertLink.Window.resetData();
					BS.InsertLink.Window.clearListeners();
					BS.InsertLink.Window.on( 'ok', function( window, data ) {
						if( data === null ) {
							return;
						}
						me.field.setValue(
							me.field.getValue() + "\n" + data.code
						);
					});
					BS.InsertLink.Window.show( me );
				});
			});
		});

		$( '#' + id ).html(
			'link'
		);
	} );
};
