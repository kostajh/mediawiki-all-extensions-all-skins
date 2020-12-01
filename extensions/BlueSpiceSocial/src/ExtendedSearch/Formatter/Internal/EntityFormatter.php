<?php

namespace BlueSpice\Social\ExtendedSearch\Formatter\Internal;

use BlueSpice\Social\Entity;
use MediaWiki\Linker\LinkRenderer;
use RequestContext;

class EntityFormatter {
	protected $entity;
	protected $result;
	protected $resultObject;

	/** @var LinkRenderer */
	protected $linkRenderer;

	/**
	 *
	 * @param Entity $entity
	 * @param array &$result
	 * @param \Elastica\Result $resultObject
	 */
	public function __construct( $entity, array &$result, $resultObject ) {
		$this->entity = $entity;
		$this->result = &$result;
		$this->resultObject = $resultObject;
	}

	/**
	 *
	 * @param LinkRenderer $linkRenderer
	 */
	public function setLinkRenderer( $linkRenderer ) {
		$this->linkRenderer = $linkRenderer;
	}

	/**
	 *
	 */
	public function format() {
		// can these timestamps be different than indexed ones?
		$this->result['ctime'] = RequestContext::getMain()->getLanguage()->date(
			$this->entity->getTimestampCreated()
		);
		$this->result['mtime'] = RequestContext::getMain()->getLanguage()->date(
			$this->entity->getTimestampTouched()
		);

		$this->result['entity_type'] = $this->entity->get( Entity::ATTR_TYPE );
		$owner = $this->entity->getOwner();
		$this->result['page_anchor'] = $this->linkRenderer->makeLink( $this->entity->getTitle() );
		$name = $this->entity->get( Entity::ATTR_OWNER_REAL_NAME );
		if ( !$name ) {
			$name = $owner->getName();
		}
		$this->result['owner'] = $this->linkRenderer->makeLink(
			$owner->getUserpage(),
			$name
		);
	}
}
