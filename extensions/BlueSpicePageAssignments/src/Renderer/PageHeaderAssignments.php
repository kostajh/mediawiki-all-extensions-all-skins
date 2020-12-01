<?php

namespace BlueSpice\PageAssignments\Renderer;

use BlueSpice\PageAssignments\IAssignment;
use BlueSpice\PageHeaderBeforeContentFactory;
use BlueSpice\Renderer;
use BlueSpice\Renderer\Params;
use Config;
use Exception;
use Html;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;
use QuickTemplate;
use RequestContext;

class PageHeaderAssignments extends Renderer {

	public const SKIN_TEMPLATE = 'skintemplate';
	/**
	 *
	 * @var PageHeaderBeforeContentFactory
	 */
	protected $factory = null;

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 * @param QuickTemplate|null $skinTemplate
	 * @param PageHeaderBeforeContentFactory|null $factory
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '', QuickTemplate $skinTemplate = null, PageHeaderBeforeContentFactory $factory = null ) {
		parent::__construct( $config, $params, $linkRenderer, $context, $name, $skinTemplate );

		$this->factory = $factory;
	}

	/**
	 *
	 * @param string $name
	 * @param MediaWikiServices $services
	 * @param Config $config
	 * @param Params $params
	 * @param IContextSource|null $context
	 * @param LinkRenderer|null $linkRenderer
	 * @param QuickTemplate|null $skinTemplate
	 * @param PageHeaderBeforeContentFactory|null $factory
	 * @return Renderer
	 */
	public static function factory( $name, MediaWikiServices $services, Config $config,
		Params $params, IContextSource $context = null, LinkRenderer $linkRenderer = null,
		QuickTemplate $skinTemplate = null, PageHeaderBeforeContentFactory $factory = null ) {
		if ( !$context ) {
			$context = $params->get(
				static::PARAM_CONTEXT,
				false
			);
			if ( !$context instanceof IContextSource ) {
				$context = RequestContext::getMain();
			}
		}
		if ( !$linkRenderer ) {
			$linkRenderer = $services->getLinkRenderer();
		}
		if ( !$factory ) {
			$factory = $services->getService( 'BSPageHeaderBeforeContentFactory' );
		}
		if ( !$skinTemplate ) {
			$skinTemplate = $params->get( static::SKIN_TEMPLATE, null );
		}
		if ( !$skinTemplate ) {
			throw new Exception(
				'Param "' . static::SKIN_TEMPLATE . '" must be an instance of '
				. QuickTemplate::class
			);
		}
		return new static(
			$config,
			$params,
			$linkRenderer,
			$context,
			$name,
			$skinTemplate,
			$factory
		);
	}

	/**
	 * @return string
	 */
	public function render() {
		if ( $this->skipProcessing() === true ) {
			return '';
		}

		$limit = $this->config->get( 'PageAssignmentsPageHeaderLimit' );

		$factory = MediaWikiServices::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);

		$target = $factory->newFromTargetTitle( $this->context->getTitle() );

		$assignments = '';
		$assignments .= Html::openElement(
				'ul',
				[
					'class' => 'pageheader-assignments-list'
				]
			);

		$assignments .= Html::openElement(
			'li',
			[
				'class' => 'pageheader-assignments-label'
			]
		);
		$assignments .= Html::element( 'i', [ 'class' => 'bs-icon-profile' ] );
		$assignments .= Html::element(
				'span',
				[],
				wfMessage( "bs-pageassignments-pageheader-label" )->text()
			);
		$assignments .= Html::closeElement( 'li' );

		$assignments .= Html::openElement(
			'li',
			[
				'class' => 'pageheader-assignments-list-items'
			]
		);
		$assignments .= Html::openElement( 'ul' );

		$cnt = 0;
		foreach ( $target->getAssignments() as $assignment ) {
			if ( $cnt === $limit ) {
				continue;
			}

			$assignments .= $this->makeEntry( $assignment );
			$cnt++;
		}

		$assignments .= Html::closeElement( 'ul' );
		$assignments .= Html::closeElement( 'li' );

		$assignments .= Html::closeElement( 'ul' );

		return $assignments;
	}

	/**
	 *
	 * @param IAssignment $assignment
	 * @return \stdClass
	 */
	protected function makeEntry( IAssignment $assignment ) {
		$html = '';

		$type = $assignment->getType();

		$factory = MediaWikiServices::getInstance()->getService(
			'BSPageAssignmentsAssignableFactory'
		);

		$template = 'pageheader-assignments-base';
		if ( $type === 'user' ) {
			$template = 'pageheader-assignments-user';
		}

		$renderer = MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			$template,
			new \BlueSpice\Renderer\Params( [
				Assignment::PARAM_ASSIGNMENT => $assignment,
				Assignment::PARAM_TAG => 'li',
				Assignment::PARAM_CLASS => 'pageheader-assignments-item'
			] )
		);
		$html = $renderer->render();

		return $html;
	}

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( $this->context->getTitle()->getArticleID() < 1 ) {
			return true;
		}

		$factory = MediaWikiServices::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);

		$target = $factory->newFromTargetTitle( $this->context->getTitle() );

		if ( !$target ) {
			return true;
		}

		$assignments = $target->getAssignments();
		if ( count( $assignments ) < 1 ) {
			return true;
		}

		return false;
	}
}
