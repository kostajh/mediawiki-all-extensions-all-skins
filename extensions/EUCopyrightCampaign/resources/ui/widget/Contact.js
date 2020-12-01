( function( mw, $ ) {
	eucc.ui.ContactWidget = function( cfg ) {
		this.representative = cfg.representative;
		this.country = cfg.country;
		this.countryNameOfficial = this.getCountryNameOfficial();

		eucc.ui.ContactWidget.parent.call( this, cfg );

		this.compiledText = '';
		this.dirty = false;

		this.windowManager = OO.ui.getWindowManager();

		this.makeTopAlternatives();
		this.makeTextComposition();
		this.makeButtons();

		this.$element.append(
			this.$textContainerOuter,
			this.buttonsLayout.$element
		);

		this.$element.addClass( 'eucc-contact-widget' );
	};

	OO.inheritClass( eucc.ui.ContactWidget, OO.ui.Widget );

	eucc.ui.ContactWidget.static.tagName = 'div';

	eucc.ui.ContactWidget.prototype.getCountryNameOfficial = function() {
		var countryName = this.country.name;
		var countryNames = countryName.split( '/' );
		if( countryNames.length > 1 ) {
			return countryNames[ 0 ].trim();
		}
		return countryName;
	};

	eucc.ui.ContactWidget.prototype.makeTopAlternatives = function() {
		this.$topAlternativesContainer = $( '<div>' ).addClass( 'eucc-contact-top-alternatives' );

		var alternativeText = mw.message( 'eucc-contact-top-alternatives-only-phone-text' ).escaped();
		var callButton = new OO.ui.ButtonWidget( {
			framed: false,
			label: mw.message( 'eucc-contact-button-call-short-label' ).text(),
			flags: [
				'progressive'
			]
		} );
		callButton.on( 'click', this.openCallDialog.bind( this ) );

		var tweetButton = new OO.ui.ButtonWidget( {
			framed: false,
			label: mw.message( 'eucc-contact-button-tweet-short-label' ).text(),
			flags: [
				'progressive'
			]
		} );
		tweetButton.on( 'click', this.openTweetDialog.bind( this ) );

		var alternativeText = mw.message( 'eucc-contact-top-alternatives-only-phone-text' ).escaped();
		if( this.representative.twitter !== '' ) {
			alternativeText = mw.message( 'eucc-contact-top-alternatives-text' ).escaped();
			alternativeText = alternativeText.replace( '$2', '<a id="top-alternative-tweet"></a>' );
		}
		alternativeText = alternativeText.replace( '$1', '<a id="top-alternative-call"></a>' );

		this.$topAlternativesContainer.append( $( '<span>' ).html( alternativeText ) );

		this.$topAlternativesContainer.find( 'a#top-alternative-call' ).replaceWith( callButton.$element );

		if( this.representative.twitter !== '' ) {
			this.$topAlternativesContainer.find( 'a#top-alternative-tweet' ).replaceWith( tweetButton.$element );
		}

		this.$element.append( this.$topAlternativesContainer );
	}

	eucc.ui.ContactWidget.prototype.makeButtons = function() {
		var generateMailButton = new OO.ui.ButtonWidget( {
			label: mw.message( 'eucc-contact-button-generate-mail-label' ).text(),
			flags: [
				"primary",
				"progressive"
			]
		} );
		generateMailButton.on( 'click', function() {
			this.validate().done( function() {
				this.openMailDialog();
			}.bind( this ) );
		}.bind( this ) );

		var items = [ generateMailButton ];

		if( this.representative.twitter !== '' ) {
			var tweetButton = new OO.ui.ButtonWidget( {
				label: mw.message(
						'eucc-contact-button-tweet-label',
						this.representative.twitter
					).text(),
				framed: false,
				flags: [
					"progressive"
				]
			} );
			tweetButton.on( 'click', this.openTweetDialog.bind( this ) );
			items.push( tweetButton );
		}

		var callButton = new OO.ui.ButtonWidget( {
			label: mw.message( 'eucc-contact-button-call-label' ).text(),
			framed: false,
			flags: [
				"progressive"
			]
		} );
		callButton.on( 'click', this.openCallDialog.bind( this ) );
		items.push( callButton );

		this.buttonsLayout = new OO.ui.HorizontalLayout( {
			items: items
		} );
	};

	eucc.ui.ContactWidget.prototype.makeTextComposition = function() {
		var greetingText, userInfo, firstPart, preIssueText,
			secondPart, outro, complimentaryClose;

		this.$textContainer = $( '<div>' ).addClass( 'eucc-contact-text-container' );
		this.$textContainerOuter = $( '<div>' ).addClass( 'eucc-contact-text-container-outer' );

		// eucc-email-part-one-v1, eucc-email-part-one-v2, eucc-email-part-one-v3
		this.firstPartMessageKey = this.pickVariation( 'eucc-email-part-one', 3 );
		// eucc-email-outro-v1, eucc-email-outro-v2, eucc-email-outro-v3
		this.outroMessageKey = this.pickVariation( 'eucc-email-outro', 3 );
		this.issues = [ {
				labelKey: 'eucc-option-protect-public-domain-label',
				// eucc-option-protect-public-domain-text-v1, eucc-option-protect-public-domain-text-v2,
				// eucc-option-protect-public-domain-text-v3
				textKey: this.pickVariation( 'eucc-option-protect-public-domain-text', 3 )
			},{
				labelKey: 'eucc-option-freedom-of-panorama-label',
				// eucc-option-freedom-of-panorama-text-v1, eucc-option-freedom-of-panorama-text-v2,
				// eucc-option-freedom-of-panorama-text-v3
				textKey: this.pickVariation( 'eucc-option-freedom-of-panorama-text', 3 )
			},{
				labelKey: 'eucc-option-exception-for-ucg-label',
				// eucc-option-exception-for-ucg-text-v1, eucc-option-exception-for-ucg-text-v2,
				// eucc-option-exception-for-ucg-text-v3
				textKey: this.pickVariation( 'eucc-option-exception-for-ucg-text', 3 )
			},{
				labelKey: 'eucc-option-exception-for-text-and-data-mining-label',
				// eucc-option-exception-for-text-and-data-mining-text-v1,
				// eucc-option-exception-for-text-and-data-mining-text-v2,
				// eucc-option-exception-for-text-and-data-mining-text-v3
				textKey: this.pickVariation( 'eucc-option-exception-for-text-and-data-mining-text', 3 )
			} ];

		greetingText = mw.message(
			'eucc-email-greeting',
			this.representative.fullName
		).escaped();

		userInfo = mw.message(
			'eucc-email-user-info',
			'##FIRST_NAME##',
			'##LAST_NAME##',
			this.countryNameOfficial
		).escaped();

		// Not exactly the cleanest solution, doing double replacement, but i still think
		// its better than having complicated string operations
		userInfo = userInfo.replace( '##FIRST_NAME##', '<label class="label-first-name" for="first_name"></label><input id="first_name" name="first_name">' );
		userInfo = userInfo.replace( '##LAST_NAME##', '<label class="label-last-name" for="last_name"></label><input id="last_name" name="last_name">' );

		firstPart = mw.message( this.firstPartMessageKey ).escaped();
		preIssueText = mw.message( 'eucc-email-issues-intro' ).escaped();
		secondPart = mw.message( 'eucc-email-part-two' ).escaped();
		outro = mw.message( this.outroMessageKey ).escaped();
		complimentaryClose = mw.message( 'eucc-email-complimentary-close' ).escaped();

		this.makeInputs();
		this.makeNewsletterContainer();

		this.$textContainer.append(
			new OO.ui.HtmlSnippet( '<p>' + greetingText + '</p>').toString(),
			new OO.ui.HtmlSnippet( '<p>' + userInfo + '</p>').toString(),
			this.customTextWidget.$element,
			new OO.ui.HtmlSnippet( '<p>' + firstPart + '</p>').toString(),
			new OO.ui.HtmlSnippet( '<p>' + preIssueText + '</p>').toString(),
			this.issueSelector.$element,
			new OO.ui.HtmlSnippet( '<p>' + secondPart + '</p>').toString(),
			new OO.ui.HtmlSnippet( '<p>' + outro + '</p>').toString(),
			new OO.ui.HtmlSnippet( '<p>' + complimentaryClose + '</p>').toString(),
			this.emailInput.$element,
			this.emailInput.input.$element,
			this.$newsletterContainer
		);

		this.$textContainer.find( '#first_name' ).replaceWith( this.firstNameInput.$element );
		this.$textContainer.find( '#last_name' ).replaceWith( this.lastNameInput.$element );

		this.$textContainer.find( '.label-first-name' ).text( mw.message( 'eucc-email-first-name-input-placeholder' ) );
		this.$textContainer.find( '.label-last-name' ).text( mw.message( 'eucc-email-last-name-input-placeholder' ) );

		this.$textContainerOuter.append( this.$textContainer );
	};

	eucc.ui.ContactWidget.prototype.makeInputs = function() {
		this.firstNameInput = new OO.ui.TextInputWidget( {
			name: 'first_name',
			required: true,
			placeholder: mw.message( 'eucc-email-first-name-input-placeholder' ).text(),
			inputId: 'first_name'
		} );
		this.firstNameInput.on( 'change', this.onInputChange.bind( this ) );
		this.lastNameInput = new OO.ui.TextInputWidget( {
			name: 'last_name',
			required: true,
			placeholder: mw.message( 'eucc-email-last-name-input-placeholder' ).text(),
			inputId: 'last_name'
		} );
		this.lastNameInput.on( 'change', this.onInputChange.bind( this ) );
		this.customTextWidget = new OO.ui.MultilineTextInputWidget( {
			autosize: true,
			classes: [ 'eucc-multiline-text-input' ],
			placeholder: mw.message( 'eucc-email-custom-text-placeholder' ).text(),
		} );
		this.customTextWidget.on( 'change', this.onInputChange.bind( this ) );

		var issueSelectorItems = [];
		var first = true;
		for( var idx in this.issues ) {
			var issue = this.issues[ idx ];
			issueSelectorItems.push(
				new eucc.ui.CheckboxMultioptionTextWidget( {
					data: mw.message( issue.labelKey ).escaped(),
					label: mw.message( issue.labelKey ).escaped(),
					contentText: mw.message( issue.textKey ).escaped(),
					selected: first || false
				} )
			);
			first = false;
		}
		this.issueSelector = new OO.ui.CheckboxMultiselectWidget( {
			items: issueSelectorItems
		} );
		this.issueSelector.on( 'change', this.onInputChange.bind( this ) );

		this.emailInput = new OO.ui.LabelWidget( {
			label: mw.message( 'eucc-email-email-input-placeholder' ).text(),
			classes: [ 'label-email' ],
			input: new OO.ui.TextInputWidget( {
				name: 'email',
				type: 'email',
				required: true,
				placeholder: mw.message( 'eucc-email-email-input-placeholder' ).text()
			} )
		} );
		this.emailInput.input.on( 'change', this.onInputChange.bind( this ) );
	};

	eucc.ui.ContactWidget.prototype.makeNewsletterContainer = function() {
		this.newsletterCheckbox = new OO.ui.CheckboxInputWidget( {
			selected: false,
			name: 'newsletter'
		} );
		this.newsletterCheckbox.on( 'change', this.onInputChange.bind( this ) );

		var ibmWatsonLink = "<a href='##IBM_WATSON_URL##'>##IBM_WATSON_LABEL##</a>";
		ibmWatsonLink = ibmWatsonLink.replace( '##IBM_WATSON_URL##', mw.config.get( 'euccMailingListPrivacyPolicyURL' ) );
		ibmWatsonLink = ibmWatsonLink.replace(
			'##IBM_WATSON_LABEL##',
			mw.message( 'eucc-email-privacy-policy-label'
		).escaped() );

		var label = mw.message( 'eucc-email-newsletter-label' ).escaped();
		label = label.replace( '$1', ibmWatsonLink );

		this.newsletterLabel = new OO.ui.LabelWidget( {
			label: new OO.ui.HtmlSnippet( '<span>' + label + '</span>' ),
			input: this.newsletterCheckbox
		} );

		this.$newsletterContainer = $( '<div>' )
			.addClass( 'eucc-email-newsletter-container' )
			.append(
				this.newsletterCheckbox.$element,
				this.newsletterLabel.$element
			);
	};

	eucc.ui.ContactWidget.prototype.onInputChange = function() {
		this.dirty = true;
	};

	eucc.ui.ContactWidget.prototype.isDirty = function() {
		return this.dirty;
	};

	eucc.ui.ContactWidget.prototype.getSelectedIssues = function() {
		var selected = this.issueSelector.items.filter( function ( item ) {
			return item.isSelected();
		} );

		var selectedIssues = [];
		for( var idx in selected ) {
			selectedIssues.push( {
				header: selected[ idx ].data,
				text: selected[ idx ].getText()
			} );
		}

		return selectedIssues;
	};

	eucc.ui.ContactWidget.prototype.validate = function() {
		var dfd = $.Deferred();

		var inputsToValidate = [
			this.firstNameInput,
			this.lastNameInput,
			this.emailInput.input
		];

		this.validateInternaly( inputsToValidate, dfd );

		return dfd.promise();
	};

	eucc.ui.ContactWidget.prototype.validateInternaly = function( inputsToValidate, dfd ) {
		if( inputsToValidate.length === 0 ) {
			return dfd.resolve();
		}

		inputsToValidate[ 0 ].getValidity()
			.done( function() {
				inputsToValidate.splice( 0, 1 );
				this.validateInternaly( inputsToValidate, dfd );
			}.bind( this ) )
			.fail( function() {
				inputsToValidate[ 0 ].focus();
				inputsToValidate[ 0 ].setValidityFlag( false );
				dfd.reject();
			} );
	};

	eucc.ui.ContactWidget.prototype.getUserInfo = function() {
		return {
			firstName: this.firstNameInput.getValue(),
			lastName: this.lastNameInput.getValue(),
			emailAddress: this.emailInput.input.getValue(),
			country: this.country,
			newsletterSignup: this.newsletterCheckbox.isSelected(),
			emailText: this.getCompiledText(),
			freeText: this.customTextWidget.getValue(),
			selectedIssues: this.getSelectedIssues(),
			selectedRepresentative: this.representative,
		};
	};

	eucc.ui.ContactWidget.prototype.getCompiledText = function() {
		var greetingText, userInfo, firstPart, preIssueText,
			secondPart, outro, complimentaryClose;

		this.compiledText = '';

		greetingText = mw.message(
			'eucc-email-greeting',
			this.representative.fullName
		).text();
		this.addParagraph( greetingText );

		userInfo = mw.message(
			'eucc-email-user-info',
			this.firstNameInput.getValue(),
			this.lastNameInput.getValue(),
			this.countryNameOfficial
		).text();
		this.addParagraph( userInfo );

		if( this.customTextWidget.getValue() ) {
			this.addParagraph( this.customTextWidget.getValue() );
		}

		firstPart = mw.message( this.firstPartMessageKey ).text();
		preIssueText = mw.message( 'eucc-email-issues-intro' ).text();
		secondPart = mw.message( 'eucc-email-part-two' ).text();
		outro = mw.message( this.outroMessageKey ).text();
		complimentaryClose = mw.message( 'eucc-email-complimentary-close' ).text();

		this.addParagraph( firstPart );
		this.addParagraph( preIssueText );

		var selectedIssues = this.getSelectedIssues();
		for( var idx in selectedIssues ) {
			this.addParagraph( selectedIssues[ idx ].text );
		}

		this.addParagraph( secondPart );
		this.addParagraph( outro );
		this.addParagraph( complimentaryClose );
		this.addParagraph( this.firstNameInput.getValue() + ' ' +
			this.lastNameInput.getValue() + ' (' + this.emailInput.input.getValue() +
			')' );

		// When text is compiled, form is considered clean
		this.dirty = false;

		return this.compiledText;
	};

	eucc.ui.ContactWidget.prototype.addParagraph = function( text ) {
		this.compiledText += text + "\n\n";
	};

	eucc.ui.ContactWidget.prototype.getTextForEmailClient = function() {
		// In mailto: only allowed link break character is "%0D%0A"
		// https://tools.ietf.org/html/rfc2368#page-3
		var emailText = this.getCompiledText();
		return emailText.replace( new RegExp( "\n", 'g' ), "%0D%0A" );
	};

	eucc.ui.ContactWidget.prototype.pickVariation = function( baseMsgKey, numberOfVariations ) {
		var variationNumber = Math.floor( Math.random() * ( numberOfVariations ) ) + 1;
		return baseMsgKey + '-v' + variationNumber;
	};

	eucc.ui.ContactWidget.prototype.openCallDialog = function() {
		if( !this.callDialog ) {
			this.callDialog = new eucc.ui.dialog.Call( {
				representative: this.representative,
				callScriptContainer: $( '#eucc-call-script' ),
				size: 'larger'
			} );
			this.windowManager.addWindows( [ this.callDialog ] );
		}
		this.windowManager.openWindow( this.callDialog )
			.closed.then( this.onDialogClose.bind( this ) );
		this.emit( 'dialogOpened', 'call' );
	};

	eucc.ui.ContactWidget.prototype.openTweetDialog = function() {
		if( !this.twitterDialog ) {
			this.twitterDialog = new eucc.ui.dialog.Twitter( {
				representative: this.representative,
				defaultText: mw.message( 'eucc-tweet-at-rep-default-text' ).text(),
				size: 'large'
			} );
			this.windowManager.addWindows( [ this.twitterDialog ] );
		}
		this.windowManager.openWindow( this.twitterDialog )
			.closed.then( this.onDialogClose.bind( this ) );
		this.emit( 'dialogOpened', 'tweet' );
	};

	eucc.ui.ContactWidget.prototype.openMailDialog = function() {
		this.mailDialog = new eucc.ui.dialog.Mail( {
			representative: this.representative,
			emailText: this.getCompiledText(),
			emailTextForEmailClient: this.getTextForEmailClient(),
			size: 'large'
		} );
		this.windowManager.addWindows( [ this.mailDialog ] );

		this.windowManager.openWindow( this.mailDialog )
			.closed.then( this.onDialogClose.bind( this ) );
		this.emit( 'dialogOpened', 'mail' );
	};

	eucc.ui.ContactWidget.prototype.onDialogClose = function( data ) {
		if( data === undefined ) {
			return;
		}
		if( data.action === 'markAsDone' ) {
			this.emit( 'actionDone' );
		}
	}

} ) ( mediaWiki, jQuery );
