<?php

namespace Wikibase\Lexeme\MediaWiki\Api;

use ApiMain;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\Lexeme\Domain\Model\Form;
use Wikibase\Lexeme\MediaWiki\Api\Error\FormNotFound;
use Wikibase\Lexeme\Presentation\ChangeOp\Deserialization\FormIdDeserializer;
use Wikibase\Lexeme\Serialization\FormSerializer;
use Wikibase\Lexeme\WikibaseLexemeServices;
use Wikibase\Lib\Store\EntityRevisionLookup;
use Wikibase\Lib\Store\EntityStore;
use Wikibase\Lib\Store\LookupConstants;
use Wikibase\Lib\Summary;
use Wikibase\Repo\Api\ApiErrorReporter;
use Wikibase\Repo\ChangeOp\ChangeOpException;
use Wikibase\Repo\ChangeOp\ChangeOpValidationException;
use Wikibase\Repo\EditEntity\MediawikiEditEntityFactory;
use Wikibase\Repo\Store\Store;
use Wikibase\Repo\SummaryFormatter;
use Wikibase\Repo\WikibaseRepo;

/**
 * @license GPL-2.0-or-later
 */
class EditFormElements extends \ApiBase {

	const LATEST_REVISION = 0;

	/**
	 * @var EntityRevisionLookup
	 */
	private $entityRevisionLookup;

	/**
	 * @var MediawikiEditEntityFactory
	 */
	private $editEntityFactory;

	/**
	 * @var EditFormElementsRequestParser
	 */
	private $requestParser;

	/**
	 * @var SummaryFormatter
	 */
	private $summaryFormatter;

	/**
	 * @var FormSerializer
	 */
	private $formSerializer;

	/**
	 * @var ApiErrorReporter
	 */
	private $errorReporter;

	/**
	 * @var EntityStore
	 */
	private $entityStore;

	public static function factory(
		ApiMain $mainModule,
		string $moduleName,
		EntityIdParser $entityIdParser
	): self {
		$wikibaseRepo = WikibaseRepo::getDefaultInstance();
		$apiHelperFactory = $wikibaseRepo->getApiHelperFactory( $mainModule->getContext() );

		$serializerFactory = $wikibaseRepo->getBaseDataModelSerializerFactory();

		$formSerializer = new FormSerializer(
			$serializerFactory->newTermListSerializer(),
			$serializerFactory->newStatementListSerializer()
		);

		return new self(
			$mainModule,
			$moduleName,
			$wikibaseRepo->getEntityRevisionLookup( Store::LOOKUP_CACHING_DISABLED ),
			$wikibaseRepo->newEditEntityFactory( $mainModule->getContext() ),
			new EditFormElementsRequestParser(
				new FormIdDeserializer( $entityIdParser ),
				WikibaseLexemeServices::getEditFormChangeOpDeserializer()
			),
			$wikibaseRepo->getSummaryFormatter(),
			$formSerializer,
			function ( $module ) use ( $apiHelperFactory ) {
				return $apiHelperFactory->getErrorReporter( $module );
			},
			$wikibaseRepo->getEntityStore()
		);
	}

	public function __construct(
		ApiMain $mainModule,
		$moduleName,
		EntityRevisionLookup $entityRevisionLookup,
		MediawikiEditEntityFactory $editEntityFactory,
		EditFormElementsRequestParser $requestParser,
		SummaryFormatter $summaryFormatter,
		FormSerializer $formSerializer,
		callable $errorReporterInstantiator,
		EntityStore $entityStore
	) {
		parent::__construct( $mainModule, $moduleName );

		$this->entityRevisionLookup = $entityRevisionLookup;
		$this->editEntityFactory = $editEntityFactory;
		$this->requestParser = $requestParser;
		$this->summaryFormatter = $summaryFormatter;
		$this->formSerializer = $formSerializer;
		$this->errorReporter = $errorReporterInstantiator( $this );
		$this->entityStore = $entityStore;
	}

	/**
	 * @inheritDoc
	 * @suppress PhanTypeMismatchArgument
	 */
	public function execute() {
		$params = $this->extractRequestParams();
		$request = $this->requestParser->parse( $params );
		if ( $request->getBaseRevId() ) {
			$baseRevId = $request->getBaseRevId();
		} else {
			$baseRevId = self::LATEST_REVISION;
		}

		$formId = $request->getFormId();

		$formRevision = $this->entityRevisionLookup->getEntityRevision(
			$formId,
			self::LATEST_REVISION,
			LookupConstants::LATEST_FROM_MASTER
		);

		if ( $formRevision === null ) {
			$error = new FormNotFound( $formId );
			$this->dieWithError( $error->asApiMessage( EditFormElementsRequestParser::PARAM_FORM_ID, [] ) );
		}

		$baseRevId = $this->getRevIdForWhenUserWasLastToEdit(
			$formRevision->getRevisionId(),
			$baseRevId,
			$formId->getLexemeId()
		);

		$form = $formRevision->getEntity();

		$changeOp = $request->getChangeOp();

		$result = $changeOp->validate( $form );
		if ( !$result->isValid() ) {
			$this->errorReporter->dieException(
				new ChangeOpValidationException( $result ),
				'modification-failed'
			);
		}

		$summary = new Summary();
		try {
			$changeOp->apply( $form, $summary );
		} catch ( ChangeOpException $exception ) {
			$this->errorReporter->dieException( $exception,  'unprocessable-request' );
		}

		$summaryString = $this->summaryFormatter->formatSummary( $summary );

		$status = $this->saveForm( $form, $summaryString, $baseRevId, $params );

		if ( !$status->isOK() ) {
			$this->dieStatus( $status );
		}

		$this->generateResponse( $form );
	}

