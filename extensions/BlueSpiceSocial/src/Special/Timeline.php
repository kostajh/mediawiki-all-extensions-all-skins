<?php

namespace BlueSpice\Social\Special;

use BlueSpice\Context;
use BlueSpice\Renderer\Params;
use BlueSpice\Social\EntityListContext\SpecialTimeline;
use BlueSpice\Social\Renderer\EntityList;
use FormatJson;
use MediaWiki\MediaWikiServices;

/**
 * Timeline SpecialPage
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
class Timeline extends \BlueSpice\SpecialPage {

	public function __construct() {
		parent::__construct( 'Timeline', 'read', true );
	}

	/**
	 *
	 * @param string $param
	 */
	public function execute( $param ) {
		$this->checkPermissions();

		$this->getOutput()->setPageTitle(
			wfMessage( 'bs-social-special-timeline-heading' )->plain()
		);

		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig(
			'bsg'
		);

		$context = new SpecialTimeline(
			new Context(
				$this->getContext(),
				$config
			),
			$config,
			$this->getContext()->getUser()
		);

		$rendererParams = [
			EntityList::PARAM_CONTEXT => $context
		];

		$filter = $this->getRequest()->getText( EntityList::PARAM_FILTER );
		if ( $filter ) {
			$decodedFilter = FormatJson::decode( $filter );
			if ( $decodedFilter !== null ) {
				$rendererParams[EntityList::PARAM_FILTER] = $decodedFilter;
			}
		}
		$sort = $this->getRequest()->getText( EntityList::PARAM_SORT );
		if ( $sort ) {
			$decodedSort = FormatJson::decode( $sort );
			if ( $decodedSort !== null ) {
				$rendererParams[EntityList::PARAM_SORT] = $decodedSort;
			}
		}

		$renderer = MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			'entitylist',
			new Params( $rendererParams )
		);

		$this->getOutput()->addHTML( $renderer->render() );
	}
}
