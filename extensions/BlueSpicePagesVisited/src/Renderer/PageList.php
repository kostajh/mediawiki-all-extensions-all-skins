<?php

namespace BlueSpice\PagesVisited\Renderer;

use BlueSpice\Renderer\Params;
use BlueSpice\UtilityFactory;
use BlueSpice\WhoIsOnline\Data\Record;
use BsStringHelper;
use Config;
use Html;
use HtmlArmor;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use Title;

class PageList extends \BlueSpice\WhoIsOnline\Renderer\UserList {
	const PARAM_MAX_TITLE_LENGTH = 'maxtitlelength';

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 * @param UtilityFactory|null $util
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '', UtilityFactory $util = null ) {
		parent::__construct( $config, $params, $linkRenderer, $context, $name, $util );
		$this->args[static::PARAM_MAX_TITLE_LENGTH] = $params->get(
			static::PARAM_MAX_TITLE_LENGTH,
			-1
		);
	}

	/**
	 *
	 * @return string
	 */
	public function render() {
		$out = '';
		$out .= Html::openElement( 'ul' );
		if ( $this->recordSet->getTotal() > 0 ) {
			foreach ( $this->recordSet->getRecords() as $record ) {
				$title = Title::makeTitle(
					$record->get( Record::PAGE_NAMESPACE ),
					$record->get( Record::PAGE_TITLE )
				);
				if ( !$title ) {
					continue;
				}
				$display = null;
				if ( $this->args[ static::PARAM_MAX_TITLE_LENGTH ] > 0 ) {
					$display = new HtmlArmor( $this->shorten( $title ) );
				}
				$out .= Html::openElement( 'li' );
				$out .= $this->linkRenderer->makeLink( $title, $display );
				$out .= Html::closeElement( 'li' );
			}
		}
		$out .= Html::closeElement( 'ul' );
		return $out;
	}

	/**
	 *
	 * @param Title $title
	 * @return string
	 */
	protected function shorten( Title $title ) {
		return BsStringHelper::shorten( $title->getPrefixedText(), [
			'max-length' => $this->args[static::PARAM_MAX_TITLE_LENGTH],
			'position' => 'middle'
		] );
	}

}
