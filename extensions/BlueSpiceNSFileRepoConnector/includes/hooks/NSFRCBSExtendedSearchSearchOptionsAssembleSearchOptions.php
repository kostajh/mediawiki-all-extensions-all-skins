<?php

class NSFRCBSExtendedSearchSearchOptionsAssembleSearchOptions {

	/**
	 *
	 * @var IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @var SearchOptions
	 */
	protected $oSearchOptions = null;

	/**
	 *
	 * @var array
	 */
	protected $aOptions = [];

	/**
	 *
	 * @var array
	 */
	protected $aFq = [];

	/**
	 *
	 * @var array
	 */
	protected $aFacetFields = [];

	/**
	 *
	 * @param SearchOptions $oSearchOptions
	 * @param array &$aOptions
	 * @param array &$aFq
	 * @param array &$aFacetFields
	 * @return bool
	 */
	public static function handle( $oSearchOptions, &$aOptions, &$aFq, &$aFacetFields ) {
		$instance = new self(
			RequestContext::getMain(),
			new NSFileRepo\Config(),
			$oSearchOptions,
			$aOptions,
			$aFq,
			$aFacetFields
		);

		return $instance->process();
	}

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @param SearchOptions $oSearchOptions
	 * @param array &$aOptions
	 * @param array &$aFq
	 * @param array &$aFacetFields
	 */
	public function __construct( $context, $config, $oSearchOptions, &$aOptions, &$aFq,
		&$aFacetFields ) {
		$this->context = $context;
		$this->config = $config;
		$this->oSearchOptions = $oSearchOptions;
		$this->aOptions =& $aOptions;
		$this->aFq =& $aFq;
		$this->aFacetFields =& $aFacetFields;
	}

	/**
	 *
	 * @return bool
	 */
	public function process() {
		$this->fetchNotReadableNamespacePrefixes();
		$this->assembleFilterQuery();
		$this->addFilterQuery();
		return true;
	}

	protected $aPrefixes = [];

	/**
	 * We need to assemble a black list. A whitelist won't work as main
	 * namespace has no prefix!
	 */
	protected function fetchNotReadableNamespacePrefixes() {
		$aNamespaceIds = array_values(
			$this->context->getLanguage()->getNamespaceIds()
		);

		foreach ( $aNamespaceIds as $iNsId ) {
			// File uploads to talk namespaces are not possible
			if ( MWNamespace::isTalk( $iNsId ) ) {
				continue;
			}

			// We don't need namespaces below the threshold into account
			if ( $this->config->get( NSFileRepo\Config::CONFIG_THRESHOLD ) > $iNsId ) {
				continue;
			}

			// If the user can read the namespace we don't need to blacklist it
			if ( \MediaWiki\MediaWikiServices::getInstance()
				->getPermissionManager()
				->userCan(
					'read',
					$this->context->getUser(),
					Title::makeTitle( $iNsId, 'X' )
				)
			) {
				continue;
			}

			$this->aPrefixes[] = MWNamespace::getCanonicalName( $iNsId );
		}
	}

	protected $sFilterQuery = '';

	/**
	 * ATTENTION: When applying a FQ in form of "-title:(Aprefix\:*)" not only
	 * files, but also other documents with the prefix "Aprefix:" in their
	 * 'title' field will be filtered out. That means tha also a wikipage like
	 * "Aprefix:Somthing unrelated to namespace Aprefix" in the main namespace
	 * will not show up in the result.
	 * As this is very unlikely we do not handle such cases here.
	 */
	protected function assembleFilterQuery() {
		if ( empty( $this->aPrefixes ) ) {
			// Bail out to avoid invalid filter query
			return;
		}

		$aDecoratedPrefixes = array_map( function ( $element ) {
			return "$element\:*";
		},  $this->aPrefixes );

		$sOrSeperatedList = implode( ' OR ', $aDecoratedPrefixes );

		// -title:(E* OR W* OR K*)
		$this->sFilterQuery = "-title:($sOrSeperatedList)";
	}

	protected function addFilterQuery() {
		if ( !empty( $this->sFilterQuery ) ) {
			$this->aFq[] = $this->sFilterQuery;
		}
	}
}
