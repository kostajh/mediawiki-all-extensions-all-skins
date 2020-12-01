<?php

namespace BlueSpice\Calumma\Renderer\PageHeader;

use BlueSpice\Calumma\PageInfoSentenceBuilder;
use BlueSpice\Calumma\Renderer\PageHeader;
use BlueSpice\PageInfoElementFactory;
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

class PageInfo extends PageHeader {

	/**
	 *
	 * @var PageInfoElementFactory
	 */
	protected $factory = null;

	/**
	 *
	 * @var PageInfoSentenceBuilder
	 */
	protected $builder = null;

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 * @param QuickTemplate|null $skinTemplate
	 * @param PageInfoElementFactory|null $factory
	 * @param PageInfoSentenceBuilder|null $builder
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '', QuickTemplate $skinTemplate = null, PageInfoElementFactory $factory = null,
		PageInfoSentenceBuilder $builder = null ) {
		parent::__construct( $config, $params, $linkRenderer, $context, $name, $skinTemplate );

		$this->factory = $factory;
		$this->builder = $builder;
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
	 * @param PageInfoElementFactory|null $factory
	 * @param PageInfoSentenceBuilder|null $builder
	 * @return Renderer
	 */
	public static function factory( $name, MediaWikiServices $services, Config $config,
		Params $params, IContextSource $context = null, LinkRenderer $linkRenderer = null,
		QuickTemplate $skinTemplate = null, PageInfoElementFactory $factory = null,
		PageInfoSentenceBuilder $builder = null ) {
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
			$factory = $services->getService( 'BSPageInfoElementFactory' );
		}
		if ( !$builder ) {
			$builder = new PageInfoSentenceBuilder(
				$context
			);
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
			$factory,
			$builder
		);
	}

	/**
	 * @return string
	 */
	public function render() {
		$title = $this->getContext()->getTitle();
		if ( !$title || !$title->exists() || $title->isSpecialPage() ) {
			return '';
		}

		$elements = $this->factory->getAll( $this->getContext() );
		if ( empty( $elements ) ) {
			return '';
		}

		$html = Html::openElement(
				'li',
				[
					'class' => 'bs-page-info-element-button-col'
				]
		);
		$html .= $this->makeActionButton();
		$html .= Html::closeElement( 'li' );

		$html .= Html::openElement(
				'li',
				[
					'class' => 'bs-page-info-element-text-col'
				]
		);
		$html .= $this->builder->build(
			$elements,
			$this->getContext()->getTitle(),
			$this->getContext()->getUser()
		);
		$html .= Html::closeElement( 'li' );

		return $html;
	}

	/**
	 *
	 * @return string
	 */
	private function makeActionButton() {
		$html = '';

		$html .= Html::openElement( 'a', [
			'id' => 'pageinfo-qm-panel',
			'class' => 'bs-page-info-element-button',
			'title' => $this->msg( 'bs-calumma-pageinfo-qm-button-tooltip' )->plain(),
			'aria-label' => $this->msg( 'bs-calumma-pageinfo-qm-button-tooltip' )->plain()
		] );

		$html .= Html::element( 'i' );
		$html .= Html::closeElement( 'a' );

		return $html;
	}

}
