<?php

use MediaWiki\MediaWikiServices;
use Wikimedia\IPSet;

class ApiParsoidBatch extends ApiBase {

	private $mPageSet;

	private function getPageSet() {
		if ( !isset( $this->mPageSet ) ) {
			$this->mPageSet = new ApiPageSet( $this );
		}

		return $this->mPageSet;
	}

	public function execute() {
		$startTime = microtime( true );
		$params = $this->extractRequestParams();

		$context = $this->getContext();
		$config = $context->getConfig();
		$ipset = new IPSet( $config->get( 'ParsoidBatchAPI_AllowedIPs' ) );
		if ( !$ipset->match( $context->getRequest()->getIP() ) ) {
			$this->dieWithError( 'apierror-parsoid-batch-notallowed', 'not_allowed' );
		}

		// Parameter validation
		$batch = json_decode( $params['batch'], true );
		if ( !is_array( $batch ) ) {
			$this->dieWithError( 'apierror-parsoid-batch-invalidbatch', 'invalid_batch' );
		}
		// @phan-suppress-next-line PhanTypeMismatchArgumentNullableInternal
		if ( count( $batch ) > 500 ) {
			$this->dieWithError( 'apierror-parsoid-batch-batchtoolarge', 'batch_too_large' );
		}
		wfIncrStats( 'ParsoidBatchAPI.batches' );
		// @phan-suppress-next-line PhanTypeMismatchArgumentNullableInternal
		wfIncrStats( 'ParsoidBatchAPI.items', count( $batch ) );

		$size = 0;
		$filenames = [];
		foreach ( $batch as $itemIndex => $itemParams ) {
			$action = $itemParams['action'];
			$this->assertScalar( $itemParams, 'action' );
			if ( $action === 'parse' || $action === 'preprocess' ) {
				$this->assertScalar( $itemParams, 'title' );
				$this->assertScalar( $itemParams, 'text' );
				$this->assertScalarOrMissing( $itemParams, 'revid' );
				$size += strlen( $itemParams['text'] );
			} elseif ( $action === 'pageprops' ) {
				$this->assertArray( $itemParams, 'titles' );
				if ( count( $itemParams['titles'] ) > ApiBase::LIMIT_BIG1 ) {
					$this->dieWithError( [ 'apiwarn-toomanyvalues', 'titles', ApiBase::LIMIT_BIG1 ] );
				}
			} elseif ( $action === 'imageinfo' ) {
				$this->assertScalar( $itemParams, 'filename' );
				if ( isset( $itemParams['txopts'] ) ) {
					$this->assertArray( $itemParams, 'txopts' );
					$txopts = $itemParams['txopts'];
					foreach ( $txopts as $k => $v ) {
						$this->assertScalar( $txopts, $k );
					}
				}
				$this->assertScalarOrMissing( $itemParams, 'page' );
				// Normalize the filename in $batch so that we can find the corresponding
				// file in the findFiles() result
				$title = Title::makeTitleSafe( NS_FILE, $itemParams['filename'] );
				if ( $title ) {
					$filenames[] = $batch[$itemIndex]['filename'] = $title->getDBkey();
				}
			} else {
				$this->dieWithError(
					[ 'apierror-parsoid-batch-invalidaction', wfEscapeWikiText( $itemIndex ) ], 'invalid_action'
				);
			}
		}
		if ( $size > 1024 * $config->get( 'MaxArticleSize' ) ) {
			$this->dieWithError( 'apierror-parsoid-batch-texttoobig', 'text_too_big' );
		}

		// Now do the thing
		if ( count( $filenames ) ) {
			$files = MediaWikiServices::getInstance()->getRepoGroup()->findFiles( $filenames );
		} else {
			$files = [];
		}

		$batchResult = [];
		$result = $this->getResult();
		$revLookup = MediaWikiServices::getInstance()->getRevisionLookup();
		foreach ( $batch as $itemIndex => $itemParams ) {
			$action = $itemParams['action'];
			if ( $action === 'parse' || $action === 'preprocess' ) {
				$title = Title::newFromText( $itemParams['title'] );
				if ( !$title ) {
					$this->dieWithError(
						[ 'apierror-parsoid-batch-invalidtitle', wfEscapeWikiText( $itemIndex ) ], 'invalid_title'
					);
				}
				$revid = null;
				if ( isset( $itemParams['revid'] ) ) {
					$revid = intval( $itemParams['revid'] );
					$rev = $revLookup->getRevisionById( $revid );
					if ( !$rev ) {
						$this->dieWithError( [ 'apierror-nosuchrevid', $revid ] );
					}
					$pTitle = $title;
					$title = $rev->getPageAsLinkTarget();
					if ( !$pTitle->equals( $title ) ) {
						$this->addWarning( [ 'apierror-revwrongpage', $rev->getId(),
							wfEscapeWikiText( $pTitle->getPrefixedText() ) ] );
					}
				}
				$text = $itemParams['text'];
				switch ( $action ) {
					case 'parse':
						// @phan-suppress-next-line PhanTypeMismatchArgumentNullable
						$itemResult = $this->parse( $text, $title, $revid );
						break;
					case 'preprocess':
						// @phan-suppress-next-line PhanTypeMismatchArgumentNullable
						$itemResult = $this->preprocess( $text, $title, $revid );
						break;
					default:
						throw new Exception( "Invalid action despite validation already being done" );
				}
			} elseif ( $action === 'imageinfo' ) {
				$filename = $itemParams['filename'];
				$file = $files[$filename] ?? null;
				$txopts = $itemParams['txopts'] ?? [];
				$page = isset( $itemParams['page'] ) ? Title::newFromText( $itemParams['page'] ) : null;
				$itemResult = $this->imageinfo( $filename, $file, $txopts, $page );
			} elseif ( $action === 'pageprops' ) {
				$itemResult = $this->pageprops( $itemParams['titles'] );
			} else {
				throw new Exception( "Invalid action despite validation already being done" );
			}
			$batchResult[] = $itemResult;
		}
		ApiResult::setIndexedTagName( $batchResult, 'item' );
		$result->addValue( null, 'parsoid-batch', $batchResult, ApiResult::NO_SIZE_CHECK );

		// Send along time to compute the batch
		$result->addValue( null, 'parsoid-batch-time', microtime( true ) - $startTime );
	}

