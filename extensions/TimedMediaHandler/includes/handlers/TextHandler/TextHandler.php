<?php
/**
 * Timed Text handling for mediaWiki
 *
 * Timed text support is presently fairly limited. Unlike Ogg and WebM handlers,
 * timed text does not extend the TimedMediaHandler class.
 *
 * TODO On "new" timedtext language save purge all pages where file exists
 */

use MediaWiki\MediaWikiServices;
use MediaWiki\TimedMediaHandler\TimedText\ParseError;
use MediaWiki\TimedMediaHandler\TimedText\SrtReader;
use MediaWiki\TimedMediaHandler\TimedText\SrtWriter;
use MediaWiki\TimedMediaHandler\TimedText\VttWriter;
use Wikimedia\Rdbms\IResultWrapper;

class TextHandler {
	// lazy init remote Namespace number
	public $remoteNs = null;
	public $remoteNsName = null;

	/**
	 * @var File
	 */
	protected $file;

	/**
	 * @var array of string keys for subtitle formats
	 */
	protected $formats;

	public function __construct( $file, $formats = [ 'vtt', 'srt' ] ) {
		$this->file = $file;
		$this->formats = $formats;
	}

	/**
	 * Get the timed text tracks elements as an associative array
	 * @return array[]
	 */
	public function getTracks() {
		if ( $this->file->isLocal() ) {
			return $this->getLocalDbTextSources();
		} elseif ( $this->file instanceof ForeignDBFile ) {
			return $this->getForeignDbTextSources();
		} elseif ( $this->file instanceof ForeignAPIFile ) {
			return $this->getRemoteTextSources( $this->file );
		} else {
			return [];
		}
	}

	/**
	 * @return bool|int
	 */
	public function getTimedTextNamespace() {
		global $wgEnableLocalTimedText;

		$repo = $this->file->getRepo();

		if ( $this->file->isLocal() ) {
			if ( $wgEnableLocalTimedText ) {
				return NS_TIMEDTEXT;
			}
		} elseif ( $repo instanceof ForeignDBViaLBRepo ) {
			global $wgTimedTextForeignNamespaces;
			$wikiID = $repo->getReplicaDB()->getDomainID();
			if ( isset( $wgTimedTextForeignNamespaces[ $wikiID ] ) ) {
				return $wgTimedTextForeignNamespaces[ $wikiID ];
			}
			// failed to get namespace via ForeignDBViaLBRepo, return NS_TIMEDTEXT
			if ( $wgEnableLocalTimedText ) {
				return NS_TIMEDTEXT;
			}
		} elseif ( $repo instanceof ForeignAPIRepo ) {
			if ( $this->remoteNs !== null ) {
				return $this->remoteNs;
			}

			// Get the namespace data from the image api repo:
			// fetchImageQuery query caches results
			$data = $repo->fetchImageQuery( [
				'meta' => 'siteinfo',
				'siprop' => 'namespaces'
			] );

			if ( isset( $data['query'] ) && isset( $data['query']['namespaces'] ) ) {
				// get the ~last~ timed text namespace defined
				foreach ( $data['query']['namespaces'] as $ns ) {
					if ( isset( $ns['canonical'] ) && $ns['canonical'] === 'TimedText' ) {
						$this->remoteNs = $ns['id'];
						$this->remoteNsName = $ns['*'];
						wfDebug( "Discovered remoteNs: $this->remoteNs and name: $this->remoteNsName \n" );
						break;
					}
				}
			}

			// Return the remote Ns, if found
			if ( $this->remoteNs !== null ) {
				return $this->remoteNs;
			}
		}

		return false;
	}

	/**
	 * Retrieve a list of TimedText pages in the database that start with
	 * the name of the file associated with this handler.
	 *
	 * If the file is on a foreign repo, will query the ForeignDb
	 *
	 * @return IResultWrapper|bool
	 */
	private function getTextPagesFromDb() {
		$ns = $this->getTimedTextNamespace();
		if ( $ns === false ) {
			wfDebug( 'Repo: ' . $this->file->getRepoName() . " does not have a TimedText namespace \n" );
			// No timed text namespace, don't try to look up timed text tracks
			return false;
		}

		$repo = $this->file->getRepo();
		if ( $repo instanceof LocalRepo ||
				$repo instanceof ForeignDBViaLBRepo ||
				$repo instanceof ForeignDBRepo ) {
			$dbr = $repo->getReplicaDB();
			$prefix = $this->file->getTitle()->getDBkey();
			return $dbr->select(
				'page',
				[ 'page_namespace', 'page_title' ],
				[
					'page_namespace' => $ns,
					'page_title ' . $dbr->buildLike( $prefix, $dbr->anyString() )
				],
				__METHOD__,
				[
					'LIMIT' => 300,
					'ORDER BY' => 'page_title'
				]
			);
		}

		return false;
	}

