<?php

namespace BlueSpice\Social\Renderer\Entity;

use BlueSpice\Renderer\Params;
use BlueSpice\Utility\CacheHelper;
use Config;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;

class Page extends \BlueSpice\Social\Renderer\Entity {

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
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	protected function render_content( $val ) {
		return '';
	}
}
