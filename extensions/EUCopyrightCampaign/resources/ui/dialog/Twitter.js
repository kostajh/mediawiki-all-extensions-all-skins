( function( mw, $ ) {
	eucc.ui.dialog.Twitter = function( cfg ) {
		this.representative = cfg.representative;
		this.defaultText = cfg.defaultText;

		eucc.ui.dialog.Twitter.parent.call( this, cfg );

		this.$element.addClass( 'eucc-twitter-dialog' );
	};

	OO.inheritClass( eucc.ui.dialog.Twitter, OO.ui.ProcessDialog );

	eucc.ui.dialog.Twitter.static.name = 'euccTwitterDialog';
	eucc.ui.dialog.Twitter.static.title = mw.message( 'eucc-twitter-dialog-title' ).text();
	eucc.ui.dialog.Twitter.static.actions = [ {
		action: 'markAsDone',
		label: mw.message( 'eucc-dialog-mark-as-done-label' ).text(),
		flags: [ 'progressive', 'primary' ]
	}, {
		flags: 'safe',
		framed: false,
		icon: 'close'
	} ];

	eucc.ui.dialog.Twitter.prototype.initialize = function() {
		eucc.ui.dialog.Twitter.parent.prototype.initialize.apply( this, arguments );

		this.content = new OO.ui.PanelLayout( {
			padded: true,
			expanded: true
		} );

		this.tweetBox = new OO.ui.MultilineTextInputWidget( {
			value: this.defaultText,
			autosize: false,
			rows: 4,
			maxLength: 280
		} );
		this.tweetBox.on( 'change', this.onChangeTweetText.bind( this ) );

		this.tweetButton = new OO.ui.ButtonWidget( {
				label: mw.message(
					'eucc-contact-button-tweet-label',
					this.representative.twitter
				).text(),
				flags: [ 'progressive', 'primary' ]
		} );
		this.tweetButton.on( 'click', this.onTweet.bind( this ) );

		this.content.$element.append(
			this.tweetBox.$element,
			this.tweetButton.$element
		);

		this.$body.append( this.content.$element );
		this.updateSize();
	};

	eucc.ui.dialog.Twitter.prototype.getActionProcess = function ( action ) {
		var dialog = this;
		if ( action === 'markAsDone' ) {
			return new OO.ui.Process( function () {
				dialog.close( { action: action } );
			} );
		}
		return eucc.ui.dialog.Twitter.parent.prototype.getActionProcess.call( this, action );
	};

	eucc.ui.dialog.Twitter.prototype.onChangeTweetText = function( value ) {
		this.tweetButton.setDisabled( !( value.length > 0 ) );
	}

	eucc.ui.dialog.Twitter.prototype.onTweet = function() {
		var tweetText = this.tweetBox.getValue();
		tweetText = this.representative.twitter + ' ' + tweetText;
		tweetText = encodeURIComponent( tweetText );

		var url = "https://twitter.com/intent/tweet?text=##TEXT##";
		url = url.replace( '##TEXT##', tweetText );

		var tweetWin = window.open();
		tweetWin.opener = null
		tweetWin.location = url;
		tweetWin.focus();
	};

} ) ( mediaWiki, jQuery );
