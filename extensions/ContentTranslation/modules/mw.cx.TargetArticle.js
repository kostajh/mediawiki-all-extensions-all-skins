/**
 * Target Article for CX - Validation, Publishing, Success and Error handling.
 *
 * @copyright See AUTHORS.txt
 * @license GPL-2.0-or-later
 *
 * @param {mw.cx.dm.Translation} translation
 * @param {ve.init.mw.CXTarget} veTarget
 * @param {Object} config Translation configuration
 */
mw.cx.TargetArticle = function MWCXTargetArticle( translation, veTarget, config ) {
	this.translation = translation;
	this.veTarget = veTarget;
	this.config = config;
	this.siteMapper = config.siteMapper;
	this.sourceTitle = config.sourceTitle;
	this.sourceLanguage = config.sourceLanguage;
	this.targetLanguage = config.targetLanguage;

	// Mixin constructors
	OO.EventEmitter.call( this );

	// Properties
	this.captcha = null;
	this.captchaDialog = null;
};

/* Inheritance */

OO.mixinClass( mw.cx.TargetArticle, OO.EventEmitter );

/* Events */

/**
 * @event publishCancel
 *
 * User canceled the publishing process.
 */

/**
 * @event publishSuccess
 *
 * Translation is successfully published.
 */

/**
 * @event captchaCancel
 *
 * User exited the captcha dialog.
 */

/**
 * @event publishError
 *
 * Error occured during publishing.
 * @param {OO.ui.Error} error
 */

/* Static Methods */

/**
 * Clean up the input document by removing CX specific markup and attributes.
 *
 * @param {HTMLDocument} doc
 * @return {HTMLDocument} Cleaned up document.
 **/
mw.cx.TargetArticle.static.getCleanedupContent = function ( doc ) {
	Array.prototype.forEach.call( doc.body.querySelectorAll( 'article, section, [data-segmentid]' ), function ( segment ) {
		var parent = segment.parentNode;
		// move all children out of the element
		while ( segment.firstChild ) {
			parent.insertBefore( segment.firstChild, segment );
		}
		segment.remove();
	} );

	// Remove all unadapted links except the ones that are explicitly marked as missing.
	// Refer ve.ui.CXLinkContextItem#createRedLink
	Array.prototype.forEach.call( doc.querySelectorAll( '.cx-link' ), function ( link ) {
		var dataCX = JSON.parse( link.getAttribute( 'data-cx' ) || '{}' );
		if ( dataCX.adapted === false && OO.getProp( dataCX, 'targetTitle', 'missing' ) !== true ) {
			// Replace the link with its inner content.
			link.replaceWith( link.innerHTML );
		} else {
			[ 'data-linkid', 'class', 'title', 'id' ].forEach( function ( attr ) {
				link.removeAttribute( attr );
			} );
		}
	} );

	// Remove empty references. Such references are initially marked as unadapted and CX data
	// is reset upon editing, so we check if reference is still marked as unadapted.
	Array.prototype.forEach.call( doc.querySelectorAll( '.mw-ref' ), function ( element ) {
		var dataCX = JSON.parse( element.getAttribute( 'data-cx' ) || '{}' );

		if ( dataCX.adapted === false ) {
			element.parentNode.removeChild( element );
		}
	} );

	// Remove all data-cx attributes. It is irrelevant for publish, reduces the HTML size.
	Array.prototype.forEach.call( doc.querySelectorAll( '[data-cx]' ), function ( element ) {
		element.removeAttribute( 'data-cx' );
	} );

	// Remove all id attributes from table cells, div tags that are assigned by cxserver.
	Array.prototype.forEach.call(
		doc.querySelectorAll( 'tr[id], td[id], th[id], table[id], tbody[id], thead[id], div[id]' ), function ( element ) {
			element.removeAttribute( 'id' );
		}
	);

	return doc;
};

/* Methods */

/**
 * Publish the translated content to target wiki.
 *
 * @param {boolean} hasIssues True if translation being published has some issues.
 * @param {boolean} shouldAddHighMTCategory Whether article being published
 * should be added to high MT tracking category.
 */
