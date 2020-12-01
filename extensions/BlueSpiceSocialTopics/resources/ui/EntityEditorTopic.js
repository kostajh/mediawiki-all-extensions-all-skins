bs.social.EntityEditorTopic = function ( config, entity ) {
	bs.social.EntityEditorText.call( this, config, entity );
};
OO.initClass( bs.social.EntityEditorTopic );
OO.inheritClass( bs.social.EntityEditorTopic, bs.social.EntityEditorText );

bs.social.EntityEditorTopic.prototype.makeFields = function() {
	var fields = bs.social.EntityEditorTopic.super.prototype.makeFields.apply(
		this
	);

	this.topictitle = new OO.ui.TextInputWidget( {
		placeholder: this.getVarLabel( 'topictitle' ),
		autosize: false,
		value: this.getEntity().data.get( 'topictitle', '' )
	});
	fields.topictitle = this.topictitle;

	var disabled = false;
	var discussiontitleid = this.getEntity().data.get(
		'discussiontitleid',
		0
	);
	var titleText = this.getEntity().data.get(
		'relatedtitle',
		''
	);
	if( discussiontitleid > 0 && titleText !== '' ) {
		disabled = true;
	}
	var option = '', localData = [];
	if( discussiontitleid > 0 ) {
		option = '<option selected="selected" value="' + discussiontitleid + '">' + titleText + '</option>';
		localData.push({
			text: titleText,
			id: discussiontitleid
		});
	}
	//fake oojs item - use working js
	this.discussiontitleid = {
		select2: true,
		$element: $(
			'<div class="oo-ui-layout oo-ui-labelElement oo-ui-fieldLayout oo-ui-fieldLayout-align-top">'
				+ '<label>'
					+ this.getVarLabel( 'discussiontitleid' )
					+ '<select style="width:100%">'
						+ option
					+ '</select>'
				+ '</label>'
			+ '</div>'
		),
		setElementGroup: function(){}
	};
	var namespaces = mw.config.get('wgNamespaceIds');
	var talkns = [];
	for( var i in namespaces ) {
		if( namespaces[i] < 1 || namespaces[i]%2 === 0 ) {
			continue;
		}
		talkns.push( namespaces[i] );
	}
	this.discussiontitleid.$element.find( 'select' ).select2({
		data: localData,
		placeholder: this.getVarLabel( 'discussiontitleid' ),
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
						namespaces: talkns
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
	fields.discussiontitleid = this.discussiontitleid;

	return fields;
};
bs.social.EntityEditorTopic.prototype.addContentFieldsetItems = function() {
	this.contentfieldset.addItems( [
		new OO.ui.FieldLayout( this.topictitle, {
			label: this.getVarLabel( 'topictitle' ),
			align: 'top'
		}),
		this.discussiontitleid
	]);
	bs.social.EntityEditorTopic.super.prototype.addContentFieldsetItems.apply(
		this
	);
};

bs.social.EntityEditorTopic.prototype.getShortModeField = function() {
	return this.topictitle;
};
