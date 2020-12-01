<?php

namespace BlueSpice\PagesVisited\Tag;

use BlueSpice\Data\FieldType;
use BlueSpice\Data\Filter;
use BlueSpice\Data\Filter\ListValue;
use BlueSpice\Data\Filter\Numeric;
use BlueSpice\Data\Filter\StringValue;
use BlueSpice\Data\ReaderParams;
use BlueSpice\Data\ResultSet;
use BlueSpice\Data\Sort;
use BlueSpice\PagesVisited\Data\Store;
use BlueSpice\PagesVisited\Renderer\PageList;
use BlueSpice\PagesVisited\Tag\PagesVisited as Tag;
use BlueSpice\Renderer\Params;
use BlueSpice\Tag\Handler;
use BlueSpice\WhoIsOnline\Data\Record;
use MediaWiki\MediaWikiServices;
use RequestContext;

class PagesVisitedHandler extends Handler {

	/**
	 *
	 * @return string
	 */
	public function handle() {
		$recordSet = new ResultSet( [], 0 );
		if ( !$this->parser->getUser()->isAnon() ) {
			$readerParams = new ReaderParams( $this->makeParams() );
			$recordSet = ( new Store() )->getReader()->read( $readerParams );
		}

		$portlet = MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			'pagesvisited-pagelist',
			new Params( [
				PageList::PARAM_RECORD_SET => $recordSet,
				PageList::PARAM_MAX_TITLE_LENGTH
					=> $this->processedArgs[ Tag::PARAM_MAX_TITLE_LENGTH ]
			] ),
			RequestContext::getMain()
		);

		return $portlet->render();
	}

	/**
	 *
	 * @return array
	 */
	protected function makeParams() {
		$params = [
			ReaderParams::PARAM_LIMIT => $this->processedArgs[PagesVisited::PARAM_COUNT],
			ReaderParams::PARAM_FILTER => [],
			ReaderParams::PARAM_FILTER => [ [
				Filter::KEY_COMPARISON => StringValue::COMPARISON_EQUALS,
				Filter::KEY_PROPERTY => Record::ACTION,
				Filter::KEY_VALUE => 'view',
				Filter::KEY_TYPE => FieldType::STRING
			], [ Filter::KEY_COMPARISON => Numeric::COMPARISON_EQUALS,
				Filter::KEY_PROPERTY => Record::USER_ID,
				Filter::KEY_VALUE => (int)$this->parser->getUser()->getId(),
				Filter::KEY_TYPE => 'numeric'
			] ]
		];
		if ( !empty( $this->processedArgs[Tag::PARAM_NAMESPACES] ) ) {
			$params[ReaderParams::PARAM_FILTER][] = [
				Filter::KEY_COMPARISON => ListValue::COMPARISON_EQUALS,
				Filter::KEY_PROPERTY => Record::PAGE_NAMESPACE,
				Filter::KEY_VALUE => $this->processedArgs[Tag::PARAM_NAMESPACES],
				Filter::KEY_TYPE => FieldType::LISTVALUE
			];
		}
		$params[ReaderParams::PARAM_LIMIT] = ReaderParams::LIMIT_INFINITE;
		if ( !empty( $this->processedArgs[Tag::PARAM_COUNT] ) ) {
			$params[ReaderParams::PARAM_LIMIT] = $this->processedArgs[Tag::PARAM_COUNT];
		}
		if ( $this->processedArgs[Tag::PARAM_ORDER] === 'pagename' ) {
			$params[ReaderParams::PARAM_SORT][] = [
				'property' => Record::PAGE_TITLE,
				'direction' => Sort::ASCENDING,
			];
		}
		return $params;
	}

}
