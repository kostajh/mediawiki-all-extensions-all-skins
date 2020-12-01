<?php
namespace BlueSpice\Social\TimelineUpdate\Hook\BsAdapterAjaxPingResult;

use BlueSpice\Data\Filter\Date;
use BlueSpice\Renderer\Params;
use BlueSpice\Social\Entity;
use MediaWiki\MediaWikiServices;

class CheckUpdate extends \BlueSpice\Hook\BsAdapterAjaxPingResult {
	protected function doProcess() {
		$data = (object)$this->params['data'];
		$data->limit = 10;

		foreach ( $data->filter as $filter ) {
			if ( $filter->property == Entity::ATTR_TIMESTAMP_CREATED ) {
				$filter->comparison = Date::COMPARISON_GREATER_THAN;
				break;
			}
		}

		$class = "\\BlueSpice\\Social\\EntityListContext";
		if ( isset( $data->EntityListContext ) ) {
			$class = $data->EntityListContext;
		}
		$entity = null;
		if ( isset( $data->parentid ) ) {
			$entity = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )
				->newFromID( $data->parentid, Entity::NS );
		}
		$context = new $class(
			$this->getContext(),
			$this->getConfig(),
			$this->getContext()->getUser(),
			$entity
		);

		$params = array_merge(
			(array)$data,
			[ 'context' => $context ]
		);
		$renderer = MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			$context->getRendererName(),
			new Params( $params )
		);
		$entities = $renderer->getEntities();

		$count = count( $entities );

		$this->singleResults['updates'] = $count > 9 ? '9+' : $count;
		$this->singleResults['success'] = true;

		return true;
	}

	protected function skipProcessing() {
		if ( $this->reference !== "BlueSpiceSocial.TimelineUpdate.Button.Update" ) {
			return true;
		}

		return false;
	}
}