mw.cx.TargetArticle.prototype.publish = function ( hasIssues, shouldAddHighMTCategory ) {
	var apiParams = {
		assert: 'user',
		action: 'cxpublish',
		from: this.sourceLanguage,
		to: this.targetLanguage,
		sourcetitle: this.sourceTitle,
		title: this.getTargetTitle(),
		html: this.getContent( true ),
		categories: this.getTargetCategories( shouldAddHighMTCategory ),
		publishtags: this.getTags(),
		wpCaptchaId: this.captcha && this.captcha.id,
		wpCaptchaWord: this.captcha && this.captcha.input.getValue(),
		cxversion: 2
	};

	// Check for title conflicts
	this.checkForPublishAnyway( this.getTargetTitle(), hasIssues ).then( function () {
		return new mw.Api().postWithToken( 'csrf', apiParams, {
			// A bigger timeout since publishing after converting html to wikitext
			// parsoid is not a fast operation.
			timeout: 100 * 1000 // in milliseconds
		} ).then( this.publishSuccess.bind( this ), this.publishFail.bind( this ) );
	}.bind( this ), function () {
		this.emit( 'publishCancel' );
	}.bind( this ) );
};

/**
 * Publish success handler
 *
 * @param {Object} response Response object from the publishing api
 * @param {Object} jqXHR
 * @return {null|jQuery.Promise}
 */
mw.cx.TargetArticle.prototype.publishSuccess = function ( response, jqXHR ) {
	var publishResult = response.cxpublish;
	if ( publishResult.result === 'success' ) {
		return this.publishComplete();
	}

	if ( publishResult.edit.captcha ) {
		// If there is a captcha challenge, get the solution and retry.
		return this.loadCaptchaDialog().then(
			this.showErrorCaptcha.bind( this, publishResult.edit.captcha )
		);
	}

	// Any other failure
	return this.publishFail( '', publishResult, publishResult, jqXHR );
};

/**
 * @fires publishSuccess
 */
mw.cx.TargetArticle.prototype.publishComplete = function () {
	this.captcha = null;
	this.emit( 'publishSuccess' );
};

/**
 * Publish failure handler
 *
 * The 'messageOrFailObjOrData' parameter could be a string explaining the error,
 * or an object with textStatus, exception and jqXHR keys (but jqXHR can be missing),
 * or equal to data. If data is present, jqXHR is also present. See T176704.
 *
 * @param {string} errorCode
 * @param {string|Object} messageOrFailObjOrData Error message (string), or object with textStatus,
 *   exception and (optionally) jqXHR, or equal to data
 * @param {Object} [data] Data returned by api.php
 * @param {Object} [jqXHR] jQuery XHR object
 */
