<?php

class PGFTikZParser {

	/**
	 * Report error to wikipage
	 *
	 * @param $rawmsg string Raw error string (escaped)
	 * @return string HTML block containing error message as displayed in output
	 */
	private static function errorMsg( $rawmsg ) {
		return Html::openElement( 'div' ) .
		       Html::openElement( 'pre' ) .
		       Html::openElement( 'tt' ) .
		       wfMessage( 'pgftikz-error-title' )->escaped() .
		       Html::closeElement( 'tt' ) .
		       $rawmsg .
		       Html::closeElement( 'pre' ) .
		       Html::closeElement( 'div' );
	}

	/**
	 * Report error from message object
	 *
	 * @param $msg Message Message object
	 * @return string HTML block containing error message as displayed in output
	 */
	private static function errorMsgObj( $msg ) {
		return self::errorMsg( $msg->escaped() );
	}

	/**
	 * Report error with content of log
	 *
	 * @param $msg Message Message object
	 * @param $log string Raw error detail text (e.g. exception message)
	 * @return string HTML block containing error message as displayed in output
	 */
	private static function errorMsgLog( $msg, $log, $nLines = -1 ) {
		$log = explode( PHP_EOL, $log );
		if ( $nLines != -1 ) {
			$nLinesLog = count( $log );
			$log = array_slice( $log, $nLinesLog - $nLines + 1, $nLinesLog);
		}
		$log = implode ( "<br />", $log );
		return self::errorMsg( $msg->escaped() . "<br />" . $log );
	}

	/**
	 * Delete wikipage created for preview (if it exists)
	 */
	private static function deletePreviewPage ( $fname, $previewSuffix,
	                                            $token = null ) {

		global $wgRequest, $wgUser;

		// Get filename for preview
		$fname_preview = preg_replace( "/\.(\w+)$/", $previewSuffix . ".$1",
		                               $fname );

		// Check page existence (using API)
		$params = new DerivativeRequest(
		    $wgRequest,
		    array(
		        'action' => 'query',
		        'titles' => 'File:' . urlencode( $fname_preview ),
		        'prop'   => 'info' )
		);
		$api = new ApiMain( $params );
		try {
			$api->execute();
		} catch ( Exception $e ) {
			self::errorMsgLog( wfMessage( 'pgftikz-error-apigetpagecontent' ),
			                   $e->getMessage() );
		}
		if ( defined( 'ApiResult::META_CONTENT' ) ) {
			$apiPagePreview = array_values(
				(array)$api->getResult()->getResultData( array( 'query', 'pages' ), array( 'Strip' => 'base' ) )
			);
		} else {
			$apiResult = $api->getResultData();
			$apiPagePreview = array_values( $apiResult['query']['pages'] );
		}

		if ( count( $apiPagePreview ) > 0 ) {
			$previewPageExists = array_key_exists( 'pageid',
			                                       $apiPagePreview[0] );
			if ( $previewPageExists ) {
				// If page exists, delete it (API call)

				if ( $token === null ) {
					// Get edit token if not provided
					$token = $wgUser->getEditToken();
				}

				$reqParams = array(
				    'action'     => 'delete',
				    'title'      => 'File:' . urlencode( $fname_preview ),
				    'token'      => $token );
				$api = new ApiMain(
				    new DerivativeRequest( $wgRequest, $reqParams, true ),
				    true // enable write?
				);
				try {
					$api->execute();
				} catch ( Exception $e ) {
					self::errorMsgLog( wfMessage( 'pgftikz-error-apidelete' ),
					                   $e->getMessage() );
				}
			}
		}
	}

