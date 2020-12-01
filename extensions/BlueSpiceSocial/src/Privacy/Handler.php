<?php

namespace BlueSpice\Social\Privacy;

use BlueSpice\Data\Filter\Numeric;
use BlueSpice\Data\ReaderParams;
use BlueSpice\Privacy\IPrivacyHandler;
use BlueSpice\Privacy\Module\Transparency;
use BlueSpice\Social\Entity;
use BlueSpice\Social\EntityListContext;
use BlueSpice\Social\Privacy\Job\DeleteEntity;
use MediaWiki\MediaWikiServices;

class Handler implements IPrivacyHandler {
	/**
	 * @var \Language
	 */
	protected $language;

	/**
	 * @var \User
	 */
	protected $user;

	/**
	 *
	 * @param \Database $db
	 */
	public function __construct( \Database $db ) {
		$this->language = \RequestContext::getMain()->getLanguage();
	}

	/**
	 * @param string $oldUsername
	 * @param string $newUsername
	 * @return \Status
	 */
	public function anonymize( $oldUsername, $newUsername ) {
		$this->user = \User::newFromName( $oldUsername );
		$entityRecords = $this->getAllEntities();

		foreach ( $entityRecords as $record ) {
			$data = $record->getData();

			$entity = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )
				->newFromObject( $data );
			if ( !$entity instanceof Entity ) {
				continue;
			}

			// Add job to update search index after the process has completed
			$job = new \BlueSpice\Social\ExtendedSearch\Job\Entity(
				$entity->getTitle()
			);

			\JobQueueGroup::singleton()->push(
				$job
			);
		}
		return \Status::newGood();
	}

	/**
	 * @param \User $userToDelete
	 * @param \User $deletedUser
	 * @return \Status
	 */
	public function delete( \User $userToDelete, \User $deletedUser ) {
		$this->user = $userToDelete;
		$entityRecords = $this->getAllEntities();

		foreach ( $entityRecords as $record ) {
			$data = $record->getData();

			$entity = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )
				->newFromObject( $data );
			if ( !$entity instanceof Entity ) {
				continue;
			}

			// Do changing owner ID in a job, since it edits the page
			$job = new DeleteEntity(
				$entity->getTitle(), [
					'deletedUserId' => $deletedUser->getId()
				]
			);

			\JobQueueGroup::singleton()->push(
				$job
			);
		}
		return \Status::newGood();
	}

	/**
	 * Very minimalistic data export - until we know more on what
	 * we should export and what not
	 *
	 * @param array $types Types of info users wants to retrieve
	 * @param string $format Requested output format
	 * @param \User $user User to export data from
	 * @return \Status
	 */
	public function exportData( array $types, $format, \User $user ) {
		$this->user = $user;
		$entityRecords = $this->getAllEntities();

		$exportData = [];
		foreach ( $entityRecords as $record ) {
			$data = $record->getData();

			$entity = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )
				->newFromObject( $data );
			if ( !$entity instanceof Entity\Text ) {
				continue;
			}

			$timestamp = $this->language->userTimeAndDate(
				$entity->getTimestampCreated(),
				$user
			);
			$exportData[] = "$timestamp: {$data->header}: {$data->text}";
		}

		return \Status::newGood( [
			Transparency::DATA_TYPE_CONTENT => $exportData
		] );
	}

	/**
	 *
	 * @return \BlueSpice\Data\Record[]
	 */
	protected function getAllEntities() {
		$context = new \BlueSpice\Context(
			\RequestContext::getMain(),
			MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' )
		);
		$serviceUser = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getMaintenanceUser()->getUser();

		$listContext = new EntityListContext\PrivacyHandler(
			$context,
			$context->getConfig(),
			$serviceUser,
			null
		);
		$filters = $listContext->getFilters();
		$filters[] = (object)[
			Numeric::KEY_PROPERTY => Entity::ATTR_OWNER_ID,
			Numeric::KEY_VALUE => $this->user->getId(),
			Numeric::KEY_COMPARISON => Numeric::COMPARISON_EQUALS,
			Numeric::KEY_TYPE => 'numeric'
		];

		$params = new ReaderParams( [
			'filter' => $filters,
			'sort' => $listContext->getSort(),
			'limit' => $listContext->getLimit(),
			'start' => 0,
		] );

		$store = new \BlueSpice\Social\Data\Entity\Store();
		$res = $store->getReader( $listContext )->read( $params );

		return $res->getRecords();
	}
}
