/*jshint browser: true */
/*global mediaWiki, jQuery */
( function ( mw, $ ) {
	'use strict';

	/* if you want to use the screenshot-method only set html2canvas = true */
	var use_html2canvas;


	/**
	 * this function returns if the Extension is supported by the clients browser
	 * tested as working for:
	 * IE >= 10
	 * Firefox >= 3.6
	 * Chrome >= 20
	 * Safari >= 6
	 * Opera >= 12.12
	 *
	 * @return {boolean}
	 */
	function browserSupported() {
		var client = $.client.profile();
		var name = client.name;
		var versionArray = client.version.split( '.' );
		var versionArray_int = [];

		for ( var i in versionArray ) {
			versionArray_int.push( parseInt( versionArray[i] ) );
		}

		if ( name === 'msie' && versionArray_int[0] >= 10 ) {
			return true;
		}
		if ( name === 'chrome' && versionArray_int[0] >= 20 ) {
			return true;
		}
		if ( name === 'safari' && versionArray_int[0] >= 6 ) {
			return true;
		}
		if ( name === 'firefox' && versionArray_int[0] > 3 ) {
			return true;
		}
		if ( name === 'firefox' && versionArray_int[0] === 3 && versionArray_int[1] >= 6 ) {
			return true;
		}
		if ( name === 'opera' && versionArray_int[0] >= 12 && versionArray_int[1] >= 12 ) {
			return true;
		}

		return false;
	}

	/* if feedback method is not set before, decide randomly and store in cookie */
	if ( typeof use_html2canvas === 'undefined' ) {
		if ( $.cookie( 'ui-feedback-type' ) === null ) {
			$.cookie( 'ui-feedback-type', ( Math.random() >= 0.5 ) ? 'screenshot' : 'questionnaire', {
				path: '/',
				expires: 21
			} );
		}
		use_html2canvas = $.cookie( 'ui-feedback-type' ) === 'screenshot';
		mw.log( 'cookie: ' + $.cookie( 'ui-feedback-type' ) );
	}
	mw.log( 'use_html2canvas: ' + use_html2canvas );

	/* TODO remove: JUST FOR TESTING */
	if ( window.location.hash === '#screenshot' ) {
		use_html2canvas = true;
	} else if ( window.location.hash === '#questionnaire' ) {
		use_html2canvas = false;
	}

	/* the feedback button */
	var button = document.createElement( 'div' );
	if ( use_html2canvas ) {
		button.className = 'feedback-button screenshot-button';
		button.innerHTML = '<span class="icon-camera"></span>&nbsp;' + mw.message( 'ui-feedback-headline' ).escaped() + '&nbsp;';
	} else {
		button.className = 'feedback-button questionnaire-button';
		button.innerHTML = '<span class="icon-edit"></span>&nbsp;' + mw.message( 'ui-feedback-headline' ).escaped() + '&nbsp;';
	}
	var feedbackform;
	var screenshotform;
	var pre_render_dialogue;

	/* the questionnaire-form */
	if ( !use_html2canvas ) {
		feedbackform = document.createElement( 'div' );
		feedbackform.innerHTML = '<div class="ui-feedback noselect green">' +

			'<div class="ui-feedback-head">' + '<div class="ui-feedback-help-button"></div>' + '<h2 class="h_green">' + mw.message( 'ui-feedback-headline' ).escaped() + '</h2>' + '<div class="ui-feedback-close"></div>' + '</div>' + // end head

			'<form id="ui-feedback-form" method="post" action="" enctype="multipart/form-data">' + '<ul>' +

			/* i wanted to */
			'<li id="ui-feedback-task-li">' + '<label class="headline">' + mw.message( 'ui-feedback-task-label' ).escaped() + '</label>' + '<select name="ui-feedback-task" id="ui-feedback-task">' + ' <option value="">' + mw.message( 'ui-feedback-task-0' ).escaped() + '</option>' + ' <option value="add/edit a item">' + mw.message( 'ui-feedback-task-1' ).escaped() + '</option>' + ' <option value="add/edit a label">' + mw.message( 'ui-feedback-task-2' ).escaped() + '</option>' + ' <option value="add/edit a description">' + mw.message( 'ui-feedback-task-3' ).escaped() + '</option>' + ' <option value="add/edit a alias">' + mw.message( 'ui-feedback-task-4' ).escaped() + '</option>' + ' <option value="add/edit a links">' + mw.message( 'ui-feedback-task-5' ).escaped() + '</option>' + ' <option value="search">' + mw.message( 'ui-feedback-task-6' ).escaped() + '</option>' + ' <option value="other">' + mw.message( 'ui-feedback-task-7' ).escaped() + '</option>' + '</select>' + '</li>' +

			/* what happened */
			'<li>' + '<label class="headline" for="ui-feedback-happened">' + mw.message( 'ui-feedback-happened-label' ).escaped() + '</label>' + '<label><input type="radio" name="ui-feedback-happened" id="ui-feedback-happened" value="1" >' + mw.message( 'ui-feedback-happened-1' ).escaped() + '</label><br>' + '<label><input type="radio" name="ui-feedback-happened" value="2" >' + mw.message( 'ui-feedback-happened-2' ).escaped() + '</label><br>' + '<label><input type="radio" name="ui-feedback-happened" value="3" >' + mw.message( 'ui-feedback-happened-3' ).escaped() + '</label><br>' + '<label><input type="radio" name="ui-feedback-happened" value="4" >' + mw.message( 'ui-feedback-happened-4' ).escaped() + '</label><br>' + '</li>' +

			/* details */
			'<li>' + '<label class="headline" for="ui-feedback-text1">' + mw.message( 'ui-feedback-comment-label' ).escaped() + '<br>' + '<textarea class="ui-feedback-textarea" id="ui-feedback-text1" name="ui-feedback-text1" rows="3" cols="8"></textarea></label>' + '</li>' +

			/* done */
			'<li>' + '<label class="headline" for="ui-feedback-done">' + mw.message( 'ui-feedback-done-label' ).escaped() + '</label>' + '<label><input type="radio" name="ui-feedback-done" id="ui-feedback-done" value="1" >' + mw.message( 'ui-feedback-yes' ).escaped() + '</label>' + '<label><input type="radio" name="ui-feedback-done" value="0" >' + mw.message( 'ui-feedback-no' ).escaped() + '</label>' + '</li>' +

			/* importance */
			'<li>' + '<label class="headline">' + mw.message( 'ui-feedback-importance-label' ).escaped() + '</label>' + '<label style="margin-left:10px;"><small>' + mw.message( 'ui-feedback-importance-1' ).escaped() + '</small><input type="radio" name="ui-feedback-importance" value="1" >' + '</label>' + '<label><input type="radio" name="ui-feedback-importance" value="2" >' + '</label>' + '<label><input type="radio" name="ui-feedback-importance" value="3" >' + '</label>' + '<label><input type="radio" name="ui-feedback-importance" value="4" >' + '</label>' + '<label><input type="radio" name="ui-feedback-importance" value="5" ><small>' + mw.message( 'ui-feedback-importance-5' ).escaped() + '</small></label>' + '</li>' +

			'<li><hr id="ui-feedback-hr"></li>' +

			'<li id="ui-feedback-anonymous-scr-li">' + '<input type="checkbox" id="ui-feedback-anonymous" name="ui-feedback-anonymous" value="true"><span id="uif-tooltip-anonym" class="uif-tooltip">' + mw.message( 'ui-feedback-anonym-help' ).escaped() + '</span>' + '<label for="ui-feedback-anonymous">' + mw.message( 'ui-feedback-anonym-label' ).escaped() + '</label>' + '<div id="ui-feedback-help-anonym" class="ui-feedback-help-icon-questionnaire"></div>' + '</li>' +

			'<li id="ui-feedback-notify-li">' + '<input type="checkbox" id="ui-feedback-notify" name="ui-feedback-notify" value="true"><span id="uif-tooltip-notify" class="uif-tooltip">' + mw.message( 'ui-feedback-notify-help' ).escaped() + '</span>' + '<label for="ui-feedback-notify">' + mw.message( 'ui-feedback-notify-label' ).escaped() + '</label>' + '<div id="ui-feedback-help-notify" class="ui-feedback-help-icon-questionnaire"></div>' + '</li>' +

			'</ul>' +

			'<div id="ui-feedback-action-buttons" >' + '<input type="hidden" name="ui-feedback-username" value="' + mw.config.get( 'wgUserName' ) + '">' + '<input type="hidden" name="ui-feedback-useragent" value="' + navigator.userAgent + '">' + '<input type="hidden" name="ui-feedback-type" value="0" >' + '<input type="hidden" name="ui-feedback-url" value="' + mw.html.escape( document.URL ) + '" >' + '<input type="button" name="reset" id="ui-feedback-reset" value="' + mw.message( 'ui-feedback-problem-reset' ).escaped() + '" >' + '&nbsp;<input type="button" name="send" id="ui-feedback-send" value="' + mw.message( 'ui-feedback-problem-send' ).escaped() + '" >' + '</div>' + '</form>' + '</div>'; //end questionnaire-form
	} else {
		/* the 'screenshot' form */
		screenshotform = document.createElement( 'div' );
		screenshotform.innerHTML = '<div class="ui-feedback noselect purple">' + '<div class="ui-feedback-head">' + '<div class="ui-feedback-help-button"></div>' + '<h2 class="h_purple">' + mw.message( 'ui-feedback-scr-headline' ).escaped() + '</h2>' + '<div class="ui-feedback-collapse"></div>' + '<div class="ui-feedback-expand"></div>' + '<div class="ui-feedback-close"></div>' + '</div>' + '<form id="ui-feedback-form" method="post" action="' + mw.util.getUrl( 'Special:UiFeedback' ) + '" target="ui-feedback-iframe" enctype="multipart/form-data">' + '<ul>' +

			/* i wanted to */
			'<li id="ui-feedback-task-li">' + '<label class="headline">' + mw.message( 'ui-feedback-task-label' ).escaped() + '</label>' + '<select name="ui-feedback-task" id="ui-feedback-task">' + ' <option value="">' + mw.message( 'ui-feedback-task-0' ).escaped() + '</option>' + ' <option value="add/edit item">' + mw.message( 'ui-feedback-task-1' ).escaped() + '</option>' + ' <option value="add/edit label">' + mw.message( 'ui-feedback-task-2' ).escaped() + '</option>' + ' <option value="add/edit description">' + mw.message( 'ui-feedback-task-3' ).escaped() + '</option>' + ' <option value="add/edit alias">' + mw.message( 'ui-feedback-task-4' ).escaped() + '</option>' + ' <option value="add/edit links">' + mw.message( 'ui-feedback-task-5' ).escaped() + '</option>' + ' <option value="search">' + mw.message( 'ui-feedback-task-6' ).escaped() + '</option>' + ' <option value="other">' + mw.message( 'ui-feedback-task-7' ).escaped() + '</option>' + '</select>' + '</li>' +

			/* highlight/blackout */
			'<li>' + '<label class="headline highlight_headline_label">' + mw.message( 'ui-feedback-highlight-label' ).escaped() + '</label>' +
				'<label id="ui-feedback-highlight-label"><input type="radio" id="ui-feedback-highlight-checkbox" name="marker" value="rgba(225,255,0,0.25)" checked><div class="highlight-button"></div> ' + mw.message( 'ui-feedback-yellow' ).escaped() + '</label>' +
				'<label id="ui-feedback-blackout-label"><input type="radio" id="ui-feedback-blackout-checkbox" name="marker" value="#000"><div class="blackout-button"></div> ' + mw.message( 'ui-feedback-black' ).escaped() + '</label>' +
				'<label id="ui-feedback-sticky-label"><input type="radio" id="ui-feedback-sticky-checkbox" name="marker" value="sticky"><div class="sticky-button"></div> ' + mw.message( 'ui-feedback-sticky' ).escaped() + '</label>' +

			'</li>' +

			/* comment */
			// '<li>' + '<label class="headline" for="ui-feedback-text3">' + mw.message( 'ui-feedback-comment-label' ).escaped() + '<br>' + '<textarea class="ui-feedback-textarea" id="ui-feedback-text3" name="ui-feedback-text3" rows="3" cols="8"></textarea></label>' + '</li>' +

			/* done? */
			'<li>' + '<label class="headline" for="ui-feedback-done">' + mw.message( 'ui-feedback-done-label' ).escaped() + '</label>' + '<label><input type="radio" name="ui-feedback-done" id="ui-feedback-done" value="1" >' + mw.message( 'ui-feedback-yes' ).escaped() + '</label>' + '<label><input type="radio" name="ui-feedback-done" value="0" >' + mw.message( 'ui-feedback-no' ).escaped() + '</label>' + '</li>' +

			/* importance */
			'<li>' + '<label class="headline">' + mw.message( 'ui-feedback-importance-label' ).escaped() + '</label>' + '<label style="margin-left:10px;"><small>' + mw.message( 'ui-feedback-importance-1' ).escaped() + '</small><input type="radio" name="ui-feedback-importance" value="1" >' + '</label>' + '<label><input type="radio" name="ui-feedback-importance" value="2" >' + '</label>' + '<label><input type="radio" name="ui-feedback-importance" value="3" >' + '</label>' + '<label><input type="radio" name="ui-feedback-importance" value="4" >' + '</label>' + '<label><input type="radio" name="ui-feedback-importance" value="5" ><small>' + mw.message( 'ui-feedback-importance-5' ).escaped() + '</small></label>' + '</li>' +


			'<li><hr id="ui-feedback-hr"></li>' +

			'<li id="ui-feedback-anonymous-scr-li">' + '<input type="checkbox" id="ui-feedback-anonymous-scr" name="ui-feedback-anonymous-scr" value="true"><span id="uif-tooltip-anonym" class="uif-tooltip">' + mw.message( 'ui-feedback-anonym-help' ).escaped() + '</span>' + '<label for="ui-feedback-anonymous-scr">' + mw.message( 'ui-feedback-anonym-label' ).escaped() + '</label>' + '<div id="ui-feedback-help-anonym" class="ui-feedback-help-icon-screenshot"></div>' + '</li>' +

			'<li id="ui-feedback-notify-li">' + '<input type="checkbox" id="ui-feedback-notify" name="ui-feedback-notify" value="true"><span id="uif-tooltip-notify" class="uif-tooltip">' + mw.message( 'ui-feedback-notify-help' ).escaped() + '</span>' + '<label for="ui-feedback-notify">' + mw.message( 'ui-feedback-notify-label' ).escaped() + '</label>' + '<div id="ui-feedback-help-notify" class="ui-feedback-help-icon-screenshot"></div>' + '</li>' +

			'</ul>' +


			'<div id="ui-feedback-action-buttons" >' + '<input type="hidden" id="ui-feedback-username-scr" name="ui-feedback-username" value="' + mw.config.get( 'wgUserName' ) + '">' + '<input type="hidden" name="ui-feedback-useragent" value="' + navigator.userAgent + '">' + '<input type="hidden" name="ui-feedback-url" value="' + document.URL + '" >' + '<input type="button" name="reset" id="ui-feedback-reset" value="' + mw.message( 'ui-feedback-problem-reset' ).escaped() + '" >' + '&nbsp;<input type="button" name="send" id="ui-feedback-send_html2canvas" value="' + mw.message( 'ui-feedback-problem-send' ).escaped() + '" >' + '</div>' +

			'</form>' + '</div>'; // end screenshot-form

		/* modal confirmation dialoge before rendering the screenshot */
		pre_render_dialogue = document.createElement( 'div' );
		$( pre_render_dialogue ).addClass( 'ui-feedback-overlay' );
		pre_render_dialogue.innerHTML = '<div class="ui-feedback-modal-dialogue grey">' + '<div class="title">' + '<h3 class="h_purple">' + mw.message( 'ui-feedback-prerender-headline' ).escaped() + '</h3>' + '<div class="ui-feedback-modal-close"></div>' + '</div>' + '<div class = "text">' + mw.message( 'ui-feedback-prerender-text1' ).escaped() + '<br><br>' + '<div style="text-align:center;"><b>' + mw.message( 'ui-feedback-prerender-text2' ).escaped() + '</b></div>' + '</div>' + '<div class="footer">&nbsp;' + '<button type="button" class="cancel left">' + mw.message( 'ui-feedback-problem-cancel' ).escaped() + '</button>' + '<button type="button" class="send right">' + mw.message( 'ui-feedback-problem-send' ).escaped() + '</button>' + '</div>' + '</div>'; // end modal pre-render-dialogue
	}

	/* function to show/hide the pre-render-dialogue */
	function toggleModalDialogue() {
		$( '.ui-feedback-overlay' ).toggle();
	}

	/* add click handlers and make the forms movable */
	function init_ui_feedback() {
		var form = feedbackform;
		$( form ).remove();
		if ( use_html2canvas ) {
			form = screenshotform;
//			$( button ).css( 'height', '119px' );
//			$( button ).css( 'background-position', '-70px 0px' );
//			$( button ).hover(
//
//				function () {
//					$( button ).css( 'background-position', '-105px 0px' );
//				},
//
//				function () {
//					$( button ).css( 'background-position', '-70px 0px' );
//				} );
			$( form ).find( '.ui-feedback-collapse' ).click( collapseForm );
			$( form ).find( '.ui-feedback-expand' ).click( expandForm );
			$( form ).find( '.ui-feedback-expand' ).hide();

		}
		/* add the textbox for other tasks */
		var dropdown = $( form ).find( '#ui-feedback-task' );
		var other_text_box = $( '<li id="ui-feedback-task-other"><input type="text" name="ui-feedback-task-other"></li>' );
		var inserted = false;
		dropdown.change( function () {
			/* add other-textbox when other is selected */
			if ( !inserted && $( '#ui-feedback-task option:selected' ).text() === mw.message( 'ui-feedback-task-7' ).escaped() ) {
				mw.log( 'other' );
				$( other_text_box ).css( 'width', $( dropdown ).css( 'width' ) );
				$( other_text_box ).insertAfter( $( '#ui-feedback-task-li' ) );
				$( other_text_box ).focus();
				inserted = true;
			} else {
				$( other_text_box ).remove();
				inserted = false;
			}
		} );

		/* anonymous and notify, only one of them should be checked */
		$( form ).find( '#ui-feedback-anonymous' ).change(

			function () {
				$( '#ui-feedback-notify' ).prop( 'disabled', !$( '#ui-feedback-notify' ).prop( 'disabled' ) );
			} );
		$( form ).find( '#ui-feedback-anonymous-scr' ).change(

			function () {
				$( '#ui-feedback-notify' ).prop( 'disabled', !$( '#ui-feedback-notify' ).prop( 'disabled' ) );
			} );
		$( form ).find( '#ui-feedback-notify' ).change(

			function () {
				$( '#ui-feedback-anonymous' ).prop( 'disabled', !$( '#ui-feedback-anonymous' ).prop( 'disabled' ) );
				$( '#ui-feedback-anonymous-scr' ).prop( 'disabled', !$( '#ui-feedback-anonymous-scr' ).prop( 'disabled' ) );
			} );

		/* append forms to body and register click-handlers */
		$( 'body' ).append( form );
		$( form ).find( '.ui-feedback-close' ).click( toggleForm );
		$( form ).find( '.ui-feedback-help-button' ).click( show_help );
		$( form ).find( '#ui-feedback-close' ).click( toggleForm );
		$( form ).find( '#ui-feedback-reset' ).click( resetForm );
		$( form ).find( '#ui-feedback-send' ).click( sendFeedback );
		$( form ).find( '.ui-feedback' ).draggable().draggable( 'option', 'opacity', 0.66 ).draggable( { cancel: '#ui-feedback-form' } );
		$( button ).click( toggleForm );
		$( '.ui-feedback' ).toggle();

		/* tooltip */
		$( '#ui-feedback-help-anonym' ).hover( function () {
			toggleTooltip( '#uif-tooltip-anonym' );
		} );
		$( '#ui-feedback-help-notify' ).hover( function () {
			toggleTooltip( '#uif-tooltip-notify' );
		} );
		$( '#ui-feedback-help-anonym' ).click( function () {
			toggleTooltip( '#uif-tooltip-anonym' );
		} );

//		$( '.ui-feedback-help-button' ).hover( function () { toggleTooltip( '#uif-tooltip-help' ); } );
//		$( '.ui-feedback-close' ).hover( function () { toggleTooltip( '#uif-tooltip-close' ); } );

		/* append pre-render dialogue to body */
		if ( use_html2canvas ) {
			$( 'body' ).append( pre_render_dialogue );
			$( '.ui-feedback-modal-dialogue' ).find( '.ui-feedback-modal-close' ).click( toggleModalDialogue );
			$( '.ui-feedback-modal-dialogue' ).find( '.cancel' ).click( toggleModalDialogue );
			$( '.ui-feedback-modal-dialogue' ).draggable();
			$( '.ui-feedback-modal-dialogue' ).draggable( 'option', 'cancel', '.text, .footer' );
			$( '.ui-feedback-modal-dialogue' ).draggable( {
				revert: true
			} );
			$( '.ui-feedback-overlay' ).toggle();
		}
	}

	function toggleTooltip( id ) {
		mw.log( 'toggleTooltip' );
		if ( $( id ).is( ':visible' ) ) {
			$( id ).css( 'display', 'none' );
		} else {
			$( id ).css( 'display', 'block' );
		}
	}

	/* function to show/hide the form */
	function toggleForm( event ) {
		var api = new mw.Api();

		if ( use_html2canvas ) {
			if ( $( '.ui-feedback' ).css( 'display' ) === 'none' ) {
				/* show */
				$( 'body' ).css( {
					'-webkit-touch-callout': 'none',
					'user-select': 'none'
				} );
			} else {
				/* hide */
				$( 'body' ).css( {
					'-webkit-touch-callout': 'initial',
					'user-select': 'initial'
				} );
			}
		}

		mw.log( 'toggle' );

		/* for the stats */
		try {
			/* count the clicks on the button */
			if ( event.target.className === 'ui-feedback-button' ) {
				// count the number of requests shown ) type 0 dynamic request (popup), 1 questionnaire-button, 2 screenshot-button
				if ( use_html2canvas ) {
					api.post( {
						action: 'uifeedback',
						mode: 'count',
						type: '2',
						click: '1'
					} );
				} else {
					api.post( {
						action: 'uifeedback',
						mode: 'count',
						type: '1',
						click: '1'
					} );
				}
			}
		} catch( e ) {
			mw.log( 'no target for this event' );
		}

		/* toggle */
		$( '.ui-feedback' ).fadeToggle( 'fast' );
		$( button ).fadeToggle();
		$( '.ui-feedback' ).css( 'position', 'absolute' );
		mw.log( $( '.ui-feedback' ).css( 'position' ) );
		$( '.ui-feedback' ).animate( {
			top: $( window ).scrollTop() + window.innerHeight / 10
		}, 500 );

		/* if html2canvas is used hide the markes */
		if ( use_html2canvas ) {

			$( 'body' ).htmlfeedback( 'toggle' );
			$( 'body' ).htmlfeedback( 'color', $( 'input[name=marker]:checked' ).val() );

			$( '#ui-feedback-anonymous-scr' ).prop( 'checked', false );

			$( '#ui-feedback-anonymous-scr' ).change( function () {
				mw.log( 'click' );
				if ( $( '#p-personal' ).is( ':visible' ) ) {
					$( '#p-personal' ).hide();
					$( '#ui-feedback-username' ).attr( 'value', 'anonymous' );
				} else {
					$( '#p-personal' ).show();
					$( '#ui-feedback-username' ).attr( 'value', mw.config.get( 'wgUserName' ) );
				}
			} );

		}
		/* close all help and notify-windows */
		$( '.ui-feedback-help' ).remove();
		$( '.ui-feedback-notification' ).remove();

	}

	/**
	 * sends the Questionnaire-form to the server
	 * @param e
	 */
	function sendFeedback( e ) {
		mw.log( $( 'input[name=ui-feedback-happened]:checked' ).val() );
		var api = new mw.Api();
		api.post( {
			action: 'uifeedback',
			mode: 'feedback',
			'ui-feedback-type': 0,
			'ui-feedback-url': $( 'input[name=ui-feedback-url]' ).val(),
			'ui-feedback-username': mw.config.get( 'wgUserName' ),
			'ui-feedback-anonymous': document.getElementById( 'ui-feedback-anonymous' ).checked,
			'ui-feedback-notify': document.getElementById( 'ui-feedback-notify' ).checked,
			'ui-feedback-useragent': navigator.userAgent,
			'ui-feedback-text1': $( '#ui-feedback-text1' ).val(),
			'ui-feedback-happened': $( 'input[name=ui-feedback-happened]:checked' ).val(),
			'ui-feedback-task': $( 'select[name=ui-feedback-task]' ).find( ':selected' ).val(),
			'ui-feedback-task-other': $( 'input[name=ui-feedback-task-other]' ).val(),
			'ui-feedback-done': $( 'input[name=ui-feedback-done]:checked' ).val(),
			'ui-feedback-importance': $( 'input[name=ui-feedback-importance]:checked' ).val()

		} ).done( function ( data ) {
			mw.log( 'API result:', data );
		} ).fail( function ( error ) {
			mw.log( 'API failed :(', error );
		} );

		resetForm();
		toggleForm();
		show_notification( mw.message( 'ui-feedback-notify-sent', mw.util.getUrl( 'Special:UiFeedback' ) ), 5000, 'green' );
	}

	/**
	 * Collapses the Form to the title-bar (only used in screenshot-form)
	 * @param e
	 */
	function collapseForm( e ) {
		$( '#ui-feedback-form' ).slideToggle();
		$( '.ui-feedback-collapse' ).hide();
		$( '.ui-feedback-expand' ).show();
	}

	/**
	 * expands the Form
	 * @param e
	 */
	function expandForm( e ) {
		$( '#ui-feedback-form' ).slideToggle();
		$( '.ui-feedback-collapse' ).show();
		$( '.ui-feedback-expand' ).hide();

	}

	/* resets all not hidden fields of the feedback-forms */
	function resetForm() {
		mw.log( 'resetForm' );
		var form = '#ui-feedback-form';
		$( ':input', $( form ) ).each( function ( i, item ) {
			switch ( item.tagName.toLowerCase() ) {
				case 'input':
					switch ( item.type.toLowerCase() ) {
						case 'text':
							item.value = '';
							break;
						case 'radio':
						case 'checkbox':
							item.checked = '';
							break;
					}
					break;
				case 'select':
					item.selectedIndex = 0;
					break;
				case 'textarea':
					item.value = '';
					break;
			}
		} );


		$( 'body' ).htmlfeedback( 'sticky', 'false' );
		$( '#ui-feedback-highlight-checkbox' ).prop( 'checked', true );
		$( '#ui-feedback-notify' ).prop( 'disabled', false );
		$( '#ui-feedback-anonymous' ).prop( 'disabled', false );
		$( '#ui-feedback-anonymous-scr' ).prop( 'disabled', false );

	}

	/* shows a notification with given color until timeout */
	function show_notification( message, timeout, color, offset_top ) {
		$( '.ui-feedback-notification' ).remove();

		if ( offset_top === undefined ) {
			offset_top = '37%';
		}
		/* the notification-window */
		var notification = document.createElement( 'div' );
		notification.setAttribute( 'class', 'ui-feedback-notification ' + color );
		notification.innerHTML = '&nbsp;';

		$( notification ).css( 'top', offset_top );
		notification.innerHTML = message;

		$( notification ).click( function () {
			$( '.ui-feedback-notification' ).animate( {
				'right': '-500px'
			}, 500 );
		} );

		$( 'body' ).append( notification );
		$( '.ui-feedback-notification' ).animate( {
			'right': '+=50px'
		}, 500 );
	}

	/* The Help-Dialogue */
	var help = document.createElement( 'div' );
	help.setAttribute( 'class', 'ui-feedback-help grey noselect' );
	help.innerHTML = '<div class="title">' + '<h3 class="h_green">' + mw.message( 'ui-feedback-help-headline' ).escaped() + '</h3>' + '<div class="ui-feedback-close"></div>' + '</div>' + '<div id="help-content"></div>';
	/* Text for both help-dialogues */
	var helpcontent = '<div class="text">' + mw.message( 'ui-feedback-help-text-top' ).escaped() + '</div>';
	if ( use_html2canvas ) {
		$( help ).find( '.h_green' ).removeClass( 'h_green' ).addClass( 'h_purple' );
		helpcontent += '<div class="title sub">' + '<h3 class="h_purple">' + mw.message( 'ui-feedback-help-subheading' ).escaped() + '</h3>' + '</div>' + '<div class="text">' + mw.message( 'ui-feedback-help-text-bottom' ).escaped() + '<div class="image"></div>' + '</div>';
	}
	$( help ).find( '#help-content' ).append( helpcontent ); // close help-content

	/**
	 * shows the Help-Dialogue
	 */
	function show_help() {
		$( 'body' ).prepend( help );
		$( help ).fadeIn();
		var left = $( '.ui-feedback' ).offset().left;
		var top = $( '.ui-feedback' ).css( 'top' );
		$( help ).css( 'left', left - 290 );
		$( help ).css( 'top', top );
		$( help ).find( '.ui-feedback-close' ).click( function () {
			$( help ).fadeOut();
		} );
		$( help ).draggable().draggable( 'option', 'opacity', 0.66 );
		$( help ).draggable( 'option', 'cancel', '#help-content' );
	}

	/* this function sends the review of a feedback-item to the api */
	function send_uifeedback_review() {
		mw.log( 'send review' );
		var api = new mw.Api();
		api.post( {
			action: 'uifeedback',
			mode: 'review',
			id: $( 'input[name=id]' ).val(),
			status: $( 'input[name=status]:checked' ).val(),
			comment: $( 'textarea[name=comment]' ).val()
		} ).done( function ( data ) {
			/* reload the page */
			window.location.href = window.location.href;
			// window.location = mw.util.getUrl( 'Special:UiFeedback' );
		} ).fail( function ( error ) {
			mw.log( 'API failed :(', error );
		} );
	}

	/* this function sets the filter for the specialpage */
	function set_uifeedback_filter() {
		var uri;
		var filter_status = [];
		var filter_importance = [];
		var filter_type = [];

		/* add the filter values to the query string */
		$( 'input[name=filter_status]:checked' ).each( function () {
			filter_status.push( $( this ).val() );
		} );
		$( 'input[name=filter_importance]:checked' ).each( function () {
			filter_importance.push( $( this ).val() );
		} );
		$( 'input[name=filter_type]:checked' ).each( function () {
			filter_type.push( $( this ).val() );
		} );
		/* create a url */
		uri = mw.util.getUrl( 'Special:UiFeedback', {
			'filter_status': filter_status,
			'filter_importance': filter_importance,
			'filter_type': filter_type
		} );

		window.location.href = uri;
	}

	function addStickyNote( e ) {
		mw.log( 'fooooo' );
		$( '.ui-feedback-sticky-note' ).each(
			function () {
				if ( $( this ).find( 'textarea' ).val() === '' ) {
					if ( $( this ) !== null ) {
						$( this ).remove();
					}
				}
			}
		);

		var note = $( '<div class="ui-feedback-sticky-note"><div class="ui-feedback-sticky-close"></div><textarea placeholder=""></textarea></div>' );
		$( note ).css( 'left', e.pageX - 50 );
		$( note ).css( 'top', e.pageY - 25 );

		$( this ).append( note );
		$( note ).draggable();
		$( note ).mousedown( function () {
			$( 'body' ).htmlfeedback( 'sticky', 'true' );
		} );
		$( note ).mouseup( function () {
			if ( $( 'input[name=marker]:checked' ).val() !== 'sticky' ) {
				$( 'body' ).htmlfeedback( 'sticky', 'false' );
			}
		} );

		$( note ).find( 'textarea' ).focus();
		$( note ).click( function ( e ) {
			return false;
		} );
		$( note ).hover(
			function () {
				$( note ).find( '.ui-feedback-sticky-close' ).toggle();
			}
		);

		$( note ).find( '.ui-feedback-sticky-close' ).click(
			function () {
				$( note ).remove();
				return false;
			}
		);

	}


	$( function () {
		var api = new mw.Api();

		/* if on the specialpage add the functions to the button */
		$( '#ui-feedback-send-review-button' ).click( send_uifeedback_review );
		$( '#ui-feedback-set-filter-button' ).click( set_uifeedback_filter );

		if ( !browserSupported() ) {
			mw.log( 'UIFeedback is not working properly in your browser so it\'s deactivated ' );
		} else {

			/* insert the feedback button */
			$( 'body' ).prepend( button );
			// type: 0 dynamic, 1 static

			/* count for statistics */
			if ( use_html2canvas ) {
				api.post( {
					action: 'uifeedback',
					mode: 'count',
					type: '2',
					show: '1'
				} );
			} else {
				api.post( {
					action: 'uifeedback',
					mode: 'count',
					type: '1',
					show: '1'
				} );
			}

			/* insert the form */
			init_ui_feedback();

			/* WikiData post-edit */
			try {
				$( wb ).on( 'stopItemPageEditMode', function ( a, origin ) {
					var offset_top = '37%';
					try {
						offset_top = $( origin.__toolbarParent[0] ).offset().top;
					} catch( e ) {
						mw.log( 'cant get parent-object' );
						mw.log( origin );
						offset_top = '37%';
					}
					var color = 'green';
					if ( use_html2canvas ) {
						color = 'purple';
					}
					mw.log( '' + origin.API_VALUE_KEY );
					mw.log( offset_top );
					if ( $.cookie( 'ui-feedback-show-postedit' ) !== 'false' ) {

						show_notification( mw.message( 'ui-feedback-notify-postedit' ), 5000, color, offset_top );

						$.data( this, 'timer', setTimeout( function () {
							$( '.ui-feedback-notification' ).animate( {
								'right': '-500px'
							}, 500 );
						}, 5000 ) );

						$( '#ui-feedback-show-postedit' ).click( function ( e ) {
							$.cookie( 'ui-feedback-show-postedit', 'false', {
								path: '/',
								expires: 21
							} );
							mw.log( 'click' );
							$( '.ui-feedback-notification' ).animate( {
								'right': '-500px'
							}, 500 );
						} );
					} else {
						mw.log( 'C is for cookie, and cookie is for me! (Cookiemonster)' );
					}

					// count the number of requests shown ) type 0 dynamic request (popup), 1 questionnaire-button, 2 screenshot-button
					api.post( {
						action: 'uifeedback',
						mode: 'count',
						type: '0',
						show: '1'
					} );

				} );
			} catch( e ) {
				mw.log( 'wikibase not found' );
			}

			/* HTMLFeedback */
			$( 'body' ).htmlfeedback( {
				onShow: function () {
					$( '#htmlfeedback-close' ).show();
					$( '.markers' ).css( 'cursor', 'crosshair' );
					$( '.ui-feedback' ).css( 'cursor', 'auto' );
					// $( '#p-personal' ).hide();
					$( 'body' ).addClass( 'noselect' );
				},
				onHide: function () {
					$( '#htmlfeedback-close' ).hide();
					$( '.markers' ).css( 'cursor', 'auto' );
					$( '#p-personal' ).show();
					$( 'body' ).addClass( 'noselect' );

				},
				onPreRender: function () {
					// alert( "A screenshot will now be rendered and uploaded to the server. That could take some time. Please don't close the browser." );
					// $( '#p-personal' ).hide();
					$( '.ui-feedback' ).hide();
					$( '.ui-feedback-help' ).remove();
					$( '.markers' ).css( 'cursor', 'wait' );

				},
				onPostRender: function ( canvas ) {

					if ( canvas.toBlob ) {
						canvas.toBlob( function ( canvasBytes ) {
							var api = new mw.Api();
							/* sending the feedback form */
							api.post( {
								action: 'uifeedback',
								mode: 'feedback',
								'ui-feedback-type': 1,
								'ui-feedback-url': $( 'input[name=ui-feedback-url]' ).val(),
								'ui-feedback-username': mw.config.get( 'wgUserName' ),
								'ui-feedback-anonymous': document.getElementById( 'ui-feedback-anonymous-scr' ).checked,
								'ui-feedback-notify': document.getElementById( 'ui-feedback-notify' ).checked,
								'ui-feedback-useragent': navigator.userAgent,
								'ui-feedback-text1': $( '#ui-feedback-text3' ).val(),
								'ui-feedback-task': $( 'select[name=ui-feedback-task]' ).find( ':selected' ).val(),
								'ui-feedback-task-other': $( 'input[name=ui-feedback-task-other]' ).val(),
								'ui-feedback-done': $( 'input[name=ui-feedback-done]:checked' ).val(),
								'ui-feedback-importance': $( 'input[name=ui-feedback-importance]:checked' ).val()
							} ).done( function ( data ) { /* if the textfeedback was sent */
								$( '.markers' ).css( 'cursor', 'auto' );
								/* sending the screenshot to the upload-api */
								var filename = 'UIFeedback_screenshot_' + data.uifeedback.id + '.png';
								mw.log( 'API result:', data );
								/* sending the image to the upload api */
								/* thanks to danwe_wmde for pointing me at the upload-wizard extension */

								api.postWithToken( 'csrf',
								{
									action: 'upload',
									format: 'json',
									filename: filename,
									file: canvasBytes
								}, {
									contentType: 'multipart/form-data'
								} )
								.done( function () {
									show_notification( mw.message( 'ui-feedback-notify-upload-sent', mw.util.getUrl( 'Special:UiFeedback' ) ), 5000, 'purple' );
									resetForm();

								} )
								.fail( function () {
									show_notification( mw.message( 'ui-feedback-notify-upload-fail' ), 5000, 'purple' );
								} );
								show_notification( mw.message( 'ui-feedback-notify-upload-progress' ), 5000, 'purple' );
							} ).fail( function ( error ) {
								mw.log( 'API failed :(', error );
							} );
						}, 'image/png' );
					} else {
						mw.log( 'no canvas.toBlob available' );
					}

					mw.log( 'postrender' );
					$( '.ui-feedback' ).show();
					toggleForm();
					$( '#p-personal' ).show();
					$( 'body' ).htmlfeedback( 'toggle' );
					$( 'canvas' ).css( 'width', '0px' ).css( 'height', '0px' );
					$( '.markers' ).hide();
					$( 'body' ).htmlfeedback( 'sticky', 'false' );
					$( '.ui-feedback-sticky-note' ).remove();
					$( '.htmlfeedback-rect' ).remove();

					$( 'body' ).addClass( 'noselect' );
					mw.log( 'postrender done' );
				}
			} );

			// Show or hide HTMLFeedback
			$( '#ui-feedback-close' ).click( function () {
				$( 'body' ).htmlfeedback( 'toggle' );
				$( 'canvas' ).hide();
				$( '.markers' ).hide();
			} );

			$( '#ui-feedback-close' ).click( function () {
				$( 'body' ).htmlfeedback( 'toggle' );
				$( '#p-personal' ).show();
				$( 'canvas' ).hide();
				$( '.markers' ).hide();
				$( '.markers' ).css( 'cursor', 'auto' );
			} );

			// Reset HTMLFeedback when we reset the form
			$( '#ui-feedback-reset' ).click( function () {
				$( '.htmlfeedback-rect' ).remove();
				$( '.ui-feedback-sticky-note' ).remove();
			} );

			// Upload sreenshot and comment to the server
			$( '#ui-feedback-send_html2canvas' ).click( toggleModalDialogue );
			$( '.ui-feedback-modal-dialogue' ).find( '.send' ).click( function ( e ) {
				toggleModalDialogue();
				e.preventDefault();

				$( 'body' ).htmlfeedback( 'render' );
			} );

			// Change marker color
			$( 'input[name=marker]' ).change( function ( event ) {
				if ( $( 'input[name=marker]:checked' ).val() === 'sticky' ) {
					$( 'body' ).htmlfeedback( 'sticky', 'true' );
					$( '.markers' )[0].addEventListener( 'click', addStickyNote, false );
					$( '.markers' ).css( 'cursor', 'text' );
				} else {
					$( 'body' ).htmlfeedback( 'sticky', 'false' );
					$( '.markers' )[0].removeEventListener( 'click', addStickyNote, false );
					$( '.markers' ).css( 'cursor', 'crosshair' );
					$( 'body' ).htmlfeedback( 'color', $( 'input[name=marker]:checked' ).val() );
				}
			} );
			/* HTMLFeedback END */
		}
	} );

}( mediaWiki, jQuery ) );
