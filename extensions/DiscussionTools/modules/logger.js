'use strict';

var trackdebug = !!mw.util.getParamValue( 'trackdebug' );

/**
 * Logs an event to http://meta.wikimedia.org/wiki/Schema:EditAttemptStep
 *
 * @instance
 * @param {Object} data
 */
module.exports = function ( data ) {
	mw.track( 'dt.schemaEditAttemptStep', data );
};

// Ensure 'ext.eventLogging' first, it provides mw.eventLog.randomTokenMatch.
// (No explicit dependency is set because we want this to just quietly not-happen
// if EventLogging isn't installed.)
mw.loader.using( 'ext.eventLogging' ).done( function () {
	var // Schema class is provided by ext.eventLogging
		Schema = mw.eventLog.Schema,
		user = mw.user,
		sampleRate = mw.config.get( 'wgDTSchemaEditAttemptStepSamplingRate' ) ||
			mw.config.get( 'wgWMESchemaEditAttemptStepSamplingRate' ),
		actionPrefixMap = {
			firstChange: 'first_change',
			saveIntent: 'save_intent',
			saveAttempt: 'save_attempt',
			saveSuccess: 'save_success',
			saveFailure: 'save_failure'
		},
		timing = {},
		session = {},
		/**
		 * Edit schema
		 * https://meta.wikimedia.org/wiki/Schema:EditAttemptStep
		 */
		/* eslint-disable camelcase */
		schemaEditAttemptStep = new Schema(
			'EditAttemptStep',
			sampleRate,
			// defaults:
			{
				page_id: mw.config.get( 'wgArticleId' ),
				revision_id: mw.config.get( 'wgRevisionId' ),
				page_title: mw.config.get( 'wgPageName' ),
				page_ns: mw.config.get( 'wgNamespaceNumber' ),
				user_id: user.getId(),
				user_class: user.isAnon() ? 'IP' : undefined,
				user_editcount: mw.config.get( 'wgUserEditCount', 0 ),
				mw_version: mw.config.get( 'wgVersion' ),
				platform: 'desktop',
				integration: 'discussiontools',
				page_token: user.getPageviewToken(),
				session_token: user.sessionId(),
				version: 1
			}
		),
		schemaVisualEditorFeatureUse = new Schema(
			'VisualEditorFeatureUse',
			sampleRate,
			// defaults:
			{
				user_id: user.getId(),
				user_editcount: mw.config.get( 'wgUserEditCount', 0 ),
				platform: 'desktop',
				integration: 'discussiontools'
			}
		);
		/* eslint-enable camelcase */

	function log() {
		// mw.log is a no-op unless resource loader is in debug mode, so
		// this allows trackdebug to work independently
		// eslint-disable-next-line no-console
		console.log.apply( console, arguments );
	}

	function computeDuration( action, event, timeStamp ) {
		// This is duplicated from the VisualEditor extension
		// (ve.init.mw.trackSubscriber.js). Changes to this should be kept in
		// sync with that file, so the data remains consistent.
		if ( event.timing !== undefined ) {
			return event.timing;
		}

		switch ( action ) {
			case 'ready':
				return timeStamp - timing.init;
			case 'loaded':
				return timeStamp - timing.init;
			case 'firstChange':
				return timeStamp - timing.ready;
			case 'saveIntent':
				return timeStamp - timing.ready;
			case 'saveAttempt':
				return timeStamp - timing.saveIntent;
			case 'saveSuccess':
			case 'saveFailure':
				// HERE BE DRAGONS: the caller must compute these themselves
				// for sensible results. Deliberately sabotage any attempts to
				// use the default by returning -1
				mw.log.warn( 'dt.schemaEditAttemptStep: Do not rely on default timing value for saveSuccess/saveFailure' );
				return -1;
			case 'abort':
				switch ( event.abort_type ) {
					case 'preinit':
						return timeStamp - timing.init;
					case 'nochange':
					case 'switchwith':
					case 'switchwithout':
					case 'switchnochange':
					case 'abandon':
						return timeStamp - timing.ready;
					case 'abandonMidsave':
						return timeStamp - timing.saveAttempt;
				}
				mw.log.warn( 'dt.schemaEditAttemptStep: Unrecognized abort type', event.type );
				return -1;
		}
		mw.log.warn( 'dt.schemaEditAttemptStep: Unrecognized action', action );
		return -1;
	}

	mw.trackSubscribe( 'dt.schemaEditAttemptStep', function ( topic, data ) {
		var actionPrefix = actionPrefixMap[ data.action ] || data.action,
			timeStamp = mw.now(),
			duration = 0;

		// Update the rolling session properties
		if ( data.action === 'init' ) {
			// eslint-disable-next-line camelcase
			session.editing_session_id = mw.user.generateRandomSessionId();
		}
		// eslint-disable-next-line camelcase
		session.editor_interface = data.editor_interface || session.editor_interface;

		// Schema's kind of a mess of special properties
		if ( data.action === 'init' || data.action === 'abort' || data.action === 'saveFailure' ) {
			data[ actionPrefix + '_type' ] = data.type;
		}
		if ( data.action === 'init' || data.action === 'abort' ) {
			data[ actionPrefix + '_mechanism' ] = data.mechanism;
		}
		if ( data.action !== 'init' ) {
			// Schema actually does have an init_timing field, but we don't want to
			// store it because it's not meaningful.
			duration = Math.round( computeDuration( data.action, data, timeStamp ) );
			data[ actionPrefix + '_timing' ] = duration;
		}
		if ( data.action === 'saveFailure' ) {
			data[ actionPrefix + '_message' ] = data.message;
		}

		// Remove renamed properties
		delete data.type;
		delete data.mechanism;
		delete data.timing;
		delete data.message;
		// eslint-disable-next-line camelcase
		data.is_oversample =
			!mw.eventLog.inSample( 1 / sampleRate );

		if ( data.action === 'abort' && data.abort_type !== 'switchnochange' ) {
			timing = {};
		} else {
			timing[ data.action ] = timeStamp;
		}

		// Switching between visual and source produces a chain of
		// abort/ready/loaded events and no init event, so suppress them for
		// consistency with desktop VE's logging.
		if ( data.abort_type === 'switchnochange' ) {
			// The initial abort, flagged as a switch
			return;
		}
		if ( timing.abort ) {
			// An abort was previously logged
			if ( data.action === 'ready' ) {
				// Just discard the ready
				return;
			}
			if ( data.action === 'loaded' ) {
				// Switch has finished; remove the abort timing so we stop discarding events.
				delete timing.abort;
				return;
			}
		}

		$.extend( data, session );

		if ( trackdebug ) {
			log( topic + '.' + data.action, duration + 'ms', data, schemaEditAttemptStep.defaults );
		} else {
			schemaEditAttemptStep.log(
				data,
				(
					mw.config.get( 'wgDTSchemaEditAttemptStepOversample' ) ||
					mw.config.get( 'wgWMESchemaEditAttemptStepOversample' )
				) ? 1 : sampleRate
			);
		}
	} );

	mw.trackSubscribe( 'dt.schemaVisualEditorFeatureUse', function ( topic, data ) {
		var event;

		// eslint-disable-next-line camelcase
		session.editor_interface = data.editor_interface || session.editor_interface;

		event = {
			feature: data.feature,
			action: data.action,
			editingSessionId: session.editing_session_id,
			// eslint-disable-next-line camelcase
			editor_interface: session.editor_interface
		};

		if ( trackdebug ) {
			log( topic, event, schemaVisualEditorFeatureUse.defaults );
		} else {
			schemaVisualEditorFeatureUse.log( event, (
				mw.config.get( 'wgDTSchemaEditAttemptStepOversample' ) ||
				mw.config.get( 'wgWMESchemaEditAttemptStepOversample' )
			) ? 1 : sampleRate );
		}

		if ( data.feature === 'editor-switch' && data.action.indexOf( 'dialog-' ) === -1 ) {
			// TODO: Account for `source-nwe-desktop` when enable2017Wikitext is set
			// eslint-disable-next-line camelcase
			session.editor_interface = session.editor_interface === 'visualeditor' ? 'wikitext' : 'visualeditor';
		}
	} );
} );
