( function( mw, $ ) {
	eucc.ui.ThankYouLayout = function( cfg ) {
		eucc.ui.ThankYouLayout.parent.call( this, cfg );

		this.$contentContainer = $( '<div>' ).addClass( 'eucc-thank-you-content' );

		this.makeMessage();
		this.makeTweet();

		this.$element.append(
			this.$icon,
			this.$contentContainer
		);

		this.$element.addClass( 'eucc-thank-you-layout' );
	};

	OO.inheritClass( eucc.ui.ThankYouLayout, OO.ui.Layout );

	eucc.ui.ThankYouLayout.static.tagName = 'div';

	eucc.ui.ThankYouLayout.prototype.makeMessage = function() {
		this.$icon = $( '<div>' )
			.addClass( 'eucc-thank-you-icon' )
			.append(
				new OO.ui.IconWidget( { icon: 'eucc-check' } ).$element
			);

		this.$contentHeader = $( '<span>' )
			.addClass( 'eucc-thank-you-header' )
			.html( mw.message( 'eucc-thank-you-header' ).escaped() );
		this.$contentText = $( '<span>' )
			.addClass( 'eucc-thank-you-text' )
			.html( mw.message( 'eucc-thank-you-text' ).escaped() );

		this.$contentContainer.append( this.$contentHeader, this.$contentText );
	};

	eucc.ui.ThankYouLayout.prototype.makeTweet = function() {
		this.hashtagLink = "<a href='https://twitter.com/hashtag/fixcopyright?src=hash'>#fixcopyright</a>";
		this.policyLink = "<a class='policy-link' href='https://fixcopyright.wikimedia.org'>fixcopyright.wikimedia.org</a>";

		var tweetText = mw.message( 'eucc-share-tweet-text' ).escaped();
		tweetText = tweetText.replace( '$1', this.hashtagLink );
		tweetText = tweetText.replace( '$2', this.policyLink );

		this.$tweetText = $( '<span>' )
			.addClass( 'eucc-share-tweet-text' )
			.html( tweetText );

		this.tweetLabel = new OO.ui.LabelWidget( {
			label: mw.message( 'eucc-share-tweet-label' ).text()
		} );

		var messageForTwitter = mw.message(
			"eucc-share-tweet-text",
			"#fixcopyright",
			"fixcopyright.wikimedia.org"
		).text();

		// Removed \u{1F449} from message that actually goes out to Twitter
		// IE crashes on it, if escaped, emoji won't show in the tweet

		var twitterPostURL = "https://twitter.com/intent/tweet?button_hashtag=fixcopyright&ref_src=twsrc%5Etfw&text=";
		twitterPostURL += encodeURIComponent( messageForTwitter );

		this.twitterButton = new OO.ui.ButtonWidget( {
			label: mw.message( 'eucc-share-tweet-button-label' ).text(),
			flags: [
				'progressive',
				'primary'
			]
		} );
		this.twitterButton.on( 'click', function() {
			window.location.href = twitterPostURL;
		} );

		this.$tweetContainer = $( '<div>')
			.addClass( 'eucc-thank-you-tweet-container' )
			.append(
				this.tweetLabel.$element,
				this.$tweetText,
				this.twitterButton.$element
			);

		this.$contentContainer.append( this.$tweetContainer );
	};

} ) ( mediaWiki, jQuery );
