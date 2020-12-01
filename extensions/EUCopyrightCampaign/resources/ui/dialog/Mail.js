( function( mw, $ ) {
	eucc.ui.dialog.Mail = function( cfg ) {
		this.representative = cfg.representative;
		this.emailText = cfg.emailText;
		this.emailTextForEmailClient = cfg.emailTextForEmailClient;

		eucc.ui.dialog.Mail.parent.call( this, cfg );

		this.connect( this, {
			copyEmail: 'onCopyMailDone'
		} );

		this.$element.addClass( 'eucc-mail-dialog' );
	};

	OO.inheritClass( eucc.ui.dialog.Mail, OO.ui.ProcessDialog );

	eucc.ui.dialog.Mail.static.name = 'euccMailDialog';
	eucc.ui.dialog.Mail.static.title = mw.message( 'eucc-mail-dialog-title' ).text();
	eucc.ui.dialog.Mail.static.actions = [ {
		action: 'markAsDone',
		label: mw.message( 'eucc-dialog-mark-as-done-label' ).text(),
		flags: [ 'progressive', 'primary' ]
	}, {
		flags: 'safe',
		framed: false,
		icon: 'close'
	}, {
		action: 'copyText',
		label: mw.message( 'eucc-mail-dialog-copy-text-label' ).text(),
		flags: [ 'progressive', 'primary' ]
	}, {
		action: 'sendMail',
		label: mw.message( 'eucc-mail-dialog-send-mail-label' ).text(),
		flags: [ 'progressive' ],
		framed: false
	} ];

	eucc.ui.dialog.Mail.prototype.initialize = function() {
		eucc.ui.dialog.Mail.parent.prototype.initialize.apply( this, arguments );

		this.content = new OO.ui.PanelLayout( {
			padded: true,
			expanded: true
		} );

		this.label = new OO.ui.LabelWidget( {
			label: mw.message( 'eucc-mail-dialog-top-label' ).text()
		} );
		this.label.$element.addClass( 'eucc-mail-dialog-top-label' );

		var targetRepMessage = mw.message(
			'eucc-mail-dialog-target-rep',
			this.representative.fullName,
			this.representative.email
		).escaped();
		targetRepMessage = targetRepMessage.replace(
			this.representative.email,
			'<span>' + this.representative.email + '</span>'
		);
		this.targetRep = new OO.ui.LabelWidget( {
			label: new OO.ui.HtmlSnippet( targetRepMessage )
		} );
		this.targetRep.$element.addClass( 'eucc-mail-dialog-rep-info' );

		this.mailTextWidget = new OO.ui.MultilineTextInputWidget( {
			readOnly: true,
			value: this.emailText,
			autosize: true
		} );

		this.content.$element.append(
			this.label.$element,
			this.targetRep.$element,
			this.mailTextWidget.$element
		);

		this.$body.append( this.content.$element );
	};

	eucc.ui.dialog.Mail.prototype.getActionProcess = function ( action ) {
		var dialog = this;
		if ( action === 'markAsDone' ) {
			return new OO.ui.Process( function () {
				dialog.close( { action: action } );
			} );
		}
		if ( action === 'copyText' ) {
			return new OO.ui.Process( function () {
				this.copyToClipboard();
			}.bind( this ) );
		}
		if ( action === 'sendMail' ) {
			return new OO.ui.Process( function () {
				this.openMailClient();
			}.bind( this ) );
		}
		return eucc.ui.dialog.Mail.parent.prototype.getActionProcess.call( this, action );
	};

	eucc.ui.dialog.Mail.prototype.copyToClipboard = function() {
		if( !navigator.clipboard ) {
			this.emit( 'copyEmail', this.copyToClipboardFallback( this.emailText ) );
			return;
		}

		// This method works without appending any elements to the DOM,
		// but is supported on only few browsers, and only if content is
		// served over https
		var writePromise = navigator.clipboard.writeText( this.emailText );
		writePromise.then( function() {
			this.emit( 'copyEmail', true );
		}.bind( this ), function() {
			this.emit( 'copyEmail', this.copyToClipboardFallback( this.emailText ) );
		}.bind( this ) );
	};

	/**
	 * Fail-safe way to do it. Supported on almost all browsers
	 *
	 * @param string text Text of the email
	 * @returns boolean
	 */
	eucc.ui.dialog.Mail.prototype.copyToClipboardFallback = function( text ) {
		var $dummyTextArea = $( '<textarea>' ).val( text );
		$dummyTextArea.insertAfter( '.eucc-content-layout' );
		$dummyTextArea.focus();
		$dummyTextArea.select();

		var success = false;
		try {
			success = document.execCommand( 'copy' );
		} catch (err) {
			//We must catch any errors, so that text area always gets removed
		}

		$dummyTextArea.remove();

		return success;
	};

	eucc.ui.dialog.Mail.prototype.addCopyToClipboardMessage = function( success ) {
		var message;
		if( success ) {
			message = mw.message( 'eucc-copy-to-clipboard-success' ).escaped();
		} else {
			message = mw.message( 'eucc-copy-to-clipboard-fail' ).escaped();
		}

		if( this.copyToClipboardStatus === undefined ) {
			this.copyToClipboardStatus = new OO.ui.LabelWidget();
			this.copyToClipboardStatus.$element.addClass( 'eucc-copy-to-clipboard-status' );
			this.content.$element.append( this.copyToClipboardStatus.$element );
		}

		this.copyToClipboardStatus.setLabel( message );
		this.copyToClipboardStatus.$element.show();
		this.updateSize();

		setTimeout( function() {
			this.copyToClipboardStatus.$element.fadeOut( 500, function() {
				this.updateSize();
			}.bind( this ) );
		}.bind( this ), 2000 );
	};

	eucc.ui.dialog.Mail.prototype.onCopyMailDone = function( success ) {
		this.addCopyToClipboardMessage( success );
	};

	eucc.ui.dialog.Mail.prototype.openMailClient = function() {
		window.location.href = this.getMailtoLink();
	};

	eucc.ui.dialog.Mail.prototype.getMailtoLink = function() {
		var link = "mailto:" + this.representative.email;
		link += "?body=" + this.emailTextForEmailClient;

		return link;
	};

} ) ( mediaWiki, jQuery );