	/**
	 * Build the api query to find TimedText pages belonging to a remote file
	 * @return array|bool
	 */
	public function getRemoteTextPagesQuery() {
		$ns = $this->getTimedTextNamespace();
		if ( $ns === false ) {
			wfDebug( 'Repo: ' . $this->file->getRepoName() . " does not have a TimedText namespace \n" );
			// No timed text namespace, don't try to look up timed text tracks
			return false;
		}
		return [
			'action' => 'query',
			'titles' => $this->file->getTitle()->getPrefixedDBkey(),
			'prop' => 'videoinfo',
			'viprop' => 'timedtext',
			'formatversion' => '2',
		];
	}

	/**
	 * Retrieve the text sources belonging to a remote file
	 * @param ForeignAPIFile $file
	 * @return array[]
	 */
	private function getRemoteTextSources( ForeignAPIFile $file ) {
		$regenerator = function () use ( $file ) {
			$repo = $file->getRepo();
			wfDebug( "Get text tracks from remote api \n" );
			$query = $this->getRemoteTextPagesQuery();
			// Error in getting timed text namespace return empty array;
			if ( $query === false || !( $repo instanceof ForeignAPIRepo ) ) {
				return [];
			}

			$data = $file->getRepo()->fetchImageQuery( $query );

			return $this->getTextTracksFromData( $data );
		};

		$repoInfo = $file->getRepo()->getInfo();
		$cacheTTL = $repoInfo['descriptionCacheExpiry'] ?? 0;

		if ( $cacheTTL > 0 ) {
			$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
			$textTracks = $cache->getWithSetCallback(
				$cache->makeKey(
					'RemoteTextTracks-url',
					$this->file->getRepoName(),
					$this->file->getName()
				),
				$cacheTTL,
				$regenerator
			);
		} else {
			$textTracks = $regenerator();
		}

		return $textTracks;
	}

	/**
	 * Retrieve the text sources belonging to a foreign db accessible file
	 * @return array[]
	 */
	public function getForeignDbTextSources() {
		$data = $this->getTextPagesFromDb();
		if ( $data !== false ) {
			return $this->getTextTracksFromRows( $data );
		}
		return [];
	}

	/**
	 * Retrieve the text sources belonging to a local file
	 * @return array[]
	 */
	public function getLocalDbTextSources() {
		global $wgEnableLocalTimedText;
		if ( $wgEnableLocalTimedText ) {
			$data = $this->getTextPagesFromDb();
			if ( $data !== false ) {
				return $this->getTextTracksFromRows( $data );
			}
		}
		return [];
	}

	/**
	 * Build an array of track information using a Database result
	 * Handles both local and foreign Db results
	 *
	 * @param IResultWrapper $data Database result with page titles
	 * @return array[]
	 */
	public function getTextTracksFromRows( IResultWrapper $data ) {
		$textTracks = [];

		if ( !$this->file->isLocal() ) {
			$namespaceName = $this->getForeignNamespaceName();
		}
		$langNames = Language::fetchLanguageNames( null, 'mw' );

		foreach ( $data as $row ) {
			// Note, the namespace ID of this title might be 'unknown'
			// to our configuration if this is called in ForeignDb situations
			if ( $this->file->isLocal() ) {
				$subTitle = Title::newFromRow( $row );
			} else {
				// @phan-suppress-next-next-line PhanTypeMismatchArgumentNullable $namespaceName is set
				// @phan-suppress-next-line PhanPossiblyUndeclaredVariable $namespaceName is set
				$subTitle = new ForeignTitle( $row->page_namespace, $namespaceName, $row->page_title );
			}
			$titleParts = explode( '.', $row->page_title );
			if ( count( $titleParts ) >= 3 ) {
				$timedTextExtension = array_pop( $titleParts );
				$languageKey = array_pop( $titleParts );
				$contentType = $this->getContentType( $timedTextExtension );
			} else {
				continue;
			}
			// If there is no valid language continue:
			if ( !isset( $langNames[ $languageKey ] ) ) {
				continue;
			}

			$language = Language::factory( $languageKey );
			foreach ( $this->formats as $format ) {
				$textTracks[] = [
					'src' => $this->getFullURL( $languageKey, $format ),
					'kind' => 'subtitles',
					'type' => $this->getContentType( $format ),
					'srclang' => $language->getHtmlCode(),
					'dir' => $language->getDir(),
					'label' => wfMessage( 'timedmedia-subtitle-language',
						$langNames[ $languageKey ],
						$languageKey )->text()
				];
			}
		}
		return $textTracks;
	}

