bs.social.EntityEditorStash = function ( config, entity ) {
	// do not allow visual editor here
	config.visualEditor = false;
	config.shortMode = false;
	bs.social.EntityEditorText.call( this, config, entity );
};
OO.initClass( bs.social.EntityEditorStash );
OO.inheritClass( bs.social.EntityEditorStash, bs.social.EntityEditorText );

bs.social.EntityEditorStash.prototype.getShortModeField = function() {
	return null;
};

bs.social.EntityEditorStash.prototype.makeFields = function() {
	var fields = bs.social.EntityEditorStash.super.prototype.makeFields.apply(
		this
	);

	var me = this;
	//overwrite the text widget to remove all may be editor stuff, that breaks
	//everything. Also do not use an actual hidden field, as these can not store
	//any values for no reason... oojs -.-
	me.text = new OO.ui.TextInputWidget( {
		value: me.getEntity().data.get( 'text', '' ),
		visible: false
	});
	fields.text = me.text;

	me.attachments = new bs.ui.widget.TextInputAttachments( {
		attachments: me.getEntity().data.get( 'attachments' )
	} );

	fields.attachments = me.attachments;
	me.attachments.on( 'remove', function( e, data ) {
		me.removeFiles( data.files );
	} );

	if( bs.ui.widget.TextInputMultiUpload ) {
		me.dropzone =  new bs.ui.widget.TextInputMultiUpload( {} );
		fields.dropzone = me.dropzone;
		me.dropzone.on( 'change', function( e, data ) {
			me.addFiles( data.files );
		} );
	}

	if( bs.ui.widget.TextInputFileSelect ) {
		me.insertfile = new bs.ui.widget.TextInputFileSelect( {
			attachments: me.attachments
		} );
		fields.insertfile = me.insertfile;
		me.insertfile.on( 'change', function( e, data ) {
			me.addFiles( data.files );
		} );
	}

	//currently broken
	/*if( bs.ui.widget.TextInputLinkSelect ) {
		this.insertlink = new bs.ui.widget.TextInputLinkSelect( {} );
		fields.insertlink = this.insertlink;
	}*/

	var disabled = false;
	var wikipageid = me.getEntity().data.get(
		'wikipageid',
		0
	);
	var titleText = me.getEntity().data.get(
		'relatedtitle',
		''
	);
	if( wikipageid > 0 && titleText !== '' ) {
		disabled = true;
	}
	var option = '', localData = [];
	if( wikipageid > 0 ) {
		option = '<option selected="selected" value="' + wikipageid + '">' + titleText + '</option>';
		localData.push({
			text: titleText,
			id: wikipageid
		});
	}
	//fake oojs item - use working js
	this.wikipageid = {
		select2: true,
		$element: $(
			'<div class="oo-ui-layout oo-ui-labelElement oo-ui-fieldLayout oo-ui-fieldLayout-align-top">'
				+ '<label>'
					+ me.getVarLabel( 'wikipageid' )
					+ '<select style="width:100%">'
						+ option
					+ '</select>'
				+ '</label>'
			+ '</div>'
		),
		setElementGroup: function(){}
	};
	var namespaces = mw.config.get( 'wgNamespaceIds' );
	var ns = [];
	for( var i in namespaces ) {
		if( namespaces[i] < 0 || namespaces[i] === bs.ns.NS_SOCIALENTITY ) {
			continue;
		}
		if( namespaces[i] !== 0 && namespaces[i]%2 !== 0 ) {
			continue;
		}
		ns.push( namespaces[i] );
	}
	me.wikipageid.$element.find( 'select' ).select2({
		data: localData,
		placeholder: me.getVarLabel( 'wikipageid' ),
		allowClear: true,
		disabled: disabled,
		ajax: {
			url: mw.util.wikiScript( 'api' ),
			dataType: 'json',
			tape: 'POST',
			data: function (params) {
				return {
					action: 'bs-socialtitlequery-store',
					query: params.term,
					options: JSON.stringify({
						namespaces: ns
					})
				};
			},
			processResults: function (data) {
				var results = [];
				$.each(data.results, function (index, result) {
					results.push({
						id: result.page_id,
						text: result.prefixedText
					});
				});
				return {
					results: results
				};
			}
		},
		initSelection: function(element, callback) {
			return callback( localData );
		},
		minimumInputLength: 1
	});
	fields.wikipageid = this.wikipageid;

	return fields;
};
bs.social.EntityEditorStash.prototype.addContentFieldsetItems = function() {
	this.contentfieldset.addItems( [
		new OO.ui.FieldLayout( this.attachments, {
			label: mw.message( 'bs-socialwikipage-stash-editor-attachedfiles' ).plain(),
			align: 'top'
		} )
	]);
	if( this.dropzone ) {
		this.contentfieldset.addItems( [ this.dropzone ] );
	}
	this.contentfieldset.addItems( [ this.wikipageid ] );
};

bs.social.EntityEditorStash.prototype.getShortModeField = function() {
	return null;
};

bs.social.EntityEditorStash.prototype.addFiles = function( files ) {
	var me = this;
	me.getEntity().showLoadMask();
	me.getData().done( function( newdata ) {
		var taskData = me.getEntity().getData();
		for( var i in newdata ) {
			taskData[i] = newdata[i];
		}
		taskData.files = files;

		bs.api.tasks.execSilent( 'socialstash', 'addFiles', taskData )
		.done( function( response ) {
			if( !response.success ) {
				if( response.message && response.message !== '' ) {
					OO.ui.alert( response.message );
				}
				me.getEntity().hideLoadMask();
				return;
			}
			var data = JSON.parse( response.payload.entity );
			me.attachments.setValue( data.attachments );
			me.text.setValue( data.text );
			me.getEntity().hideLoadMask();
		});
	});
};

bs.social.EntityEditorStash.prototype.removeFiles = function( files ) {
	var me = this;
	me.getEntity().showLoadMask();
	me.getData().done( function( newdata ) {
		var taskData = me.getEntity().getData();
		for( var i in newdata ) {
			taskData[i] = newdata[i];
		}
		taskData.files = files;

		bs.api.tasks.execSilent( 'socialstash', 'removeFiles', taskData )
		.done( function( response ) {
			if( !response.success ) {
				if( response.message && response.message !== '' ) {
					OO.ui.alert( response.message );
				}
				me.getEntity().hideLoadMask();
				return;
			}
			var data = JSON.parse( response.payload.entity );
			me.attachments.setValue( data.attachments );
			me.text.setValue( data.text );
			me.getEntity().hideLoadMask();
		});
	});
};
