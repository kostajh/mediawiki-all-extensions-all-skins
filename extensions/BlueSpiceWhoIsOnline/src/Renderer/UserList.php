<?php

namespace BlueSpice\WhoIsOnline\Renderer;

use BlueSpice\Data\ResultSet;
use BlueSpice\Renderer\Params;
use BlueSpice\UtilityFactory;
use BlueSpice\WhoIsOnline\Data\Record;
use Config;
use Html;
use HtmlArmor;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;
use User;

class UserList extends \BlueSpice\Renderer {
	const PARAM_RECORD_SET = 'recordset';

	/**
	 *
	 * @var ResultSet
	 */
	protected $recordSet = null;

	/**
	 *
	 * @var UtilityFactory
	 */
	protected $util = null;

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
		parent::__construct( $config, $params, $linkRenderer, $context, $name );

		$this->util = $util;

		$this->args[static::PARAM_TAG] = $params->get(
			static::PARAM_TAG,
			'ul'
		);
		$this->recordSet = $params->get(
			static::PARAM_RECORD_SET,
			null
		);
		if ( !$this->recordSet instanceof ResultSet ) {
			throw new MWException(
				"Param '" . static::PARAM_RECORD_SET . "' must be an instance of '"
				. ResultSet::class . "'!"
			);
		}
	}

	/**
	 *
	 * @param string $name
	 * @param MediaWikiServices $services
	 * @param Config $config
	 * @param Params $params
	 * @param IContextSource|null $context
	 * @param LinkRenderer|null $linkRenderer
	 * @param UtilityFactory|null $util
	 * @return Renderer
	 */
	public static function factory( $name, MediaWikiServices $services, Config $config,
		Params $params, IContextSource $context = null, LinkRenderer $linkRenderer = null,
		UtilityFactory $util = null ) {
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
		if ( !$util ) {
			$util = $services->getService( 'BSUtilityFactory' );
		}

		return new static( $config, $params, $linkRenderer, $context, $name, $util );
	}

	/**
	 *
	 * @return string
	 */
	public function render() {
		$out = '';
		$out .= Html::openElement( 'ul' );
		if ( $this->recordSet->getTotal() < 1 ) {
			$out .= Html::element(
				'li',
				[],
				$this->msg( 'bs-whoisonline-nousers' )->plain()
			);
		}
		foreach ( $this->recordSet->getRecords() as $record ) {
			$user = User::newFromId( $record->get( Record::USER_ID ) );
			if ( !$user ) {
				continue;
			}
			$displayName = $this->util->getUserHelper( $user )->getDisplayName();
			$out .= Html::openElement( 'li' );
			$out .= $this->linkRenderer->makeLink(
				$user->getUserPage(),
				new HtmlArmor( $displayName )
			);
			$out .= Html::closeElement( 'li' );
		}
		$out .= Html::closeElement( 'ul' );
		return $out;
	}

}
