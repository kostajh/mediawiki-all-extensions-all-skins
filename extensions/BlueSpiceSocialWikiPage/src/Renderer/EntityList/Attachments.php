<?php

namespace BlueSpice\Social\WikiPage\Renderer\EntityList;

use BlueSpice\Renderer\Params;
use BlueSpice\RendererFactory;
use BlueSpice\Social\Entity;
use BlueSpice\Social\EntityAttachment;
use BlueSpice\Social\Renderer\EntityList;
use BlueSpice\Social\WikiPage\EntityListContext\AfterContent;
use Config;
use Html;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;
use Title;

class Attachments extends EntityList {

	/**
	 *
	 * @var RendererFactory
	 */
	protected $rendererFactory = null;

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 * @param RendererFactory|null $rendererFactory
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null, $name = '',
		RendererFactory $rendererFactory = null ) {
		parent::__construct( $config, $params, $linkRenderer, $context, $name );
		$this->rendererFactory = $rendererFactory;
		if ( empty( $this->getEntities() ) ) {
			$this->args[static::PARAM_CLASS] .= ' empty-attachments';
		}
	}

	/**
	 * Returns a rendered template as HTML markup
	 * @return string - HTML
	 */
	public function render() {
		if ( !$this->getContext() instanceof AfterContent || !empty( $this->getEntities() ) ) {
			return parent::render();
		}
		$content = '';
		if ( $this->args[ static::PARAM_SHOW_ENTITY_LIST_MENU ] ) {
			$content .= $this->renderEntityListMenu();
		}
		if ( $this->args[ static::PARAM_SHOW_HEADLINE ] ) {
			$content .= $this->renderEntityListHeadline();
		}
		$content .= $this->getOpenTag();
		$content .= Html::openElement( 'li' );
		$content .= $this->rendererFactory->get(
			'social-wikipage-createfirstattachmentaftercontent',
			$this->params,
			$this->getContext()
		)->render();
		$content .= Html::closeElement( 'li' );
		$content .= $this->getCloseTag();

		return $content;
	}

	/**
	 *
	 * @param string $name
	 * @param MediaWikiServices $services
	 * @param Config $config
	 * @param Params $params
	 * @param IContextSource|null $context
	 * @param LinkRenderer|null $linkRenderer
	 * @param RendererFactory|null $rendererFactory
	 * @return Renderer
	 */
	public static function factory( $name, MediaWikiServices $services, Config $config,
		Params $params, IContextSource $context = null, LinkRenderer $linkRenderer = null,
		RendererFactory $rendererFactory = null ) {
		if ( !$context ) {
			$context = $params->get(
				static::PARAM_CONTEXT,
				false
			);
			if ( !$context instanceof IContextSource ) {
				$context = \RequestContext::getMain();
			}
		}
		if ( !$linkRenderer ) {
			$linkRenderer = $services->getLinkRenderer();
		}
		if ( !$rendererFactory ) {
			$rendererFactory = $services->getService( 'BSRendererFactory' );
		}

		return new static(
			$config,
			$params,
			$linkRenderer,
			$context,
			$name,
			$rendererFactory
		);
	}

	/**
	 *
	 * @return string
	 */
	protected function makeTagContent() {
		$content = '';
		$content .= Html::openElement( 'li' );
		$content .= Html::openElement( 'div', [
			'class' => 'bs-social-entity-content-attachments'
		] );
		foreach ( $this->getEntities() as $entity ) {
			$content .= $this->renderEntitiy( $entity );
		}
		$content .= Html::closeElement( 'div' );
		$content .= Html::closeElement( 'li' );
		return $content;
	}

	/**
	 *
	 * @param Entity $entity
	 * @param string $out
	 * @return string
	 */
	protected function renderEntitiy( Entity $entity, $out = '' ) {
		if ( !$entity->getConfig()->get( 'CanHaveAttachments' ) ) {
			return $out;
		}
		$availableAttachments = $entity->getConfig()->get(
			'AvailableAttachments'
		);

		$repoGroup = MediaWikiServices::getInstance()->getRepoGroup();
		$attachmentTypes = $entity->get( $entity::ATTR_ATTACHMENTS, [] );
		foreach ( $attachmentTypes as $type => $attachments ) {
			if ( !in_array( $type, $availableAttachments ) ) {
				continue;
			}
			if ( $type === 'images' ) {
				foreach ( $attachments as $image ) {
					$title = Title::makeTitle( NS_FILE, $image );
					if ( !$title ) {
						continue;
					}
					$file = $repoGroup->findFile( $title );
					if ( !$file ) {
						continue;
					}

					$entityAttachment = EntityAttachment::factory(
						$entity,
						$file,
						strpos( $file->getMimeType(), 'image' ) === false
							? 'file'
							: 'image'
					);
					$out .= $entityAttachment->render();
				}
			}
			if ( $type === 'links' ) {
				foreach ( $attachments as $link ) {
					$title = Title::newFromText( $link );
					if ( !$title ) {
						continue;
					}

					$entityAttachment = EntityAttachment::factory(
						$entity,
						$title,
						'link'
					);
					$out .= $entityAttachment->render();
				}
			}
		}
		return $out;
	}
}
