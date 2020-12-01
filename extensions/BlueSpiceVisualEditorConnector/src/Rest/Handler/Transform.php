<?php

namespace BlueSpice\VisualEditorConnector\Rest\Handler;

use MediaWiki\MediaWikiServices;
use MediaWiki\Rest\Handler;
use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\Response;
use MWParsoid\Config\PageConfigFactory;
use MWParsoid\ParsoidServices;
use RequestContext;
use Throwable;
use Title;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\Parsoid\Config\PageConfig;
use Wikimedia\Parsoid\Parsoid;

class Transform extends Handler {
	/** @var Parsoid */
	protected $parsoid;
	/** @var PageConfigFactory */
	protected $pageConfigFactory;

	/**
	 * @return static
	 */
	public static function factory() {
		$services = MediaWikiServices::getInstance();
		$parsoidServices = new ParsoidServices( $services );
		return new static(
			new Parsoid(
				$parsoidServices->getParsoidSiteConfig(),
				$parsoidServices->getParsoidDataAccess()
			),
			$parsoidServices->getParsoidPageConfigFactory()
		);
	}

	/**
	 * @param Parsoid $parsoid
	 * @param PageConfigFactory $pageConfigFactory
	 */
	public function __construct( Parsoid $parsoid, PageConfigFactory $pageConfigFactory ) {
		$this->parsoid = $parsoid;
		$this->pageConfigFactory = $pageConfigFactory;
	}

	/**
	 * @return Response
	 * @throws HttpException
	 */
	public function execute() {
		$params = $this->getValidatedParams();
		$from = $params['from'];
		$to = $params['to'];

		try {
			$this->verifyFromTo( $from, $to );

			$content = $params['content'] ?? '';
			if ( $from === 'html' ) {
				$res = $this->parsoid->html2wikitext( $this->getPageConfig( '' ), $content );
			} else {
				$res = $this->parsoid->wikitext2html( $this->getPageConfig( $content ) );
			}

			return $this->getResponseFactory()->createJson( [ 'transformed' => $res ] );
		} catch ( Throwable $ex ) {
			return $this->getResponseFactory()->createHttpError( '500', [
				'message' => $ex->getMessage()
			] );
		}
	}

	/**
	 * Get page config for content
	 *
	 * @param string $content
	 * @return PageConfig
	 */
	private function getPageConfig( $content ) {
		$user = RequestContext::getMain()->getUser();
		return $this->pageConfigFactory->create(
			Title::newFromText( 'BSVECTransform' ),
			$user,
			null,
			$content
		);
	}

	/**
	 * @return array
	 */
	public function getParamSettings() {
		return [
			'from' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => [ 'html', 'wikitext' ],
				ParamValidator::PARAM_REQUIRED => true,
			],
			'to' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => [ 'html', 'wikitext' ],
				ParamValidator::PARAM_REQUIRED => true,
			],
			'content' => [
				self::PARAM_SOURCE => 'post',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			]
		];
	}

	private function verifyFromTo( $from, $to ) {
		$allowed = [ 'html', 'wikitext' ];
		$res = array_diff( $allowed, [ $from, $to ] );
		if ( !empty( $res ) ) {
			throw new HttpException( 'Parameters "from" and "to" must be different' );
		}
	}
}
