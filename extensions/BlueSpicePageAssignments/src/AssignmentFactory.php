<?php

namespace BlueSpice\PageAssignments;

use BlueSpice\Context;
use BlueSpice\Data\Filter;
use BlueSpice\Data\ReaderParams;
use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\PageAssignments\Data\Assignment\Store;
use BlueSpice\PageAssignments\Data\Record;
use MediaWiki\MediaWikiServices;

class AssignmentFactory {

	/**
	 *
	 * @var IAssignment[]
	 */
	protected $targetCache = [];

	/**
	 *
	 * @var AssignableFactory
	 */
	protected $assignableFactory = null;

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 * @var ExtensionAttributeBasedRegistry
	 */
	protected $targetRegistry;

	/**
	 *
	 * @param AssignableFactory $assignableFactory
	 * @param \Config $config
	 * @param ExtensionAttributeBasedRegistry $targetRegistry
	 */
	public function __construct( AssignableFactory $assignableFactory, $config,
		$targetRegistry ) {
		$this->assignableFactory = $assignableFactory;
		$this->config = $config;
		$this->targetRegistry = $targetRegistry;
	}

	/**
	 *
	 * @param \Title $title
	 * @return bool|ITarget
	 * @throws \MWException
	 */
	public function newFromTargetTitle( \Title $title ) {
		if ( $title->getArticleID() < 1 ) {
			return false;
		}
		$instance = $this->fromCache( $title );
		if ( $instance ) {
			return $instance;
		}

		$assignments = $this->getAssignments( $title );
		$targetClass = $this->getTargetClass();
		if ( $targetClass === null ) {
			throw new \MWException( 'No target specified' );
		}

		$instance = call_user_func_array( "$targetClass::factory", [
			$this->config,
			$assignments,
			$title
		] );

		$this->appendCache( $instance );
		return $instance;
	}

	/**
	 *
	 * @return string|null
	 */
	protected function getTargetClass() {
		$targets = $this->targetRegistry->getAllKeys();
		$targetToUse = $this->config->get( 'PageAssignmentsTarget' );

		if ( $targetToUse && in_array( $targetToUse, $targets ) ) {
			return $this->targetRegistry->getValue( $targetToUse );
		}
		return null;
	}

	/**
	 *
	 * @param ITarget $instance
	 */
	protected function appendCache( ITarget $instance ) {
		$this->targetCache[ $instance->getTitle()->getArticleId() ]
			= $instance;
	}

	/**
	 *
	 * @param \Title $title
	 * @return ITarget|false
	 */
	protected function fromCache( \Title $title ) {
		if ( isset( $this->targetCache[$title->getArticleID()] ) ) {
			return $this->targetCache[$title->getArticleID()];
		}
		return false;
	}

	/**
	 *
	 * @param \Title|null $title
	 * @return IAssignment[]
	 */
	protected function getAssignments( \Title $title = null ) {
		if ( !$title || $title->getArticleID() < 1 ) {
			return [];
		}

		$recordSet = $this->getStore()->getReader()->read(
			new ReaderParams( [ 'filter' => [
				[
					Filter::KEY_FIELD => Record::PAGE_ID,
					Filter::KEY_VALUE => (int)$title->getArticleID(),
					Filter::KEY_TYPE => 'numeric',
					Filter::KEY_COMPARISON => Filter::COMPARISON_EQUALS,
				]
			] ] )
		);

		$assignments = [];
		foreach ( $recordSet->getRecords() as $record ) {
			$assignment = $this->factory(
				$record->get( Record::ASSIGNEE_TYPE ),
				$record->get( Record::ASSIGNEE_KEY ),
				$title
			);
			if ( !$assignment ) {
				continue;
			}
			$assignments[] = $assignment;
		}
		return $assignments;
	}

	/**
	 *
	 * @return Store
	 */
	public function getStore() {
		return new Store(
			new Context( \RequestContext::getMain(), $this->config ),
			MediaWikiServices::getInstance()->getDBLoadBalancer()
		);
	}

	/**
	 *
	 * @param ITarget $target
	 * @return true
	 */
	public function invalidate( ITarget $target ) {
		if ( isset( $this->targetCache[$target->getTitle()->getArticleID()] ) ) {
			unset( $this->targetCache[$target->getTitle()->getArticleID()] );
		}
		return true;
	}

	/**
	 *
	 * @param string $type
	 * @param string $key
	 * @param \Title $title
	 * @return IAssignment|null
	 */
	public function factory( $type, $key, \Title $title ) {
		$assignable = $this->assignableFactory->factory( $type );
		if ( !$assignable ) {
			return null;
		}
		$class = $assignable->getAssignmentClass();

		return new $class(
			$this->config,
			null,
			$title,
			$type,
			$key
		);
	}

	/**
	 *
	 * @return array
	 */
	public function getRegisteredTypes() {
		return $this->assignableFactory->getRegisteredTypes();
	}
}
