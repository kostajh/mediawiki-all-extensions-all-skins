<?php

namespace BlueSpice\Social\Renderer\Entity;

use BlueSpice\Renderer\Params;
use BlueSpice\Social\Entity\Text as EntityText;
use BlueSpice\Social\EntityAttachment;
use BlueSpice\Utility\CacheHelper;
use Config;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;
use Title;

class Text extends \BlueSpice\Social\Renderer\Entity {

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 * @param CacheHelper|null $cacheHelper
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '', CacheHelper $cacheHelper = null ) {
		parent::__construct(
			$config,
			$params,
			$linkRenderer,
			$context,
			$name,
			$cacheHelper
		);

		$this->args['content'] = '';
		$this->args['attachments'] = $this->getEntity()->get(
			EntityText::ATTR_ATTACHMENTS
		);
	}

	/**
	 *
	 * @param mixed $val
	 * @param string $type
	 * @return string
	 */
	protected function render_attachments( $val, $type = 'Default' ) {
		$out = '';
		if ( empty( $val ) || !is_array( $val ) ) {
			return $out;
		}
		$availableAttachments = $this->getEntity()->getConfig()->get(
			'AvailableAttachments'
		);
		$repoGroup = MediaWikiServices::getInstance()->getRepoGroup();
		foreach ( $val as $type => $attachments ) {
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

					$type = 'image';
					if ( strpos( $file->getMimeType(), 'image' ) === false ) {
						$type = 'file';
					}
					$entityAttachment = EntityAttachment::factory(
						$this->getEntity(),
						$file,
						$type
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
						$this->getEntity(),
						$title,
						'link'
					);
					$out .= $entityAttachment->render();
				}
			}
		}
		return $out;
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	protected function render_content( $val ) {
		return $this->getEntity()->get(
			EntityText::ATTR_PARSED_TEXT,
			''
		);
	}
}