mw.cx.TargetArticle.prototype.publishFail = function ( errorCode, messageOrFailObjOrData, data, jqXHR ) {
	var editError, editResult;

	if ( !data ) {
		if ( errorCode === 'ok-but-empty' ) {
			this.showPublishError( mw.msg( 'cx-publish-error-empty' ) );
			return;
		}

		this.showErrorException( messageOrFailObjOrData );
		return;
	}

	// Event logging
	mw.hook( 'mw.cx.translation.publish.error' ).fire(
		this.sourceLanguage,
		this.targetLanguage,
		this.sourceTitle,
		this.getTargetTitle(),
		data
	);

	editError = data.error;
	if ( editError ) {
		// Handle spam blacklist error (either from core or from Extension:SpamBlacklist)
		// Example of API result - https://phabricator.wikimedia.org/P8991
		if ( editError.code === 'spamblacklist' ) {
			this.showPublishError(
				mw.msg( 'cx-publish-error-spam-blacklist', editError.info ),
				editError.info
			);
			return;
		} else if ( editError.code.indexOf( 'abusefilter' ) === 0 ) {
			// Handle Abuse Filter errors.
			this.showPublishError(
				mw.msg( 'cx-publish-error-abuse-filter', editError.abusefilter.description ),
				editError.info
			);
			return;
		} else if ( editError.code === 'invalidtitle' ) {
			this.showPublishError(
				mw.message( 'title-invalid-characters', this.getTargetTitle() ).text(),
				JSON.stringify( editError )
			);
			return;
		} else if ( editError.code === 'badtoken' || editError.code === 'assertuserfailed' ) {
			this.showUnrecoverablePublishError(
				mw.msg( 'cx-lost-session-publish' ),
				JSON.stringify( editError )
			);
			return;
		} else if ( editError.code === 'titleblacklist-forbidden' ) {
			this.showPublishError( mw.msg( 'cx-publish-error-title-blacklist' ), JSON.stringify( editError ) );
			return;
		} else if ( editError.code === 'readonly' ) {
			this.showUnrecoverablePublishError( mw.msg( 'cx-publish-error-readonly' ), editError.readonlyreason );
			return;
		}
	}

	editResult = data.edit;
	// Handle captcha
	// Captcha "errors" usually aren't errors. We simply don't know about them ahead of time,
	// so we save once, then (if required) we get an error with a captcha back and try again after
	// the user solved the captcha.
	if ( editResult && editResult.captcha && (
		editResult.captcha.type === 'image' ||
		editResult.captcha.type === 'simple' ||
		editResult.captcha.type === 'math' ||
		editResult.captcha.type === 'question'
	) ) {
		this.loadCaptchaDialog().then( this.showErrorCaptcha.bind( this, editResult ) );
		return;
	}

	// Handle (other) unknown and/or unrecoverable errors
	this.showErrorUnknown( editResult, data, jqXHR );
};

/**
 * Load captcha dialog dependency dynamically, since captcha dialog is rarely shown.
 *
 * @return {jQuery}
 */
mw.cx.TargetArticle.prototype.loadCaptchaDialog = function () {
	return mw.loader.using( 'mw.cx.ui.CaptchaDialog' ).then( this.setupCaptchaDialog.bind( this ) );
};

mw.cx.TargetArticle.prototype.setupCaptchaDialog = function () {
	if ( this.captchaDialog ) {
		// Dialog is already set up
		return;
	}

	this.captchaDialog = new mw.cx.ui.CaptchaDialog();
	this.captchaDialog.connect( this, {
		publish: 'publish',
		cancel: 'onCaptchaCancel'
	} );
	OO.ui.getWindowManager().addWindows( [ this.captchaDialog ] );
};

/**
 * Handle captcha challenge error
 *
 * @param {Object} apiResult publishing API result
 * @fires publishErrorCaptcha
 */
mw.cx.TargetArticle.prototype.showErrorCaptcha = function ( apiResult ) {
	if ( this.captcha ) {
		this.captchaDialog.showErrors( mw.msg( 'cx-captcha-dialog-error' ) );
	}

	this.captcha = {
		input: this.captchaDialog.input,
		id: apiResult.id
	};

	if ( apiResult.type === 'image' ) {
		// FancyCaptcha
		// Based on FancyCaptcha::getFormInformation() (https://git.io/v6mml) and
		// ext.confirmEdit.fancyCaptcha.js in the ConfirmEdit extension.
		mw.loader.load( 'ext.confirmEdit.fancyCaptcha' );
		this.captchaDialog.setFancyCaptcha( apiResult.url );
	} else if ( apiResult.type === 'simple' || apiResult.type === 'math' ) {
		// SimpleCaptcha and MathCaptcha
		this.captchaDialog.setCaptcha( 'captcha-create', apiResult.question, apiResult.mime );
	} else if ( apiResult.type === 'question' ) {
		// QuestyCaptcha
		this.captchaDialog.setCaptcha( 'questycaptcha-create', apiResult.question, apiResult.mime );
	} else {
		mw.log.error( '[CX] Unsupported captcha type: ' + apiResult.type );
		// At this point, we encountered unknown or unsupported captcha type, or ConfirmEdit is
		// malfunctioning in some fashion. User is stuck at this point and cannot publish,
		// but we can at least unblock the UI and show the error message.
		this.onCaptchaCancel();
		this.showUnrecoverablePublishError( mw.msg( 'cx-captcha-unsupported-type' ) );
		return;
	}

	OO.ui.getWindowManager().openWindow( 'cxCaptcha' );
};