	/**
	 * Build an array of track information using an API result
	 * @param mixed $data JSON decoded result from a query API request
	 * @return array[]
	 */
	public function getTextTracksFromData( $data ) {
		$textTracks = [];
		foreach ( $data['query']['pages'] ?? [] as $page ) {
			foreach ( $page['videoinfo'] ?? [] as $info ) {
				foreach ( $info['timedtext'] ?? [] as $track ) {
					foreach ( $this->formats as $format ) {
						if ( ( $track['type'] ?? '' ) === $this->getContentType( $format ) ) {
							// Add validation ?
							$textTracks[] = $track;
						}
					}
				}
			}
		}
		return $textTracks;
	}

	public function getContentType( $timedTextExtension ) {
		if ( $timedTextExtension === 'srt' ) {
			return 'text/x-srt';
		} elseif ( $timedTextExtension === 'vtt' ) {
			return 'text/vtt';
		}
		return '';
	}

	public function getForeignNamespaceName() {
		global $wgEnableLocalTimedText;
		if ( $this->remoteNs !== null ) {
			return $this->remoteNsName;
		}
		/* Else, we use the canonical namespace, since we can't look up the actual one */
		if ( $wgEnableLocalTimedText ) {
			return strtr( MWNamespace::getCanonicalName( NS_TIMEDTEXT ), ' ', '_' );
		} else {
			// Assume the default name if no local TimedText.
			return 'TimedText';
		}
	}

	/**
	 * Retrieve a url to the raw subtitle file
	 * Only use for local and foreignDb requests
	 *
	 * @param string $lang
	 * @param string $format
	 * @return string
	 */
	public function getFullURL( $lang, $format ) {
		$title = $this->file->getTitle();
		// Note we need to use the canonical namespace in case this is a
		// foreign DB repo (Wikimedia Commons style) in a different language
		// than the current site.
		$canonicalTitle = Title::makeName(
			$title->getNamespace(),
			$title->getDbKey(),
			'', // fragment
			'', // interwiki
			true // canonical namespace
		);
		$params = [
			'action' => 'timedtext',
			'title' => $canonicalTitle,
			'lang' => $lang,
			'trackformat' => $format,
		];
		if ( !$this->file->isLocal() ) {
			$params['origin'] = '*';
		}
		$query = wfArrayToCgi( $params );

		// Note: This will return false if scriptDirUrl is not set for repo.
		return $this->file->getRepo()->makeUrl( $query, 'api' );
	}

	/**
	 * Convert subtitles between SubRIP (SRT) and WebVTT, laxly.
	 *
	 * @param string $from source format, one of 'srt' or 'vtt'
	 * @param string $to destination format, one of 'srt' or 'vtt'
	 * @param string $data source-formatted subtitles
	 * @param ParseError[] &$errors optional outparam to capture errors
	 * @return string destination-formatted subtitles
	 */
	public static function convertSubtitles( $from, $to, $data, &$errors = [] ) {
		// Note that we convert even if the format is the same, to ensure
		// data format integrity.
		//
		// @todo cache the conversion in memcached
		switch ( $from ) {
			case 'srt':
				$reader = new SrtReader();
				break;
			case 'vtt':
				// @todo once VttReader is implemented, use it.
				// For now throw an exception rather than a fatal error.
				throw new MWException( 'vtt source pages are not yet supported' );
			default:
				throw new MWException( 'Unsupported timedtext filetype' );
		}
		switch ( $to ) {
			case 'srt':
				$writer = new SrtWriter();
				break;
			case 'vtt':
				$writer = new VttWriter();
				break;
			default:
				throw new MWException( 'Unsupported timedtext filetype' );
		}
		try {
			$reader->read( $data );
			$cues = $reader->getCues();
			$errors = $reader->getErrors();

			$newFile = $writer->write( $cues );
			return $newFile;
		} catch ( Exception $e ) {
			throw new MWException( 'Timed text track conversion failed: ' .
				$e->getMessage() );
		}
	}
}
