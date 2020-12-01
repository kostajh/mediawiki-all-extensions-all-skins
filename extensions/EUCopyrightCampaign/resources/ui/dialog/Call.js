( function( mw, $ ) {
	eucc.ui.dialog.Call = function( cfg ) {
		this.representative = cfg.representative;
		this.$callScript = cfg.callScriptContainer;

		eucc.ui.dialog.Call.parent.call( this, cfg );

		this.$element.addClass( 'eucc-call-dialog' );
	};

	OO.inheritClass( eucc.ui.dialog.Call, OO.ui.ProcessDialog );

	eucc.ui.dialog.Call.static.name = 'euccCallDialog';
	eucc.ui.dialog.Call.static.title = mw.message( 'eucc-call-dialog-title' ).text();
	eucc.ui.dialog.Call.static.actions = [ {
		action: 'markAsDone',
		label: mw.message( 'eucc-dialog-mark-as-done-label' ).text(),
		flags: [ 'progressive', 'primary' ]
	}, {
		flags: 'safe',
		framed: false,
		icon: 'close'
	} ];

	eucc.ui.dialog.Call.prototype.initialize = function() {
		eucc.ui.dialog.Call.parent.prototype.initialize.apply( this, arguments );

		this.content = new OO.ui.PanelLayout( {
			padded: false,
			expanded: true
		} );

		this.makeContactInfo();
		this.makeCallScript();

		this.$body.append( this.content.$element );
		this.updateSize();
	};

	eucc.ui.dialog.Call.prototype.makeContactInfo = function() {
		this.$contactInfo = $( '<div>' ).addClass( 'eucc-call-dialog-contact-info' );
		var repNameLabel = new OO.ui.LabelWidget( {
			label: this.representative.fullName
		} );
		repNameLabel.$element.addClass( 'eucc-call-dialog-contact-info-name' );

		var brusselsPhoneLabel = new OO.ui.LabelWidget( {
			label: mw.message( 'eucc-call-representative-phone-brussels' ).text()
		} );
		var brusselsPhonePill = this.makePhonePill( this.representative.brusselsPhone );

		var strasbourgPhoneLabel = new OO.ui.LabelWidget( {
			label: mw.message( 'eucc-call-representative-phone-strasbourg' ).text()
		} );
		var strasbourgPhonePill = this.makePhonePill( this.representative.strasbourgPhone );

		this.$contactInfo.append(
			repNameLabel.$element,
			brusselsPhoneLabel.$element,
			brusselsPhonePill.$element,
			strasbourgPhoneLabel.$element,
			strasbourgPhonePill.$element
		);
		this.content.$element.append( this.$contactInfo );
	};

	eucc.ui.dialog.Call.prototype.makeCallScript = function() {
		this.$callScriptContainer = $( '<div>' ).addClass( 'eucc-call-dialog-call-script' );
		this.callScriptLabel = new OO.ui.LabelWidget( {
			label: mw.message( 'eucc-call-script-label' ).text()
		} );
		this.callScriptLabel.$element.addClass( 'eucc-call-dialog-call-script-label' );
		this.$callScriptContainer.append( this.callScriptLabel.$element, this.$callScript );
		this.$callScript.css( 'display', 'block' );
		this.content.$element.append( this.$callScriptContainer );
	};

	eucc.ui.dialog.Call.prototype.makePhonePill = function( phone ) {
		var phoneEscaped = mw.html.escape( phone );
		var phoneStripped = phoneEscaped.replace( /[\(\)\s]/g, '' );
		var phoneLink = '<a href="tel:##PHONE_STRIPPED##">##PHONE##</a>';
		phoneLink = phoneLink.replace( new RegExp( '##PHONE##', 'g' ), phoneEscaped );
		phoneLink = phoneLink.replace( new RegExp( '##PHONE_STRIPPED##', 'g' ), phoneStripped );

		var phonePill = new OO.ui.HorizontalLayout( {
			items: [
				new OO.ui.IconWidget( {
					icon: 'call',
					flags: [
						'progressive'
					]
				} ),
				new OO.ui.LabelWidget( {
					label: new OO.ui.HtmlSnippet( phoneLink )
				} )
			]
		} );
		phonePill.$element.addClass( 'eucc-call-dialog-phone-pill' );
		return phonePill;
	};

	eucc.ui.dialog.Call.prototype.getActionProcess = function ( action ) {
		var dialog = this;
		if ( action === 'markAsDone' ) {
			return new OO.ui.Process( function () {
				dialog.close( { action: action } );
			} );
		}
		return eucc.ui.dialog.Call.parent.prototype.getActionProcess.call( this, action );
	};

} ) ( mediaWiki, jQuery );