	/**
	 * Main parser function
	 */
	public static function PGFTikZParse( $input, array $args,
	                                     Parser $parser, PPFrame $frame) {

		// Header / footer for image pages (better if not configurable by user?)
		$wgPGFTikZTextMarker = "PGFTikZ-file-text -- DO NOT EDIT";
		// Suffix appended to filename to create preview page
		$wgPGFTikZPreviewSuffix = "__PGFTikZpreview";

		// Local parameters
		$flagDebug = true;
		$eol = PHP_EOL; // "\n"

		// Special case for use within template
		$isContentPage = $parser->getTitle()->isContentPage();
		if ( $flagDebug ) {
			wfDebugLog( "", "Is content page: " .
			            ( $isContentPage ? "yes" : "no" ) . "\n" );
		}
		if( !$isContentPage ) {
			return "<nowiki>" . $input . "</nowiki>";
		}
		// true: only expand template parameters; false: expand also magic words
		$expandedPageText = $parser->replaceVariables( $input, $frame, false );

		// Global variables
		global $wgRequest;
		global $wgServer;
		global $wgScriptPath;
		global $wgUser;
		global $wgParser;
		global $wgParserConf;

		// Global parameters
		global $wgPGFTikZDefaultDPI;

		// Found this to avoid 'Invalid marker' problem
		// http://www.cityofsteamwiki.com/extensions/ArticleComments/.svn/
		// pristine/39/39caa7e912faa5b2e8ae5d3418086bc95a7a7e91.svn-base
		if ( $parser === $wgParser ) {
			// Needed since r82645. Workaround the 'Invalid marker' problem by
			// giving a new parser to wfMessage().
			$wgParser = new StubObject( 'wgParser', $wgParserConf['class'],
			                             array( $wgParserConf ) );
		}

		// Detect preview mode (use another filename for upload if it is the
		// case).  This is to avoid edit conflicts when saving the page.
		$flagIsPreview = $parser->getOptions()->getIsPreview();

// DEBUG-disable cache
		//$parser->disableCache();

		// Create text page for input (saved in file's wikipage)
		$imgPageText  = "<pre>\n";
		$imgPageText .= $wgPGFTikZTextMarker . "\n";
		$imgPageText .= htmlspecialchars( $expandedPageText ) . "\n";
		$imgPageText .= $wgPGFTikZTextMarker . "\n";
		$imgPageText .= "</pre>\n";

		// User can force update of image file by passing an "update" argument
		// (the value of the parameter is discarded)
		$flagForceUpdate = isset( $args['update'] );

		// Get dpi parameter
		if ( isset( $args['dpi'] ) ) {
			$dpi = $args['dpi'];
		} else {
			$dpi = $wgPGFTikZDefaultDPI;
		}

		// Split input line by line
		$lines = explode( "\n", $expandedPageText );
		// Remove empty lines, reindex to 0:N-1
		$lines = array_values( array_filter( $lines, 'trim' ) );
		$nLines = count( $lines );
		if ( count( $nLines ) == 0 ) {
			return self::errorMsg ( wfMessage( 'pgftikz-error-emptyinput' ) );
		}

		// Get image entry (should be the first line)
		// Require a filename to use for output image
		$imgFname = "";
		$firstLineIdx = 0;
		while ( strlen( $lines[$firstLineIdx] ) == 0 &&
		        $firstLineIdx < $nLines ) {
			$firstLineIdx++;
		}
		if ( $firstLineIdx >= $nLines ) {
			return self::errorMsgObj( wfMessage( 'pgftikz-error-emptyinput' ) );
		}

		// Extract filename from first (non-empty) line
		// $imageEntryLine is an image inclusion line
		// (e.g. [[File:graph.png|title]])
		$imageEntryLine = $lines[$firstLineIdx];
		if ( preg_match( "/\s*\[\[\s*File\s*:\s*([^|\]]*)/",
		                 $imageEntryLine, $matches ) ) {
			if ( count( $matches ) > 1 ) {
				$imgFname = $matches[1];
			} else {
				// DEBUG
				return self::errorMsgObj(
				    wfMessage( 'pgftikz-error-imagelineparse' ) );
			}
		} else {
			return self::errorMsgObj(
			    wfMessage( 'pgftikz-error-imagelineparse' ) );
		}
		if ( $flagDebug ) {
			wfDebugLog( "", "PGF-image entry line found: $imageEntryLine\n" );
		}
		if ( $flagIsPreview ) {
			// Append suffix to filename for preview
			$imgFnameOld = $imgFname;
			$imgFname = preg_replace( "/\.(\w+)$/",
			                          $wgPGFTikZPreviewSuffix . ".$1",
			                          $imgFname );
			// Update image entry line
			$imageEntryLine = str_replace( $imgFnameOld, $imgFname,
			                               $imageEntryLine );
		}
		if ($flagDebug) {
			wfDebugLog( "", "PGF-image fname: $imgFname - $imageEntryLine\n" );
		}

		// 1 - Check existence of image file
		// ---------------------------------
		// Note: there might be a better way using Image::newFromTitle() or
		// Image::newFromName() (see
		// http://www.ehartwell.com/TechNotes/MediaWikiSideTrips.htm#Load_image)

		// Check if a file with the same name exists using API
		$flagFoundImage = false;
		$flagNeedUpdate = false;
		if ( !$flagForceUpdate ) {
			$params = new DerivativeRequest(
				$wgRequest,
				array(
					'action' => 'query',
					'titles' => 'File:' . urlencode( $imgFname ),
					'prop'   => 'revisions',
					'rvprop' => 'content' )
				);
			$api = new ApiMain( $params );
			try {
				$api->execute();
			} catch ( Exception $e ) {
				self::errorMsgLog(
				    wfMessage( 'pgftikz-error-apigetpagecontent' ),
				    $e->getMessage() );
			}
			if ( defined( 'ApiResult::META_CONTENT' ) ) {
				$apiPagesResult = array_values(
					(array)$api->getResult()->getResultData( array( 'query', 'pages' ), array( 'Strip' => 'base' ) )
				);
			} else {
				$apiResult = $api->getResultData();
				$apiPagesResult = array_values( $apiResult['query']['pages'] );
			}

			if ( count( $apiPagesResult ) > 0 ) {
				$flagFoundImage = array_key_exists( 'revisions',
				                                    $apiPagesResult[0] );
				if ( $flagFoundImage ) {
//DEBUG
					if ( $flagDebug ) {
						wfDebugLog( "", "PGF-File $imgFname already exists, " .
						            "checking if different\n". "\n");
					}
					$revisions = ApiResult::stripMetadataNonRecursive(
						$apiPagesResult[0]['revisions']
					);
					if ( count( $revisions ) > 0 ) {
						// File already exists, compare content
						if ( defined( 'ApiResult::META_CONTENT' ) &&
							isset( $revisions[0][ApiResult::META_CONTENT] )
						) {
							$imgPageTextRef = $revisions[0][$revisions[0][ApiResult::META_CONTENT]];
						} else {
							$imgPageTextRef = $revisions[0]['*'];
						}
						$textNewArray = explode( PHP_EOL, $imgPageText );
						$textRefArray = explode( PHP_EOL, $imgPageTextRef );
						$nLinesNew = count( $textNewArray );
						$nLinesRef = count( $textRefArray );
						// Compare strings between $wgPGFTikZTextMarker lines
						$liNew = 1;
						$liRef = 0;
						$foundFirst = false;
						while ( $liRef < $nLinesRef && !$foundFirst ) {
							$lineRef = $textRefArray[$liRef];
							if ( strcmp( $lineRef,
							             $wgPGFTikZTextMarker ) == 0 ) {
								$foundFirst = true;
							} else {
								$liRef++;
							}
						}
						if ( !$foundFirst ) {
							return self::errorMsgObj(
							    wfMessage( 'pgftikz-error-nonpgffile' ) );
						}
						$foundDiff = false;
						while ( $liNew < $nLinesNew && $liRef < $nLinesRef &&
						        !$foundDiff ) {
							$lineRef = $textRefArray[$liRef];
							$lineNew = $textNewArray[$liRef];
							if ( strcmp( $lineRef, $lineNew ) != 0 ) {
								$foundDiff = true;
							} else {
								$liRef++;
								$liNew++;
							}
						}
						$flagNeedUpdate = $foundDiff;
					} else {
						return self::errorMsgObj(
						    wfMessage( 'pgftikz-error-apigetpagecontent' ) );
					}
				} else {
					if ( $flagDebug ) {
						wfDebugLog( "", "PGF-image not found\n" );
					}
				}
			} else {
				return self::errorMsgObj(
				    wfMessage( 'pgftikz-error-apigetpagecontent' ) );
			}
		}
// DEBUG
		if ( $flagDebug ) {
			wfDebugLog( "", "PGF-Found  = " . ( $flagFoundImage  ?1:0) . "\n");
			wfDebugLog( "", "PGF-Update = " . ( $flagForceUpdate ?1:0) . "\n");
			wfDebugLog( "", "PGF-NeedUp = " . ( $flagNeedUpdate  ?1:0) . "\n");
		}

		// If the file exists and update is not needed or forced, simply render
		// the image.
		if ( $flagFoundImage && !$flagNeedUpdate && !$flagForceUpdate ) {
//DEBUG
			if ( $flagDebug ) {
				wfDebugLog( "","PGF-no need to update, display image\n");
			}
			if ( !$flagIsPreview ) {
				self::deletePreviewPage( $imgFname, $wgPGFTikZPreviewSuffix );
			}
			return $parser->recursiveTagParse( $imageEntryLine, $frame );
		}
//DEBUG
		if ( $flagDebug ) {
			wfDebugLog( "","PGF-Continue with compilation". "\n");
		}

		// End-of-line character for temporary tex file
		$TEXLR = PHP_EOL; //"\n";

		// Extract preamble
		$preambleStr = "";
		$li = $firstLineIdx + 1;
		$foundPreambleStart = false;
		$foundPreambleEnd = false;
		while ( $li < $nLines && !$foundPreambleEnd )
		{
			$line = $lines[$li];
			if ( !$foundPreambleStart ) {
				if ( preg_match("#<PGFTikZPreamble>#", $line ) ) {
					$foundPreambleStart = true;
				}
			} else {
				if ( preg_match( "#</PGFTikZPreamble>#", $line ) ) {
					$foundPreambleEnd = true;
				} else {
					// Append preamble line to latex string
					$preambleStr .= $line . $TEXLR;
				}
			}
			$li++;
		}
		if ( !$foundPreambleStart || !$foundPreambleEnd ) {
			return self::errorMsgObj(
			    wfMessage( 'pgftikz-error-preambleparse' ) );
		}

		// Extract tex input
		$latexContent = "";
		while ( $li < $nLines ) {
			$latexContent .= $lines[$li] . $TEXLR;
			$li++;
		}

		// Instantiate compiler module
		$compiler = new PGFTikZCompiler();

		// Create latex file, compile and convert
		if ( !$compiler->generateImage( $preambleStr, $latexContent, $imgFname,
		                                $dpi, $TEXLR ) ) {
			return self::errorMsg( $compiler->getError() );
		}

//DEBUG
		if ( $flagDebug ) {
			wfDebugLog( "", "PGF-done compiling, upload" . "\n");
		}

		// 5 - Upload output image to wiki
		// -------------------------------

		// File location
		$filename = $compiler->getFolder() . "/" . $imgFname;

		// Get edit token
		$token = $wgUser->getEditToken();

		// Request parameters
		$comment = 'Automatically uploaded by PGFTikZ extension';
		$params = array(
		    'filename'        => $imgFname,
		    'comment'         => $comment,
		    'text'            => $imgPageText,
		    'file'            => '@'.$filename,
		    'ignorewarnings'  => '1',
		    'token'           => $token
		);

//DEBUG
		if ($flagDebug) {
			wfDebugLog( "", "PGF-upload from file\n");
		}

		$upload = new UploadFromFile();
		$upload->initializeFromRequest( $wgRequest );
		$upload->initializePathInfo( $imgFname, $filename,
		                             filesize( $filename ) );

		$title = $upload->getTitle();
		if( $title == null ) {
			wfDebugLog( '', 'PGF-title empty' );
			return;
		}
		$warnings = $upload->checkWarnings();
		if ( $flagDebug ) {
			$var = var_export( $warnings, true );
			wfDebugLog( '', 'PGF-warnings' . $var );
		}

		$verification = $upload->verifyUpload();
		if ( $flagDebug ) {
			$var = var_export( $verification, true );
			wfDebugLog( '', 'PGF-verification' . $var );
		}
		if ( $verification['status'] === UploadBase::OK ) {
			$upload->performUpload( $comment, $imgPageText, false, $wgUser );
		} else {
			switch( $verification['status'] ) {
			case UploadBase::EMPTY_FILE:
				return self::errorMsgObj(
				    wfMessage( 'pgftikz-error-uploadlocal_error_empty' ) );
				break;
			case UploadBase::FILETYPE_MISSING:
				return self::errorMsgObj(
				    wfMessage( 'pgftikz-error-uploadlocal_error_missing' ) );
				break;
			case UploadBase::FILETYPE_BADTYPE:
				global $wgFileExtensions;
				return self::errorMsgObj(
				    wfMessage( 'pgftikz-error-uploadlocal_error_badtype' ) );
				break;
			case UploadBase::MIN_LENGTH_PARTNAME:
				return self::errorMsgObj(
				    wfMessage( 'pgftikz-error-uploadlocal_error_tooshort' ) );
				break;
			case UploadBase::ILLEGAL_FILENAME:
				return self::errorMsgObj(
				    wfMessage( 'pgftikz-error-uploadlocal_error_illegal' ) );
				break;
			case UploadBase::OVERWRITE_EXISTING_FILE:
				return self::errorMsgObj(
				    wfMessage( 'pgftikz-error-uploadlocal_error_overwrite' ) );
				break;
			case UploadBase::VERIFICATION_ERROR:
				return self::errorMsgLog(
				    wfMessage( 'pgftikz-error-uploadlocal_error_verify' ),
				    $verification['details'][0] );
				break;
			case UploadBase::HOOK_ABORTED:
				return self::errorMsgObj(
				    wfMessage( 'pgftikz-error-uploadlocal_error_hook' ) );
				break;
			default:
				return self::errorMsgObj(
				    wfMessage( 'pgftikz-error-uploadlocal_error_unknown' ) );
				break;
			}
		}

		// Still need to update page text
		if ( $flagFoundImage || $flagForceUpdate ) {
//DEBUG
			if ($flagDebug) {
				wfDebugLog( "", "PGF-update page content\n");
			}
			// Note: ignoring edit conflicts (for now)
			$reqParams = array(
				'action'     => 'edit',
				'title'      => 'File:' . urlencode( $imgFname),
				'text'       => $imgPageText,
				'summary'    => wfMessage( 'update from PGFTikZ' )->inContentLanguage()->text(),
				'notminor'   => true,
				'recreate'   => true,
				'bot'        => true,
				'token'      => $token
				);
			$api = new ApiMain(
				new DerivativeRequest( $wgRequest, $reqParams, true ),
				true // enable write?
				);
			try {
				$api->execute();
			} catch ( Exception $e ) {
				self::errorMsgLog( wfMessage( 'pgftikz-error-apiedit' ),
				                   $e->getMessage() );
			}
			if ( defined( 'ApiResult::META_CONTENT' ) ) {
				$apiResult = $api->getResult()->getResultData();
			} else {
				$apiResult = $api->getResultData();
			}
			if ( array_key_exists ( 'edit', $apiResult ) ) {
				if ( array_key_exists( 'result', $apiResult['edit'] ) ) {
					if ( strcasecmp( $apiResult['edit']['result'],
					                 'Success' ) != 0 ) {
						return self::errorMsgObj(
						    wfMessage( 'pgftikz-error-apiedit' ) );
					}
				} else {
					return self::errorMsgObj(
					    wfMessage( 'pgftikz-error-apiedit' ) );
				}
			} else {
				return self::errorMsgObj(
				    wfMessage( 'pgftikz-error-apiedit' ) );
			}
		}
//DEBUG
		if ($flagDebug) {
			wfDebugLog( "", "PGF-done updating page content\n");
		}

		// Delete intermediate files
//DEBUG
		if ($flagDebug) {
			wfDebugLog( "", "PGF-deleting temporary data\n");
		}

		// Delete file used for preview
		if ( !$flagIsPreview ) {
			self::deletePreviewPage( $imgFname, $wgPGFTikZPreviewSuffix,
			                         $token );
		}

		// 6 - Insert <img ...> code in page
		// ---------------------------------
		// Use recursiveTagParse()
		// (https://www.mediawiki.org/wiki/Manual:
		// Tag_extensions#Since_version_1.16)
//DEBUG
		if ($flagDebug) {
			wfDebugLog( "", "PGF-render image:\n" . $imageEntryLine. "\n" );
		}

		return $parser->recursiveTagParse( $imageEntryLine, $frame );
	}
}