/**
 * @fires captchaCancel
 */
mw.cx.TargetArticle.prototype.onCaptchaCancel = function () {
	this.captcha = null;
	this.emit( 'captchaCancel' );
};

/**
 * Show an error based on an exception+textStatus object
 *
 * @param {Object} failObj Object from the rejection params of mw.Api, with exception and textStatus
 */
mw.cx.TargetArticle.prototype.showErrorException = function ( failObj ) {
	var errorMsg = failObj.exception || failObj.textStatus;

	if ( errorMsg instanceof Error ) {
		errorMsg = errorMsg.toString();
	}

	if ( errorMsg ) {
		this.showUnrecoverablePublishError( errorMsg, errorMsg );
	} else {
		this.showErrorUnknown( null, null, failObj.jqXHR );
	}
};

/**
 * Handle unknown publish error
 *
 * @method
 * @param {Object} editResult
 * @param {Object|null} data API response data
 * @param {Object} jqXHR
 */
mw.cx.TargetArticle.prototype.showErrorUnknown = function ( editResult, data, jqXHR ) {
	var errorDetails,
		errorMsg = ( editResult && editResult.info ) || ( data && data.error && data.error.info ),
		errorCode = ( editResult && editResult.code ) || ( data && data.error && data.error.code ),
		unknown = 'Unknown error';

	if ( jqXHR && jqXHR.status !== 200 ) {
		unknown += ', HTTP status ' + data.xhr.status;
	}

	errorDetails = errorMsg || errorCode || unknown;
	this.showUnrecoverablePublishError(
		mw.msg( 'cx-publish-error-unknown', errorDetails ),
		errorDetails
	);
};

/**
 * Show publish process error message
 *
 * @method
 * @param {string|jQuery|Node[]} msg Message content (string of HTML, jQuery object or array of
 *  Node objects)
 * @param {string} [errorLog]
 * @param {boolean} [allowReapply=true] Whether or not to allow the user to reapply.
 *  Reset when swapping panels. Assumed to be true unless explicitly set to false.
 *
 * @fires publishError
 */
mw.cx.TargetArticle.prototype.showPublishError = function ( msg, errorLog, allowReapply ) {
	this.emit( 'publishError', new OO.ui.Error( msg, { recoverable: allowReapply } ) );

	if ( !errorLog ) {
		return;
	}

	mw.log.error( '[CX] Publishing failed ' + errorLog );
};

/**
 * Show publish error which doesn't allow reapply.
 *
 * @param {string|jQuery|Node[]} msg Message content (string of HTML, jQuery object or array of
 *  Node objects)
 * @param {string} [errorLog]
 */
mw.cx.TargetArticle.prototype.showUnrecoverablePublishError = function ( msg, errorLog ) {
	this.showPublishError( msg, errorLog, false );
};

/**
 * Get content for publishing
 *
 * @param {boolean} deflate Whether the content should be deflated
 * @return {string} Content for publishing, may be deflated
 */
mw.cx.TargetArticle.prototype.getContent = function ( deflate ) {
	var cleanupHtml,
		doc = this.veTarget.getSurface().getDom();

	cleanupHtml = mw.libs.ve.targetSaver.getHtml( this.constructor.static.getCleanedupContent( doc ) );

	return deflate ? mw.deflate( cleanupHtml ) : cleanupHtml;
};

/**
 * Check to see if "Publish anyway" dialog needs to be displayed, in case of
 * page with the given title already existing or translation having issues.
 *
 * @param {string} title The title to check
 * @param {boolean} hasIssues Whether the translation has issues
 * @return {jQuery.Promise}
 */
