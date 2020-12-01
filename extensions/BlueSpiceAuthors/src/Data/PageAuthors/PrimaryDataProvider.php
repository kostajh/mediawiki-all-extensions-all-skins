<?php

namespace BlueSpice\Authors\Data\PageAuthors;

use BlueSpice\Authors\AuthorsList;
use BlueSpice\Data\IPrimaryDataProvider;
use ReaderParams;
use Title;
use User;
use Wikimedia\Rdbms\LoadBalancer;

class PrimaryDataProvider implements IPrimaryDataProvider {

	/**
	 *
	 * @var Record[]
	 */
	protected $data = [];

	/**
	 *
	 * @var Title
	 */
	protected $title = null;

	/**
	 *
	 * @var string[]
	 */
	protected $authorsBlacklist = [];

	/**
	 *
	 * @var LoadBalancer
	 */
	protected $loadBalancer = null;

	/**
	 *
	 *
	 * @param Title $title
	 * @param string[] $blacklist
	 * @param LoadBalancer $loadBalancer
	 */
	public function __construct( $title, $blacklist, $loadBalancer ) {
		$this->title = $title;
		$this->loadBalancer = $loadBalancer;
		$this->authorsBlacklist = $blacklist;
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return Record[]
	 */
	public function makeData( $params ) {
		$list = new AuthorsList( $this->title, $this->authorsBlacklist );

		$revision = $this->title->getFirstRevision();
		$originator = $list->getOriginator( $revision );
		$editors = $list->getEditors();

		if ( $originator !== '' ) {
			$user = User::newFromName( $originator );
			if ( $user instanceof \User ) {
				$this->appendRowToData( [
					'user_name' => $user->getName(),
					'author_type' => 'originator'
				] );
			}
		}

		foreach ( $editors as $editor ) {
			$user = User::newFromName( $editor );
			if ( $user instanceof \User === false ) {
				continue;
			}
			$this->appendRowToData( [
				'user_name' => $user->getName(),
				'author_type' => 'editor'
			] );
		}

		return $this->data;
	}

	/**
	 *
	 * @param array $data
	 */
	protected function appendRowToData( $data ) {
		$this->data[] = new Record( (object)[
			Record::USER_NAME => $data['user_name'],
			Record::AUTHOR_TYPE => $data['author_type']
		] );
	}
}
