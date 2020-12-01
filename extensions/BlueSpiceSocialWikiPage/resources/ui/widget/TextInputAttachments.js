bs = bs || {};
bs.ui = bs.ui || {};
bs.ui.widget = bs.ui.widget || {};

bs.ui.widget.TextInputAttachments = function ( config ) {
	OO.ui.InputWidget.call( this, config );
	var me = this;
	me.attachments = config.attachments || false;
	me.api = new mw.Api();
	me.$element.find( 'input' ).remove();
	me.$element.addClass( 'bs-social-entity-content-attachments' );
};
OO.initClass( bs.ui.widget.TextInputAttachments );
OO.inheritClass( bs.ui.widget.TextInputAttachments, OO.ui.InputWidget );

bs.ui.widget.TextInputAttachments.prototype.init = function() {
	this.render();
};

bs.ui.widget.TextInputAttachments.prototype.makeAttachmentEditor = function( attachment, id ) {
	var delMsg = mw.message(
		'bs-socialwikipage-stash-editor-attachedfile-delete'
	);
	var $attachment = $(
		'<div class="breakheight">'
			+ '<span id="' + id + '" class="bs-social-entity-attachment-wrapper editable">'
				+ '<a href="#" class="delete" title="' + delMsg.plain() + '" />'
				+ '<img src="" data-attachment="' + attachment + '" />'
				+ '<p class="attachment-name">' + attachment + '</p>'
			+ '</span>'
		+ '</div>'
	);
	return $attachment;
};

bs.ui.widget.TextInputAttachments.prototype.renderAttachment = function( attachment, id ) {
	var me = this;
	this.api.get({
		action:'query',
		format: 'json',
		titles: 'File:' + attachment,
		prop: 'imageinfo',
		iiprop: 'url',
		iiurlwidth: 200
	} ).done( function( data ) {
		me.$element.find( '#' + id + ' img' ).first().attr(
			"src",
			data.query.pages[Object.keys(data.query.pages)[0]].imageinfo[0].thumburl
		);
	} );
};

bs.ui.widget.TextInputAttachments.prototype.getValue = function() {
	return this.attachments;
};

bs.ui.widget.TextInputAttachments.prototype.setValue = function( attachments ) {
	this.attachments = attachments;
	this.render();
};

bs.ui.widget.TextInputAttachments.prototype.render = function() {
	var me = this;
	me.$element.html( '' );
	if( !me.attachments ) {
		return;
	}
	for( var i in me.attachments.images ) {
		var id = 'attachmentEditor-' + bs.social.generateUniqueId();
		var $attachment = me.makeAttachmentEditor( me.attachments.images[i], id );
		me.$element.append( $attachment );
		me.renderAttachment( me.attachments.images[i], id );
		$( document ).on( 'click', '#' + id + ' a.delete', function( e ) {
			e.stopPropagation();
			var img = $( this ).siblings( 'img' ).first();
			if( !img || img.length < 1 ) {
				return false;
			}
			me.emit( 'remove', me, {
				files: [ img.attr( 'data-attachment' ) ]
			} );
			return false;
		} );
	}
	me.emit( 'rendercomplete', me, {} );
};
