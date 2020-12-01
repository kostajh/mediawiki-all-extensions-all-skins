/**
 * JavaScript for the Push tab in the Push extension.
 * @see https://www.mediawiki.org/wiki/Extension:Push
 *
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

( function( mw, $ ) { $( document ).ready( function() {

	var pages,
		targetData = [],
		$pushButton = $( '.push-button' ),
		$pushAllButton = $( '#push-all-button' ),
		$txtTemplateList = $( '#txtTemplateList' ),
		$txtFileList = $( '#txtFileList' ),
		$checkIncFiles = $( '#checkIncFiles' );

	$.each( $pushButton, function( i, v ) {
		getRemoteArticleInfo( $( v ).attr( 'targetid' ), $( v ).attr( 'pushtarget' ) );
	} );

	$pushButton.click( function() {
		this.disabled = true;

		var errorDiv = $( '#targeterrors' + $( this ).attr( 'targetid' ) );
		errorDiv.fadeOut( 'fast' );

		if ( $('#checkIncTemplates').prop('checked') ) {
			pages = mw.config.get( 'wgPushTemplates' );
			pages.unshift( $('#pageName').attr('value') );
		} else {
			pages = [ $( '#pageName' ).attr( 'value' ) ];
		}

		setButtonToImgPush(
			this,
			pages,
			$( this ).attr( 'pushtarget' ),
			$( this ).attr( 'targetname' )
		);
	} );

	$pushAllButton.click( function() {
		this.disabled = true;
		this.innerHTML = mw.msg( 'push-button-pushing' );
		$.each( $pushButton, function( i, v ) {
			$( v ).click();
		} );
	} );

	$( '#divIncTemplates' ).hover(
		function() {
			var isHidden = $txtTemplateList.css( 'opacity' ) === 0;

			if ( isHidden ) {
				$txtTemplateList.css( 'display', 'inline' );
			}

			$txtTemplateList.fadeTo(
				isHidden? 'slow' : 'fast',
				1
			);
		},
		function() {
			$txtTemplateList.fadeTo( 'fast', 0.5 )
		}
	).click( function() {
		setIncludeFilesText();
		displayTargetsConflictStatus();
	} );

	$( '#divIncFiles' ).click( function() {
		displayTargetsConflictStatus();
	} ).hover(
		function() {
			var isHidden = $txtFileList.css( 'opacity' ) === 0;

			if ( isHidden ) {
				$txtFileList.css( 'display', 'inline' );
				setIncludeFilesText();
			}

			$txtFileList.fadeTo(
				isHidden ? 'slow' : 'fast',
				1
			);
		},
		function() {
			$txtFileList.fadeTo( 'fast', 0.5 )
		}
	);

	function setIncludeFilesText() {
		if ( $checkIncFiles.length !== 0 ) {
			var files = mw.config.get( 'wgPushPageFiles' );

			if ( $('#checkIncTemplates').prop('checked') ) {
				files = files.concat( mw.config.get( 'wgPushTemplateFiles' ) );
			}

			if ( files.length > 0 ) {
				$txtFileList.text( '(' + mw.msg( 'push-tab-embedded-files' ) + ' ' );

				for ( var i in files ) {
					if ( files.hasOwnProperty( i ) ) {
						if ( i > 0 ) {
							$txtFileList.append( ', ' );
						}
						$txtFileList.append(
							$( '<a>' )
								.attr( 'href', mw.config.get( 'wgPushIndexPath' ) + '?title=' + files[i] )
								.text( files[i] )
						);
					}
				}

				$txtFileList.append( ')' );
			} else {
				$txtFileList.text( mw.msg( 'push-tab-no-embedded-files' ) );
			}
		}
	}

	function getRemoteArticleInfo( targetId, targetUrl ) {
		var pageName = $( '#pageName' ).attr('value');

		$.getJSON(
			targetUrl + '/api.php?callback=?',
			{
				'action': 'query',
				'format': 'json',
				'prop': 'revisions',
				'rvprop': 'timestamp|user|comment',
				'titles': [pageName]
					.concat( mw.config.get( 'wgPushTemplates' ) )
					.concat( mw.config.get( 'wgPushPageFiles' ) )
					.concat( mw.config.get( 'wgPushTemplateFiles' ) )
					.join( '|' )
			},
			function( data ) {
				if ( data.query ) {
					var infoDiv = $( '#targetinfo' + targetId );

					var existingPages = [];
					var remotePage = false;
					var message;

					for ( var remotePageId in data.query.pages ) {
						if ( data.query.pages.hasOwnProperty( remotePageId ) ) {
							if ( remotePageId > 0 ) {
								if ( data.query.pages[remotePageId].title === pageName ) {
									remotePage = data.query.pages[remotePageId];
								} else {
									existingPages.push( data.query.pages[remotePageId] );
								}
							}
						}
					}

					targetData[targetId] = { 'existingPages': existingPages };

					if ( remotePage ) {
						$( '#targetlink' + targetId ).attr( { 'class': '' } );

						var revision = remotePage.revisions[0];
						var dateTime = revision.timestamp.split( 'T' );

						message = mw.msg(
							'push-tab-last-edit',
							revision.user,
							dateTime[0],
							dateTime[1].replace( 'Z', '' )
						);
					} else {
						$( '#targetlink' + targetId ).attr( { 'class': 'new' } );
						message = mw.msg( 'push-tab-not-created' );
					}

					infoDiv.text( message );
					infoDiv.fadeIn( 'slow' );

					displayTargetConflictStatus( targetId );
				}
			}
		);
	}

	function displayTargetsConflictStatus() {
		$.each( $( ".push-button" ), function( i, v ) {
			displayTargetConflictStatus( $( v ).attr( 'targetid' ) );
		} );
	}

	function displayTargetConflictStatus( targetId ) {
		if ( !targetData[targetId] ) {
			// It's possible the request to retrieve this data failed, so don't do anything when this is the case.
			return;
		}

		if ( $('#checkIncTemplates').prop('checked') ) {
			var overrideTemplates = [];

			for ( var remotePageId in targetData[targetId].existingPages ) {
				if ( targetData[targetId].existingPages.hasOwnProperty( remotePageId ) ) {
					if ( targetData[targetId].existingPages[remotePageId].ns === 10 ) {
						// Add the template, but get rid of the namespace prefix first.
						overrideTemplates.push( targetData[targetId].existingPages[remotePageId].title.split( ':', 2 )[1] );
					}
				}
			}

			if ( overrideTemplates.length > 0 ) {
				$( '#targettemplateconflicts' + targetId )
					.text( mw.msg( 'push-tab-template-override',
						overrideTemplates.join( ', ' ),
						overrideTemplates.length
					) )
					.fadeIn( 'slow' );
			} else {
				$( '#targettemplateconflicts' + targetId ).fadeOut( 'slow' );
			}
		} else {
			$( '#targettemplateconflicts' + targetId ).fadeOut( 'fast' );
		}

		if ( $checkIncFiles.length !== 0 && $checkIncFiles.prop( 'checked' ) ) {
			var overideFiles = [];

			for ( remotePageId in targetData[targetId].existingPages ) {
				if ( targetData[targetId].existingPages.hasOwnProperty( remotePageId ) ) {
					if ( targetData[targetId].existingPages[remotePageId].ns === 6 ) {
						// Add the file, but get rid of the namespace prefix first.
						overideFiles.push( targetData[targetId].existingPages[remotePageId].title.split( ':', 2 )[1] );
					}
				}
			}

			if ( overideFiles.length > 0 ) {
				$( '#targetfileconflicts' + targetId )
					.text( mw.msg( 'push-tab-files-override',
						overideFiles.join( ', ' ),
						overideFiles.length
					) )
					.fadeIn( 'slow' );
			} else {
				$( '#targetfileconflicts' + targetId ).fadeOut( 'slow' );
			}
		} else {
			$( '#targetfileconflicts' + targetId ).fadeOut( 'fast' );
		}
	}

	function initiatePush( sender, pages, targetUrl, targetName ) {
		sender.innerHTML = mw.msg( 'push-button-pushing' );

		new mw.Api().postWithEditToken( {
			action: 'push',
			page: pages,
			targets: targetUrl
		} ).done( function ( data ) {
			if ( data.error ) {
				handleError( sender, data.error );
			} else if ( data[0] && data[0].error ) {
				handleError( sender, data[0].error );
			} else if ( data[0] && data[0].edit && data[0].edit.captcha ) {
				handleError( sender, { info: mw.msg( 'push-err-captacha', targetName ) } );
			} else {
				handlePushingCompletion( sender );
			}
		} ).fail( function ( errorCode, data ) {
			handleError( sender, data.error );
		} );
	}

	function handlePushingCompletion( sender ) {
		sender.innerHTML = mw.msg( 'push-button-completed' );

		setTimeout( function() {
			reEnableButton( sender );
		}, 1000 );
	}

	function setButtonToImgPush( button, pages, targetUrl, targetName ) {
		var images = mw.config.get( 'wgPushPageFiles' ).concat( mw.config.get( 'wgPushTemplateFiles' ) );

		if ( images.length > 0 && $checkIncFiles.length !== 0 && $checkIncFiles.prop( 'checked' ) ) {
			var currentFile = images.pop();
			button.innerHTML = mw.msg( 'push-button-pushing-files' );
			initiateImagePush( button, pages, targetUrl, targetName, images, currentFile );
		} else {
			initiatePush( button, pages, targetUrl, targetName );
		}
	}

	function initiateImagePush( sender, pages, targetUrl, targetName, images, fileName ) {
		new mw.Api().postWithEditToken( {
			action: 'pushimages',
			images: fileName,
			targets: targetUrl
		} ).done( function ( data ) {
			var fail = false;

			if ( data.error ) {
				data.error.info = mw.msg( 'push-tab-err-filepush', data.error.info );
				handleError( sender, data.error );
				fail = true;
			} else {
				for ( var i in data ) {
					if ( data.hasOwnProperty( i ) ) {
						if ( data[i].error ) {
							data[i].error.info = mw.msg( 'push-tab-err-filepush', data[i].error.info );
							handleError( sender, data[i].error );
							fail = true;
							break;
						} else if ( !data[i].upload ) {
							data[i].error.info = mw.msg( 'push-tab-err-filepush-unknown' );
							handleError( sender, data[i].error );
							fail = true;
							break;
						}
					}
				}
			}

			if ( !fail ) {
				if ( images.length > 0 ) {
					var currentFile = images.pop();
					initiateImagePush( sender, pages, targetUrl, targetName, images, currentFile );
				} else {
					initiatePush( sender, pages, targetUrl, targetName );
				}
			}
		} ).fail( function ( errorCode, data ) {
			data.error.info = mw.msg( 'push-tab-err-filepush', data.error.info );
			handleError( sender, data.error );
		} );
	}

	function reEnableButton( button ) {
		button.innerHTML = mw.msg( 'push-button-text' );
		button.disabled = false;

		getRemoteArticleInfo( $( button ).attr( 'targetid' ), $( button ).attr( 'pushtarget' ) );

		// If there is a "push all" button, make sure to reset it
		// when all other buttons have been reset.
		if ( typeof $pushAllButton !== "undefined" ) {
			var hasDisabled = false;

			$.each( $pushButton, function( i, v ) {
				if ( v.disabled ) {
					hasDisabled = true;
				}
			} );

			if ( !hasDisabled ) {
				$pushAllButton.attr( "disabled", false );
				$pushAllButton.text( mw.msg( 'push-button-all' ) );
			}
		}
	}

	function handleError( sender, error ) {
		var errorDiv = $( '#targeterrors' + $( sender ).attr( 'targetid' ) );

		if ( error.code && error.code === 'uploaddisabled' ) {
			error.info = mw.msg( 'push-tab-err-uploaddisabled' );
		}

		errorDiv.text( error.info );
		errorDiv.fadeIn( 'slow' );

		sender.innerHTML = mw.msg( 'push-button-failed' );
		setTimeout( function() { reEnableButton( sender ); }, 2500 );
	}

} ); } )( mediaWiki, jQuery );