	protected function assertScalar( array $array, $key ) {
		if ( !isset( $array[$key] ) ) {
			$eKey = wfEscapeWikiText( $key ); // Might be user-supplied via txopts
			$this->dieWithError( [ 'apierror-missingparam', $eKey ], "missing_$eKey" );
		}
		if ( !is_scalar( $array[$key] ) ) {
			$eKey = wfEscapeWikiText( $key ); // Might be user-supplied via txopts
			$this->dieWithError( [ 'apierror-parsoid-batch-mustbescalar', $eKey ], "invalid_$eKey" );
		}
	}

	protected function assertScalarOrMissing( array $array, $key ) {
		if ( isset( $array[$key] ) && !is_scalar( $array[$key] ) ) {
			$this->dieWithError( [ 'apierror-parsoid-batch-mustbescalar', $key ], "invalid_$key" );
		}
	}

	protected function assertArray( array $array, $key ) {
		if ( !isset( $array[$key] ) ) {
			$this->dieWithError( [ 'apierror-missingparam', $key ], "missing_$key" );
		}
		if ( !is_array( $array[$key] ) ) {
			$this->dieWithError( [ 'apierror-parsoid-batch-mustbearray', $key ], "invalid_$key" );
		}
	}

	/**
	 * @param string $text
	 * @param Title $title
	 * @param int|null $revid
	 *
	 * @return array
	 * @throws MWException
	 * @throws MWUnknownContentModelException
	 */
	protected function parse( $text, Title $title, $revid ) {
		$parser = MediaWikiServices::getInstance()->getParser();

		$options = ParserOptions::newCanonical( $this->getContext() );
		$options->enableLimitReport( false );
		if ( is_callable( [ $options, 'setWrapOutputClass' ] ) &&
			!defined( 'ParserOutput::SUPPORTS_UNWRAP_TRANSFORM' )
		) {
			// @phan-suppress-next-line PhanTypeMismatchArgument
			$options->setWrapOutputClass( false ); // Parsoid doesn't want the output wrapper
		}
		$out = $parser->parse( $text, $title, $options, true, true, $revid );
		$result = [
			'text' => $out->getText( [ 'unwrap' => true ] ),
			'modules' => $this->formatModules( $out->getModules() ),
			'modulescripts' => $this->formatModules( [] ),
			'modulestyles' => $this->formatModules( $out->getModuleStyles() ),
			'categories' => $this->formatCategoryLinks( $out->getCategories() ),
			'properties' => $this->formatProperties( $out->getProperties() ),
		];
		$result[ApiResult::META_BC_SUBELEMENTS][] = 'text';
		return $result;
	}

