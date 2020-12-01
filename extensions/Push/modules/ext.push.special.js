/**
 * JavaScript for Special:Push in the Push extension.
 * @see https://www.mediawiki.org/wiki/Extension:Push
 *
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

( function( mw, $ ) { $( document ).ready( function() {
	var resultList = $( '#pushResultList' );
	var targets = mw.config.get( 'wgPushTargets' ) || [];
	var pages = mw.config.get( 'wgPushPages' ) || [];
	var requestAmount = Math.min( pages.length, mw.config.get( 'wgPushWorkerCount' ) || 0 );
	var batchSize = Math.min( targets.length, mw.config.get( 'wgPushBatchSize' ) || 0 );
	var pushedFiles = [];

	for ( i = requestAmount; i > 0; i-- ) {
		initiateNextPush();
	}

	function initiateNextPush() {
		var page = pages.pop();

		if ( page ) {
			startPush( page, 0, null );
		} else if ( !--requestAmount ) {
			showCompletion();
		}
	}

	function appendAndScroll( item ) {
		var box = $('#pushResultDiv');
		var innerBox = $('#pushResultDiv > .innerResultBox');
		var atBottom = Math.abs( innerBox.offset().top ) + box.height() + box.offset().top >= innerBox.outerHeight();

		resultList.append( item );

		if ( atBottom ) {
			box.attr( {'scrollTop': box.attr( 'scrollHeight' )} );
		}
	}

	function startPush( pageName, targetOffset, listItem ) {
		if ( targetOffset === 0 ) {
			listItem = $( '<li />' );
			listItem.text( mw.msg( 'push-special-item-pushing', pageName ) );
			appendAndScroll( listItem );
		}

		var currentBatchLimit = Math.min( targetOffset + batchSize, targets.length );
		var currentBatchStart = targetOffset;
		if ( targetOffset < targets.length ) {
			listItem.text( listItem.text() + '...' );

			targetOffset = currentBatchLimit;

			new mw.Api().postWithEditToken( {
				action: 'push',
				page: pageName,
				targets: targets.slice( currentBatchStart, currentBatchLimit ).join( '|' )
			} ).done( function ( data ) {
				if ( data.error ) {
					handleError( listItem, pageName, data.error );
				} else if ( data.length > 0 && data[0].edit && data[0].edit.captcha ) {
					handleError( listItem, pageName, { info: mw.msg( 'push-err-captcha-page', pageName ) } );
				} else {
					startPush( pageName, targetOffset, listItem );
				}
			} ).fail( function ( errorCode, data ) {
				handleError( listItem, pageName, data.error );
			} );
		}
		else {
			if ( mw.config.get( 'wgPushIncFiles' ) ) {
				getIncludedImagesAndInitPush( pageName, listItem );
			}
			else {
				completeItem( pageName, listItem );
			}
		}
	}

	function getIncludedImagesAndInitPush( pageName, listItem ) {
		listItem.text( mw.msg( 'push-special-obtaining-fileinfo', pageName ) );

		new mw.Api().get(
			{
				'action': 'query',
				'prop': 'images',
				'titles': pageName,
				'imlimit': 500
		}).done( function ( data ) {
			if ( data.query ) {
				var images = [];
				for ( var page in data.query.pages ) {
					if ( data.query.pages.hasOwnProperty( page ) && data.query.pages[page].images ) {
						for ( var i = data.query.pages[page].images.length - 1; i >= 0; i-- ) {
							if ( $.inArray( data.query.pages[page].images[i].title, pushedFiles ) === -1 ) {
								pushedFiles.push( data.query.pages[page].images[i].title );
								images.push( data.query.pages[page].images[i].title );
							}
						}
					}
				}

				if ( images.length > 0 ) {
					var currentFile = images.pop();
					startFilePush( pageName, images, 0, listItem, currentFile );
				} else {
					completeItem( pageName, listItem );
				}
			} else {
				handleError( pageName, { info: mw.msg( 'push-special-err-imginfo-failed' ) } );
			}
		});
	}

	function startFilePush( pageName, images, targetOffset, listItem, fileName ) {
		if ( targetOffset === 0 ) {
			listItem.text( mw.msg( 'push-special-pushing-file', pageName, fileName ) );
		}
		else {
			listItem.text( listItem.text() + '...' );
		}

		var currentBatchLimit = Math.min( targetOffset + batchSize, targets.length );
		var currentBatchStart = targetOffset;

		if ( targetOffset < targets.length ) {
			listItem.text( listItem.text() + '...' );

			targetOffset = currentBatchLimit;

			new mw.Api().postWithEditToken({
				action: 'pushimages',
				images: fileName,
				targets: targets.slice( currentBatchStart, currentBatchLimit ).join( '|' )
			}).done( function( data ) {
				var fail = false;

				if ( data.error ) {
					handleError( listItem, pageName, { info: mw.msg( 'push-tab-err-filepush', data.error.info ) } );
					fail = true;
				} else {
					for ( var i in data ) {
						if ( data.hasOwnProperty( i ) ) {
							if ( data[i].error ) {
								handleError( listItem, pageName, { info: mw.msg( 'push-tab-err-filepush', data[i].error.info ) } );
								fail = true;
								break;
							} else if ( !data[i].upload ) {
								handleError( listItem, pageName, { info: mw.msg( 'push-tab-err-filepush-unknown' ) } );
								fail = true;
								break;
							}
						}
					}
				}

				if ( !fail ) {
					startFilePush( pageName, images, targetOffset, listItem, fileName );
				}
			});
		}
		else {
			if ( images.length > 0 ) {
				var currentFile = images.pop();
				startFilePush( pageName, images, 0, listItem, currentFile );
			} else {
				completeItem( pageName, listItem );
			}
		}
	}

	function completeItem( pageName, listItem ) {
		listItem.text( mw.msg( 'push-special-item-completed', pageName ) );
		listItem.css( 'color', 'darkgray' );
		initiateNextPush();
	}

	function handleError( listItem, pageName, error ) {
		listItem.text( mw.msg( 'push-special-item-failed', pageName, error.info ) );
		listItem.css( 'color', 'darkred' );
		initiateNextPush();
	}

	function showCompletion() {
		appendAndScroll( $( '<li />' ).append( $( '<b />' ).text( mw.msg( 'push-special-push-done' ) ) ) );
	}

} ); } )( mediaWiki, jQuery );