mw.cx.TargetArticle.prototype.checkForPublishAnyway = function ( title, hasIssues ) {
	// CAPTCHA check may occur as a response to the request to publish the translation.
	// If that happens, we can and should skip these checks to avoid showing
	// "Publish anyway" dialog again if the target page already exists.
	if ( this.captcha ) {
		return $.Deferred().resolve().promise();
	}

	return ve.init.platform.linkCache.get( title ).then( function ( result ) {
		var title, message,
			targetExists = !result.missing;

		if ( hasIssues && targetExists ) {
			title = mw.msg( 'cx-publishing-dialog-title' );
			message = mw.msg( 'cx-overwriting-with-issues' );
		} else if ( hasIssues && !targetExists ) {
			title = mw.msg( 'cx-publishing-with-issues-dialog-title' );
			message = mw.msg( 'cx-publishing-with-issues-dialog-message' );
		} else if ( !hasIssues && targetExists ) {
			title = mw.msg( 'cx-publishing-dialog-title' );
			message = mw.msg( 'cx-publishing-dialog-sub-title' );
		} else {
			return;
		}

		return this.showDialog( title, message );
	}.bind( this ) );
};

/**
 * Display the dialog which asks the user to "publish anyway", in spite of some problems.
 *
 * @param {string} title Title for the publishing dialog.
 * @param {string} message Main message of the publishing dialog.
 * @return {jQuery.Promise}
 */
mw.cx.TargetArticle.prototype.showDialog = function ( title, message ) {
	var windowManager = OO.ui.getWindowManager(),
		messageDialog = windowManager.getWindow( 'message' );

	return messageDialog.then( function ( win ) {
		win.message.$element.css( 'white-space', 'pre-line' );

		return windowManager.openWindow( win, {
			title: title,
			message: message,
			actions: [
				{ action: 'publish', label: mw.msg( 'cx-publishing-dialog-publish-anyway-button' ), flags: 'primary' },
				{ action: 'cancel', label: mw.msg( 'cx-draft-cancel-button-label' ), flags: 'safe' }
			]
		} ).closed.then( function ( data ) {
			if ( !data || data.action === 'cancel' ) {
				return $.Deferred().reject();
			}
		} );
	} );
};

/**
 * Get current target title from translation data model.
 * Not the translation title can be changed by translator at any point of translation.
 *
 * @return {string} target title
 */
mw.cx.TargetArticle.prototype.getTargetTitle = function () {
	return this.translation.getTargetTitle();
};

mw.cx.TargetArticle.prototype.getTargetURL = function () {
	return this.siteMapper.getPageUrl( this.targetLanguage, this.getTargetTitle() );
};

/**
 * Get the categories for the article to be published
 *
 * @param {boolean} shouldAddHighMTCategory True if high MT tracking category should be added.
 * @return {string[]}
 */
mw.cx.TargetArticle.prototype.getTargetCategories = function ( shouldAddHighMTCategory ) {
	var targetCategories, index,
		maintenanceCategoryMsg = 'cx-unreviewed-translation-category';

	targetCategories = this.translation.getTargetCategories();
	index = targetCategories.indexOf( maintenanceCategoryMsg );

	if ( shouldAddHighMTCategory ) {
		// Avoid duplicates.
		if ( index < 0 ) {
			// Note that we are adding the msg as an indicator that
			// this article need the tracking category. The prefixing
			// of appropriate namespace and category title localization
			// is done at publish api backend.
			targetCategories.push( maintenanceCategoryMsg );
		}
	} else if ( index >= 0 ) {
		// Make sure to remove if maintenanceCategoryMsg is already in targetCategories
		targetCategories.splice( index, 1 );
	}

	return targetCategories;
};

/**
 * Get the tags for the article to be published.
 * API accepts multiple values separated by '|'
 *
 * @return {string}
 */
mw.cx.TargetArticle.prototype.getTags = function () {
	var query = new mw.Uri().query,
		campainConfig = mw.config.get( 'wgContentTranslationCampaigns' );
	return OO.getProp( campainConfig, query.campaign, 'edittag' ) || '';
};