	/**
	 * @param string $text
	 * @param Title $title
	 * @param int|null $revid
	 *
	 * @return array
	 * @throws MWException
	 * @throws MWUnknownContentModelException
	 */
	protected function preprocess( $text, Title $title, $revid ) {
		$parser = MediaWikiServices::getInstance()->getParser();

		$options = ParserOptions::newCanonical( $this->getContext() );
		$wikitext = $parser->preprocess( $text, $title, $options, $revid );
		$out = $parser->getOutput();
		$result = [
			'wikitext' => $wikitext,
			'categories' => $this->formatCategoryLinks( $out->getCategories() ),
			'properties' => $this->formatProperties( $out->getProperties() ),
			'modules' => $this->formatModules( $out->getModules() ),
			'modulescripts' => $this->formatModules( [] ),
			'modulestyles' => $this->formatModules( $out->getModuleStyles() ),
		];
		$result[ApiResult::META_BC_SUBELEMENTS][] = 'wikitext';
		return $result;
	}

	protected function formatModules( array $modules ) {
		$result = array_values( array_unique( $modules ) );
		ApiResult::setIndexedTagName( $result, 'm' );
		return $result;
	}

	protected function formatCategoryLinks( array $links ) {
		$result = [];
		foreach ( $links as $link => $sortkey ) {
			$entry = [ 'sortkey' => $sortkey ];
			ApiResult::setContentValue( $entry, 'category', (string)$link );
			$result[] = $entry;
		}
		ApiResult::setIndexedTagName( $result, 'cl' );
		return $result;
	}

	protected function formatProperties( array $props ) {
		$result = $props;
		ApiResult::setArrayType( $result, 'BCkvp', 'name' );
		ApiResult::setIndexedTagName( $result, 'pp' );
		return $result;
	}

	protected function pageprops( array $titles ) {
		$pageSet = $this->getPageSet();
		$pageSet->populateFromTitles( $titles );

		$pages = [];

		// This is pretty much copied from ApiQuery::outputGeneralPageInfo(),
		// except for adding page properties and redirect to good titles.

		foreach ( $pageSet->getMissingTitles() as $fakeId => $title ) {
			$vals = [];
			ApiQueryBase::addTitleInfo( $vals, $title );
			$vals['missing'] = true;
			if ( $title->isKnown() ) {
				$vals['known'] = true;
			}
			$pages[$fakeId] = $vals;
		}

		foreach ( $pageSet->getInvalidTitlesAndReasons() as $fakeId => $data ) {
			$pages[$fakeId] = $data + [ 'invalid' => true ];
		}

		foreach ( $pageSet->getSpecialTitles() as $fakeId => $title ) {
			$vals = [];
			ApiQueryBase::addTitleInfo( $vals, $title );
			$vals['special'] = true;
			if ( !$title->isKnown() ) {
				$vals['missing'] = true;
			}
			$pages[$fakeId] = $vals;
		}

		$pageProps = PageProps::getInstance();
		$goodTitles = $pageSet->getGoodTitles();
		$props = $pageProps->getProperties( $goodTitles, [ 'disambiguation' ] );

		foreach ( $goodTitles as $pageid => $title ) {
			$vals = [];
			$vals['pageid'] = $pageid;
			ApiQueryBase::addTitleInfo( $vals, $title );
			if ( isset( $props[$pageid] ) ) {
				$vals['pageprops'] = $props[$pageid];
			}
			if ( $title->isRedirect() ) {
				$vals['redirect'] = true;
			}
			$pages[$pageid] = $vals;
		}

		return $pages;
	}

