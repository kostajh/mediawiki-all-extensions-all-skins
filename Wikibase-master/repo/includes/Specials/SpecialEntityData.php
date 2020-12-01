<?php

namespace Wikibase\Repo\Specials;

use HtmlCacheUpdater;
use HttpError;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\Repo\LinkedData\EntityDataFormatProvider;
use Wikibase\Repo\LinkedData\EntityDataRequestHandler;
use Wikibase\Repo\LinkedData\EntityDataSerializationService;
use Wikibase\Repo\WikibaseRepo;

/**
 * Special page to act as a data endpoint for the linked data web.
 * The web server should generally be configured to make this accessible via a canonical URL/URI,
 * such as <http://my.domain.org/entity/Q12345>.
 *
 * Note that this is implemented as a special page and not a per-page action, so there is no need
 * for the web server to map ID prefixes to wiki namespaces.
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 * @author Thomas Pellissier Tanon
 * @author Anja Jentzsch < anja.jentzsch@wikimedia.de >
 */
class SpecialEntityData extends SpecialWikibasePage {

	public const SPECIAL_PAGE_NAME = 'EntityData';

	/**
	 * @var EntityDataRequestHandler
	 */
	private $requestHandler;

	/**
	 * @var EntityDataFormatProvider
	 */
	private $entityDataFormatProvider;

	public function __construct(
		EntityDataRequestHandler $requestHandler,
		EntityDataFormatProvider $entityDataFormatProvider
	) {
		parent::__construct( self::SPECIAL_PAGE_NAME );

		$this->requestHandler = $requestHandler;
		$this->entityDataFormatProvider = $entityDataFormatProvider;
	}

	public static function factory(
		HtmlCacheUpdater $htmlCacheUpdater,
		EntityIdParser $entityIdParser
	): self {
		global $wgUseCdn, $wgApiFrameOptions;

		$wikibaseRepo = WikibaseRepo::getDefaultInstance();

		$entityDataFormatProvider = $wikibaseRepo->getEntityDataFormatProvider();

		$entityRevisionLookup = $wikibaseRepo->getEntityRevisionLookup();
		$entityRedirectLookup = $wikibaseRepo->getStore()->getEntityRedirectLookup();

		$serializationService = new EntityDataSerializationService(
			$wikibaseRepo->getStore()->getEntityLookup(),
			$wikibaseRepo->getEntityContentFactory(),
			$wikibaseRepo->getPropertyDataTypeLookup(),
			$wikibaseRepo->getValueSnakRdfBuilderFactory(),
			$wikibaseRepo->getEntityRdfBuilderFactory(),
			$entityDataFormatProvider,
			$wikibaseRepo->getCompactBaseDataModelSerializerFactory(),
			$wikibaseRepo->getCompactEntitySerializer(),
			$wikibaseRepo->getSiteLookup(),
			$wikibaseRepo->getRdfVocabulary()
		);

		$maxAge = $wikibaseRepo->getSettings()->getSetting( 'dataCdnMaxAge' );
		$formats = $entityDataFormatProvider->getAllowedFormats();

		$defaultFormat = empty( $formats ) ? 'html' : $formats[0];

		$entityDataRequestHandler = new EntityDataRequestHandler(
			$wikibaseRepo->getEntityDataUriManager(),
			$htmlCacheUpdater,
			$entityIdParser,
			$entityRevisionLookup,
			$entityRedirectLookup,
			$serializationService,
			$entityDataFormatProvider,
			$wikibaseRepo->getLogger(),
			$wikibaseRepo->getSettings()->getSetting( 'entityTypesWithoutRdfOutput' ),
			$defaultFormat,
			$maxAge,
			$wgUseCdn,
			$wgApiFrameOptions
		);

		return new self( $entityDataRequestHandler, $entityDataFormatProvider );
	}

	/**
	 * @see SpecialWikibasePage::execute
	 *
	 * @param string|null $subPage
	 *
	 * @throws HttpError
	 */
	public function execute( $subPage ) {
		// If there is no ID, show an HTML form
		// TODO: Don't do this if HTML is not acceptable according to HTTP headers.
		if ( !$this->requestHandler->canHandleRequest( $subPage, $this->getRequest() ) ) {
			$this->showForm();
			return;
		}

		$this->requestHandler->handleRequest( $subPage, $this->getRequest(), $this->getOutput() );
	}

	/**
	 * Shows an informative page to the user; Called when there is no entity to output.
	 */
	public function showForm() {
		//TODO: show input form with selector for format and field for ID. Add some explanation,
		//      point to meta-info like schema and license, and generally be a helpful data endpoint.
		$supportedFormats = $this->entityDataFormatProvider->getSupportedExtensions();
		$supportedFormats[] = 'html';
		$this->getOutput()->showErrorPage(
			'wikibase-entitydata-title',
			'wikibase-entitydata-text',
			[ $this->getOutput()->getLanguage()->commaList( $supportedFormats ) ]
		);
	}

}
