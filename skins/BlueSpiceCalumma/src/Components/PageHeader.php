<?php

namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\Renderer\PageHeader as PageHeaderRenderer;
use BlueSpice\Calumma\Renderer\PageHeader\Category;
use BlueSpice\Calumma\Renderer\PageHeader\LastEdit;
use BlueSpice\Calumma\TemplateComponent;
use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\Renderer\NullRenderer;
use BlueSpice\Renderer\Params;
use BlueSpice\RendererFactory;
use BlueSpice\SkinData;
use Html;
use MediaWiki\MediaWikiServices;
use Sanitizer;
use Title;
use WikiPage;
use WikiTextContent;

class PageHeader extends TemplateComponent {

	/**
	 *
	 * @return string
	 */
	protected function getTemplatePathName() {
		return 'Calumma.Components.PageHeader';
	}

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$args = parent::getTemplateArgs();

		$showContextBand = $this->showContextBand();
		$showQualificationBand = $this->showQualificationBand();
		$showMetaBand = $this->showMetaBand();

		$args = $this->getDefaultArgs();
		if ( $this->isEditMode() ) {
			$args['firstheading'] = $this->getFirstHeading();
			return $args;
		}

		$args = [
			'sitenotice' => $this->getSiteNotice(),
			'indicators' => $this->getIndicators(),
			'firstheading' => $this->getFirstHeading(),
			'lang' => $this->getPageLanguageCode(),
			'headerlinks' => $this->getHeaderLinks(),
			'breadcrumbs' => $showContextBand ? $this->getBreadCrumbs() : '',
			'namespaces' => $showContextBand ? $this->getSiteNamespaces() : '',
			'categories' => $showQualificationBand ? $this->getCategories() : '',
			'pageinfoelements' => $showMetaBand ? $this->getPageInfoElement() : '',
			'beforecontent' => $showMetaBand ? $this->getDataBeforeContent() : '',
			'lastedit' => $showMetaBand ? $this->getLastEdit() : '',
			'watchaction' => $showMetaBand ? $this->getWatchAction() : '',
			'contextband' => $showContextBand,
			'qualificationband' => $showQualificationBand,
			'metaband' => $showMetaBand

		] + $args;