	/**
	 * @param Form $form
	 * @param string $summary
	 * @param int $baseRevisionId
	 * @param array $params
	 * @return \Status
	 */
	private function saveForm(
		Form $form,
		$summary,
		$baseRevisionId,
		array $params
	) {
		$editEntity = $this->editEntityFactory->newEditEntity(
			$this->getUser(),
			$form->getId(),
			$baseRevisionId
		);

		// TODO: bot flag should probably be part of the request
		$flags = EDIT_UPDATE;
		if ( isset( $params['bot'] ) && $params['bot'] &&
			$this->getPermissionManager()->userHasRight( $this->getUser(), 'bot' )
		) {
			$flags |= EDIT_FORCE_BOT;
		}

		$tokenThatDoesNotNeedChecking = false;
		return $editEntity->attemptSave(
			$form,
			$summary,
			$flags,
			$tokenThatDoesNotNeedChecking
		);
	}

	/**
	 * @param Form $form
	 */
	private function generateResponse( Form $form ) {
		$apiResult = $this->getResult();

		$serializedForm = $this->formSerializer->serialize( $form );

		// TODO: Do we really need `success` property in response?
		$apiResult->addValue( null, 'success', 1 );
		$apiResult->addValue( null, 'form', $serializedForm );
	}

	/**
	 * @see ApiBase::getAllowedParams
	 */
	protected function getAllowedParams() {
		return [
			EditFormElementsRequestParser::PARAM_FORM_ID => [
				self::PARAM_TYPE => 'string',
				self::PARAM_REQUIRED => true,
			],
			EditFormElementsRequestParser::PARAM_DATA => [
				self::PARAM_TYPE => 'text',
				self::PARAM_REQUIRED => true,
			],
			'bot' => [
				self::PARAM_TYPE => 'boolean',
				self::PARAM_DFLT => false,
			],
			EditFormElementsRequestParser::PARAM_BASEREVID => [
				self::PARAM_TYPE => 'integer',
			]
		];
	}

	/**
	 * @see ApiBase::isWriteMode()
	 */
	public function isWriteMode() {
		return true;
	}

	/**
	 * As long as this codebase is in development and APIs might change any time without notice, we
	 * mark all as internal. This adds an "unstable" notice, but does not hide them in any way.
	 */
	public function isInternal() {
		return true;
	}

	/**
	 * @see ApiBase::needsToken()
	 */
	public function needsToken() {
		return 'csrf';
	}

	/**
	 * @see ApiBase::mustBePosted()
	 */
	public function mustBePosted() {
		return true;
	}

	protected function getExamplesMessages() {
		$formId = 'L12-F1';
		$exampleData = [
			'representations' => [
				'en-US' => [ 'value' => 'color', 'language' => 'en-US' ],
				'en-GB' => [ 'value' => 'colour', 'language' => 'en-GB' ],
			],
			'grammaticalFeatures' => [
				'Q1', 'Q2'
			]
		];

		$query = http_build_query( [
			'action' => $this->getModuleName(),
			EditFormElementsRequestParser::PARAM_FORM_ID => $formId,
			EditFormElementsRequestParser::PARAM_DATA => json_encode( $exampleData )
		] );

		$languages = array_map( function ( $r ) {
			return $r['language'];
		}, $exampleData['representations'] );
		$representations = array_map( function ( $r ) {
			return $r['value'];
		}, $exampleData['representations'] );

		$representationsText = $this->getLanguage()->commaList( $representations );
		$languagesText = $this->getLanguage()->commaList( $languages );
		$grammaticalFeaturesText = $this->getLanguage()->commaList( $exampleData['grammaticalFeatures'] );

		$exampleMessage = new \Message(
			'apihelp-wbleditformelements-example-1',
			[
				$formId,
				$representationsText,
				$languagesText,
				$grammaticalFeaturesText
			]
		);

		return [
			urldecode( $query ) => $exampleMessage
		];
	}

	/**
	 * Returns $latestRevisionId if all of edits since $baseRevId are done
	 * by the same user, otherwise returns $baseRevId.
	 *
	 * @param int $latestRevisionId
	 * @param int $baseRevId
	 * @param EntityId $entityId
	 * @return int
	 */
	private function getRevIdForWhenUserWasLastToEdit(
		$latestRevisionId,
		$baseRevId,
		EntityId $entityId
	) {
		if ( $baseRevId === self::LATEST_REVISION || $latestRevisionId === $baseRevId ) {
			return $latestRevisionId;
		}

		$userWasLastToEdit = $this->entityStore->userWasLastToEdit(
			$this->getUser(),
			$entityId,
			$baseRevId
		);
		if ( $userWasLastToEdit ) {
			return $latestRevisionId;
		}

		return $baseRevId;
	}

}