	/**
	 * @param string $filename
	 * @param File|null $file
	 * @param array $txopts
	 * @param Title|null $page Title for BadFileLookup::isBadFile context
	 *
	 * @return array|null
	 */
	protected function imageinfo( $filename, $file, array $txopts, $page ) {
		if ( !$file ) {
			// Short return code for missing images
			return null;
		}
		$result = [
			'width' => $file->getWidth(),
			'height' => $file->getHeight(),
			'size' => $file->getSize(),
			'mediatype' => $file->getMediaType(),
			'mime' => $file->getMimeType(),
			'url' => wfExpandUrl( $file->getFullUrl(), PROTO_CURRENT ),
			'mustRender' => $file->mustRender(),
			'badFile' => MediaWikiServices::getInstance()->getBadFileLookup()
				->isBadFile( $filename, $page ?: null ),
		];
		$length = $file->getLength();
		if ( $length ) {
			$result['duration'] = (float)$length;
		}
		$txopts = $this->makeTransformOptions( $file, $txopts );
		$mto = $file->transform( $txopts );
		if ( $mto ) {
			if ( $mto->isError() ) {
				'@phan-var \MediaTransformError $mto';
				$result['thumberror'] = $mto->toText();
			} else {
				if ( $txopts ) {
					// Do srcset scaling
					Linker::processResponsiveImages( $file, $mto, $txopts );
					if ( count( $mto->responsiveUrls ) ) {
						$result['responsiveUrls'] = [];
						foreach ( $mto->responsiveUrls as $density => $url ) {
							$result['responsiveUrls'][$density] = wfExpandUrl(
								$url, PROTO_CURRENT );
						}
					}
				}

				// Proposed MediaTransformOutput serialization method for T51896 etc.
				if ( is_callable( [ $mto, 'getAPIData' ] ) ) {
					/** @phan-suppress-next-line PhanUndeclaredMethod */
					$result['thumbdata'] = $mto->getAPIData( [ 'fullurl' ] );
				}

				$result['thumburl'] = wfExpandUrl( $mto->getUrl(), PROTO_CURRENT );
				$result['thumbwidth'] = $mto->getWidth();
				$result['thumbheight'] = $mto->getHeight();
			}
		} else {
			$result['thumberror'] = "Presumably, invalid parameters, despite validation.";
		}
		return $result;
	}

	/**
	 * @param File $file
	 * @param array $hp
	 *
	 * @return array
	 */
	protected function makeTransformOptions( $file, array $hp ) {
		// Validate the input parameters like Parser::makeImage()
		$handler = $file->getHandler();
		if ( !$handler ) {
			return []; // will get iconThumb()
		}
		foreach ( $hp as $name => $value ) {
			if ( !$handler->validateParam( $name, $value ) ) {
				unset( $hp[$name] );
			}
		}

		// This part is similar to Linker::makeImageLink(). If there is no width,
		// set one based on the source file size.
		$page = $hp['page'] ?? 1;
		if ( !isset( $hp['width'] ) ) {
			if ( isset( $hp['height'] ) && $file->isVectorized() ) {
				// If it's a vector image, and user only specifies height
				// we don't want it to be limited by its "normal" width.
				global $wgSVGMaxSize;
				$hp['width'] = $wgSVGMaxSize;
			} else {
				$hp['width'] = $file->getWidth( $page );
			}

			// We don't need to fill in a default thumbnail width here, since
			// that is done by Parsoid. Parsoid always sets the width parameter
			// for thumbnails.
		}

		return $hp;
	}

	public function isInternal() {
		return true;
	}

	public function getAllowedParams() {
		return [
			'batch' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			]
		];
	}
}
