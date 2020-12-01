<?php

namespace BlueSpice\Authors;

use MediaWiki\MediaWikiServices;

class AuthorsList {

	/**
	 *
	 * @var \Wikimedia\Rdbms\LoadBalancer
	 */
	protected $loadBalancer = null;

	/**
	 *
	 * @var \Title
	 */
	protected $title = null;

	/**
	 *
	 * @var string[]
	 */
	protected $blacklist = [];

	/**
	 *
	 * @var int
	 */
	protected $limit = 1;

	/**
	 *
	 * @var bool
	 */
	protected $more = false;

	/**
	 *
	 * @param \Title $title
	 * @param array $blacklist
	 * @param int $limit
	 * @param \Wikimedia\Rdbms\LoadBalancer|null $loadBalancer
	 */
	public function __construct( $title, $blacklist, $limit = 0, $loadBalancer = null ) {
		$this->title = $title;
		$this->blacklist = $blacklist;
		$this->loadBalancer = $loadBalancer;
		$this->limit = $limit;

		if ( $this->loadBalancer === null ) {
			$services = MediaWikiServices::getInstance();
			$this->loadBalancer = $services->getDBLoadBalancer();
		}
	}

	/**
	 * Find first editor. If editor is on blacklist return empty string.
	 * @param Revision $revision
	 * @return string The originators username
	 *
	 */
	public function getOriginator( $revision ) {
		if ( $revision instanceof \Revision === false ) {
			return '';
		}

		if ( !$revision->getUser() ) {
			return '';
		}

		$originator = $revision->getUserText();

		if ( \User::isIP( $originator ) ) {
			return '';
		}
		if ( in_array( $originator, $this->blacklist ) ) {
			return '';
		}
		return $originator;
	}

	/**
	 * @return string[]
	 */
	public function getEditors() {
		$usertexts = $this->loadAllUserTexts();

		if ( empty( $usertexts ) ) {
			return [];
		}

		$count = count( $usertexts );
		$items = 0;
		$editors = [];

		while ( $items < $count ) {
			if ( $this->limit && $items > ( $this->limit - 1 ) ) {
				$this->more = true;
				break;
			}

			if ( \User::isIP( $usertexts[$items] ) ) {
				unset( $usertexts[$items] );
				$items++;
				continue;
			}

			$user = \User::newFromName( $usertexts[$items] );

			if ( !is_object( $user ) || in_array( $user->getName(), $this->blacklist ) ) {
				unset( $usertexts[$items] );
				$items++;
				continue;
			}

			$editors[] = $usertexts[$items];
			$items++;
		}

		return $editors;
	}

	/**
	 *
	 * @return bool
	 */
	public function moreEditors() {
		return $this->more;
	}

	/**
	 *
	 * @return string[]
	 */
	protected function loadAllUserTexts() {
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$query = MediaWikiServices::getInstance()->getRevisionStore()->getQueryInfo();
		$query['fields'][] = 'MAX(rev_timestamp) AS ts';
		$conds['rev_page'] = $this->title->getArticleID();
		$options = [
			'GROUP BY' => 'rev_user_text',
			'ORDER BY' => 'ts DESC'
		];
		$res = $dbr->select(
			$query['tables'],
			$query['fields'],
			$conds,
			__METHOD__,
			$options,
			$query['joins']
		);

		if ( $res->numRows() == 0 ) {
			return [];
		}

		$authors = [];
		foreach ( $res as $row ) {
			$authors[] = $row->rev_user_text;
		}

		$dbr->freeResult( $res );

		return $authors;
	}
}