		return $args;
	}

	/**
	 *
	 * @return array
	 */
	protected function getDefaultArgs() {
		return [
			'sitenotice' => '',
			'indicators' => '',
			'firstheading' => '',
			'lang' => '',
			'headerlinks' => '',
			'breadcrumbs' => '',
			'namespaces' => '',
			'categories' => '',
			'pageinfoelements' => '',
			'lastedit' => '',
			'watchaction' => '',
			'edit' => '',
			'contextband' => false,
			'qualificationband' => false,
			'metaband' => false,
			'actionband' => false
		];
	}

	/**
	 *
	 * @return string
	 */
	protected function getSiteNamespaces() {
		$params = array_merge(
			parent::getTemplateArgs(),
			[ PageHeaderRenderer::SKIN_TEMPLATE => $this->getSkinTemplate() ]
		);
		return $this->getRendererFactory()->get(
			'pageheader-context',
			new Params( $params ),
			$this->getSkin()->getContext()
		)->render();
	}

	/**
	 *
	 * @return string
	 */
	protected function getPageInfoElement() {
		$params = array_merge(
			parent::getTemplateArgs(),
			[ PageHeaderRenderer::SKIN_TEMPLATE => $this->getSkinTemplate() ]
		);
		return $this->getRendererFactory()->get(
			'pageheader-pageinfo',
			new Params( $params ),
			$this->getSkin()->getContext()
		)->render();
	}

	/**
	 * @return string
	 */
	protected function getLastEdit() {
		$params = array_merge(
			parent::getTemplateArgs(),
			[ PageHeaderRenderer::SKIN_TEMPLATE => $this->getSkinTemplate() ]
		);
		$renderer = $this->getRendererFactory()->get(
			'pageheader-lastedit',
			new Params( $params ),
			$this->getSkin()->getContext()
		);

		if ( $renderer instanceof NullRenderer ) {
			$renderer = LastEdit::factory(
				'pageheader-lastedit',
				MediaWikiServices::getInstance(),
				MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' ),
				new Params( $params )
			);
		}

		return $renderer->render();
	}

	/**
	 *
	 * @return string
	 */
	protected function getEditButton() {
		$params = parent::getTemplateArgs();
		$params[SkinData::FEATURED_ACTIONS] = $this->getSkinTemplate()->get(
			SkinData::FEATURED_ACTIONS
		);
		$params = array_merge(
			$params,
			[ PageHeaderRenderer::SKIN_TEMPLATE => $this->getSkinTemplate() ]
		);
		return $this->getRendererFactory()->get(
			'pageheader-editbutton',
			new Params( $params ),
			$this->getSkin()->getContext()
		)->render();
	}

	/**
	 *
	 * @return string
	 */
	protected function getSiteNotice() {
		return $this->getSkinTemplate()->get( 'sitenotice' );
	}

	/**
	 *
	 * @return string
	 */
	protected function getIndicators() {
		$out = '';
		$indicators = $this->getSkinTemplate()->get( 'indicators' );
		// Logic from `BaseTemplate`
		foreach ( $indicators as $id => $content ) {
			$out .= Html::rawElement(
				'div',
				[
					'id' => Sanitizer::escapeIdForAttribute( "mw-indicator-$id" ),
					'class' => 'mw-indicator',
				],
				$content
			) . "\n";
		}
		return $out;
	}

	/**
	 *
	 * @return string
	 */
	protected function getFirstHeading() {
		$titleText = $this->getSkinTemplate()->get( 'title' );
		// Might contain HTML elements e.g. by BlueSpiceRating
		// e.g. "<span>...</span>Some Page<a>...</a>"
		$strippedTitleText = strip_tags( $titleText );

		$currentTitle = $this->getSkin()->getTitle();
		$title = Title::newFromText( $strippedTitleText );
		// Only shorten if not already overwritten by another extension or `{{DISPLAYTITLE:...}}`

		if ( $title && $title->equals( $currentTitle ) ) {
			$titleText = str_replace(
				$strippedTitleText,
				$currentTitle->getSubpageText(),
				$titleText
			);
		}

		return $titleText;
	}

	/**
	 *
	 * @return string
	 */
	protected function getHeaderLinks() {
		return $this->getSkinTemplate()->get( 'headerlinks' );
	}

	/**
	 *
	 * @return string
	 */
	protected function getPageLanguageCode() {
		return $this->getSkin()->getTitle()->getPageViewLanguage()->getHtmlCode();
	}

	/**
	 *
	 * @return string
	 */
	protected function getBreadCrumbs() {
		$params = array_merge(
			parent::getTemplateArgs(),
			[ PageHeaderRenderer::SKIN_TEMPLATE => $this->getSkinTemplate() ]
		);
		return $this->getRendererFactory()->get(
			'pageheader-breadcrumb',
			new Params( $params ),
			$this->getSkin()->getContext()
		)->render();
	}

	/**
	 *
	 * @return string
	 */
	protected function getCategories() {
		if ( $this->hideCategories() ) {
			return '';
		}

		$args = parent::getTemplateArgs();

		// If user has set preference not to show hidden cats,
		// do not retrieve them.
		$showHiddenCategories = \RequestContext::getMain()->getUser()
			->getOption( "showhiddencats" );
		$categoryRequestType = "normal";
		if ( $showHiddenCategories ) {
			$categoryRequestType = "all";
		}
		$categoriesFromSkin = $this->getSkin()->getOutput()
			->getCategories( $categoryRequestType );

		// Filter out tracking categories if the user set hidden categories
		// not be displayes. Unfortunately, this has to be done on a text
		// level, as tracking categories do not have any metadata to
		// identify them.
		if ( !$showHiddenCategories ) {
			$trackingCategories = new \TrackingCategories(
				MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'main' )
			);
			$trackingCategoryList = $trackingCategories->getTrackingCategories();
			foreach ( $trackingCategoryList as $category ) {
				$trackingCategoryNames[] = $category['cats'][0]->mTextform;
			}
			$categoriesToBeDisplayed = [];
			foreach ( $categoriesFromSkin as $category ) {
				if ( in_array( $category, $trackingCategoryNames ) ) {
					continue;
				}
				$categoriesToBeDisplayed[] = $category;
			}
		} else {
			$categoriesToBeDisplayed = $categoriesFromSkin;
		}

		$args[Category::PARAM_CATEGORY_NAMES] = $categoriesToBeDisplayed;
		$args = array_merge(
			$args,
			[ PageHeaderRenderer::SKIN_TEMPLATE => $this->getSkinTemplate() ]
		);
		$renderer = $this->getRendererFactory()->get(
			'pageheader-category',
			new Params( $args ),
			$this->getSkin()->getContext()
		);
		if ( $renderer instanceof NullRenderer ) {
			$renderer = Category::factory(
				'pageheader-category',
				MediaWikiServices::getInstance(),
				MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' ),
				new Params( $args )
			);
		}
		return $renderer->render();
	}

	/**
	 *
	 * @return string
	 */
	protected function getWatchAction() {
		$contentNavigation = $this->getSkinTemplate()->get( 'content_navigation' );

		$watch = [];
		$text = '';
		$title = '';

		if ( isset( $contentNavigation['actions']['watch'] ) ) {
			$watch = $contentNavigation['actions']['watch'];
			$iconClass = 'bs-icon-star-empty';
			$text = wfMessage( 'bs-calumma-page-watch-text',
					$this->getSkin()->getTitle()->getPrefixedText() )->plain();
			$title = wfMessage( 'bs-calumma-page-watch-title',
					$this->getSkin()->getTitle()->getPrefixedText() )->plain();
		}

		if ( isset( $contentNavigation['actions']['unwatch'] ) ) {
			$watch = $contentNavigation['actions']['unwatch'];
			$iconClass = 'bs-icon-star-full';
			$text = wfMessage( 'bs-calumma-page-unwatch-text',
					$this->getSkin()->getTitle()->getPrefixedText() )->plain();
			$title = wfMessage( 'bs-calumma-page-unwatch-title',
					$this->getSkin()->getTitle()->getPrefixedText() )->plain();
		}

		if ( empty( $watch ) ) {
			return '';
		}

		$html = Html::openElement(
				'span',
				[
					'id' => $watch['id'],
					'class' => 'bs-page-watch'
				] );

		$html .= Html::openElement(
				'a',
				[
					'href' => $watch['href'],
					'class' => $watch['class'],
					'title' => $title
				]
			);
		$html .= Html::element(
				'i',
				[
					'class' => $iconClass
				] );
		$html .= Html::element(
				'span',
				[],
				$text
				);
		$html .= Html::closeElement( 'a' );

		$html .= Html::closeElement( 'span' );

		return $html;
	}

	/**
	 *
	 * @return RendererFactory
	 */
	protected function getRendererFactory() {
		return MediaWikiServices::getInstance()->getService( 'BSRendererFactory' );
	}

	/**
	 *
	 * @return bool
	 */
	protected function showContextBand() {
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function showQualificationBand() {
		if ( $this->getSkin()->getTitle()->exists() === false ) {
			return false;
		}
		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function showMetaBand() {
		$title = $this->getSkin()->getTitle();
		if ( $title->exists() === false ) {
			return false;
		}
		if ( $title->isTalkPage() ) {
			return false;
		}
		if ( $this->isHistoryView() ) {
			return false;
		}
		if ( $this->isDiffView() ) {
			return false;
		}

		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function hideCategories() {
		if ( $this->getSkin()->getTitle()->isTalkPage() ) {
			return true;
		}
		if ( $this->isHistoryView() ) {
			return true;
		}
		if ( $this->isDiffView() ) {
			return true;
		}
		$isInstanceOf = $this->isInstanceOfContent( WikiTextContent::class );
		$isContentModel = $this->isContentModel( CONTENT_MODEL_WIKITEXT );
		if ( !$isInstanceOf && !$isContentModel ) {
			return true;
		}

		return false;
	}

	/**
	 * Save for overwriting content classes, but page must exist
	 * @param string $contentClass
	 * @return bool
	 */
	private function isInstanceOfContent( $contentClass ) {
		if ( !$this->getSkin()->getTitle()->exists() ) {
			return false;
		}
		$wikiPage = WikiPage::factory( $this->getSkin()->getTitle() );
		if ( !$wikiPage ) {
			return false;
		}
		return $wikiPage->getContent() instanceof $contentClass;
	}

	/**
	 * Unsafe when extending contentmodel
	 * @param string $modelName
	 * @return bool
	 */
	private function isContentModel( $modelName ) {
		return $this->getSkin()->getTitle()->getContentModel() === $modelName;
	}

	/**
	 *
	 * @return bool
	 */
	private function isDiffView() {
		return $this->getSkin()->getRequest()->getVal( 'diff', '-1' ) !== '-1';
	}

	/**
	 *
	 * @return bool
	 */
	private function isHistoryView() {
		return $this->getSkin()->getRequest()->getVal( 'action', 'view' ) === 'history';
	}

	/**
	 *
	 * @return bool
	 */
	private function isEditMode() {
		$action = $this->getSkin()->getRequest()->getVal( 'action', 'view' );
		$veAction = $this->getSkin()->getRequest()->getVal( 'veaction', '' );
		return $action === 'edit' || $veAction === 'edit';
	}

	/**
	 *
	 * @return string
	 */
	private function getDataBeforeContent() {
		$data = [];

		$params = array_merge(
			parent::getTemplateArgs(),
			[
				PageHeaderRenderer::SKIN_TEMPLATE => $this->getSkinTemplate()
			]
		);

		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationPageHeaderBeforeContentRegistry'
		);

		$registeredFactoryCallbacks = $registry->getAllValues();

		$activeProviders =
			MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' )
				->get( 'BlueSpiceCalummaPageHeaderBeforeContentEnabledProviders' );

		foreach ( $registeredFactoryCallbacks as $callbackKey => $factoryCallback ) {

			if ( !in_array( $callbackKey, $activeProviders ) ) {
				continue;
			}

			if ( !is_callable( $factoryCallback ) ) {
				throw new \Exception( "No valid callback for '$callbackKey'!" );
			}

			$data[] = $this->getRendererFactory()->get(
				$callbackKey,
				new Params( $params ),
				$this->getSkin()->getContext()
			)->render();

		}
		return $data;
	}
}
