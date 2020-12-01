<?php
/**
 * An entity that handles 'Mark As Helpful' items
 */
class MarkAsHelpfulItem {

	// database field definition
	protected $property = [
		'mah_id' => null,
		'mah_type' => null,
		'mah_item' => null,
		'mah_user_id' => null,
		'mah_user_editcount' => null,
		'mah_namespace' => null,
		'mah_title' => null,
		'mah_timestamp' => null,
		'mah_system_type' => null,
		'mah_user_agent' => null,
		'mah_locale' => null
	];

	protected $user;
	protected $loadedFromDatabase = false;

	/**
	 * Constructor
	 * @param int|null $mah_id an id that represents a unique mark as helpful record
	 */
	public function __construct( $mah_id = null ) {
		if ( $mah_id == intval( $mah_id ) ) {
			$this->property['mah_id'] = $mah_id;
		}
	}

	/**
	 * Getter method
	 * @param string $key the name of a property
	 * @return mixed|null
	 */
	public function getProperty( $key ) {
		if ( array_key_exists( $key, $this->property ) ) {
			return $this->property[$key];
		} else {
			return null;
		}
	}

	/**
	 * Setter method
	 * @param string $key the name of the property
	 * @param mixed $value the value of the property
	 */
	public function setProperty( $key, $value ) {
		if ( array_key_exists( $key, $this->property ) ) {
			$this->property[$key] = $value;
		}
	}

	/**
	 * get the owner of the 'mark as helpful' item
	 * @param User|null $backUpUser backup user if this->user isn't set, and not loadedFromDatabase
	 * @return User|null
	 */
	private function getUser( ?User $backUpUser ) {
		if ( !$this->user ) {
			if ( $this->loadedFromDatabase ) {
				if ( $this->getProperty( 'mah_user_id' ) ) {
					$this->user = User::newFromId( $this->getProperty( 'mah_user_id' ) );
				}
			} else {
				$this->user = $backUpUser;
			}
		}

		return $this->user;
	}

	/**
	 * Load data into object from external data
	 * @param array $params an array of data to be loaded into the object
	 * @param User $user
	 * @throws MWMarkAsHelpFulItemPropertyException
	 */
	public function loadFromRequest( $params, User $user ) {
		global $wgMarkAsHelpfulType;

		if ( isset( $params['type'] ) && in_array( $params['type'], $wgMarkAsHelpfulType ) ) {
			$this->setProperty( 'mah_type', $params['type'] );
		} else {
			throw new MWMarkAsHelpFulItemPropertyException( 'Unsupported type!' );
		}

		if ( isset( $params['item'] ) && $params['item'] == intval( $params['item'] ) ) {
			$this->setProperty( 'mah_item', $params['item'] );
		} else {
			throw new MWMarkAsHelpFulItemPropertyException( 'Invalid item!' );
		}

		if ( $user->isAnon() ) {
			throw new MWMarkAsHelpFulItemPropertyException( 'User not logged in!' );
		}

		$this->setProperty( 'mah_user_id', $user->getId() );
		$this->setProperty( 'mah_user_editcount', $user->getEditCount() );

		if ( isset( $params['page'] ) ) {
			$page = Title::newFromText( $params['page'] );

			if ( $page ) {
				$this->setProperty( 'mah_namespace', $page->getNamespace() );
				$this->setProperty( 'mah_title', $page->getDBkey() );
			} else {
				throw new MWMarkAsHelpFulItemPropertyException( 'Invalid page!' );
			}
		}

		$this->setProperty( 'mah_timestamp', wfTimestampNow() );

		if ( isset( $params['system'] ) ) {
			$this->setProperty( 'mah_system_type', $params['system'] );
		}
		if ( isset( $params['useragent'] ) ) {
			$this->setProperty( 'mah_user_agent', $params['useragent'] );
		}
		if ( isset( $params['locale'] ) ) {
			$this->setProperty( 'mah_locale', $params['locale'] );
		}
	}

	/**
	 * Load from database
	 * @param array $conds keys to load unique item from database, it must be one of the allowed keys
	 * @return bool
	 * @throws MWMarkAsHelpFulItemSearchKeyException
	 */
	public function loadFromDatabase( $conds ) {
		$searchKey = array_keys( $conds );

		$flag = sort( $searchKey );

		if ( !$flag ) {
			return false;
		}

		$searchKey = implode( ',', $searchKey );

		$allowableSearchKey = [ 'mah_id', 'mah_item,mah_type,mah_user_id' ];

		if ( !in_array( $searchKey, $allowableSearchKey ) ) {
			throw new MWMarkAsHelpFulItemSearchKeyException( 'Invalid search key!' );
		}

		$dbr = wfGetDB( DB_REPLICA );

		$res = $dbr->selectRow(
			[ 'mark_as_helpful' ],
			[ '*' ],
			$conds,
			__METHOD__
		);

		if ( $res !== false ) {
			foreach ( $this->property as $key => $val ) {
				$this->setProperty( $key, $res->$key );
			}

			$this->loadedFromDatabase = true;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * To mark an item as helpful, this function should be called after either loadFromRequest() or setProperty()
	 * data must be validated if called from setProperty()
	 */
	public function mark() {
		$dbw = wfGetDB( DB_MASTER );

		$row = [];

		foreach ( $this->property as $key => $value ) {
			if ( $value !== null ) {
				$row[$key] = $value;
			}
		}

		$this->property['mah_id'] = $dbw->nextSequenceValue( 'mark_as_helpful_mah_id' );
		$dbw->insert( 'mark_as_helpful', $row, __METHOD__, [ 'IGNORE' ] );
		$this->setProperty( 'mah_id', $dbw->insertId() );
	}

	/**
	 * Unmark an item as helpful, we don't allow anonymous user to unmark an item
	 * @param User $currentUser the current user who is browsing the site
	 */
	public function unmark( $currentUser ) {
		if ( $currentUser->isAnon() ) {
			return;
		}

		if ( $this->getProperty( 'mah_id' ) ) {

			// Attempt to load from database if not loaded yet
			if ( !$this->loadedFromDatabase ) {
				if ( !$this->loadFromDatabase( [ 'mah_id' => $this->getProperty( 'mah_id' ) ] ) ) {
					return;
				}
			}

			$user = $this->getUser( $currentUser );

			if ( !$user || $user->isAnon() ) {
				return;
			}
			if ( $currentUser->getId() == $user->getId() ) {
				$dbw = wfGetDB( DB_MASTER );

				$dbw->delete(
					'mark_as_helpful',
					[ 'mah_id' => $this->getProperty( 'mah_id' ) ],
					__METHOD__
				);
			}
		}
	}

	/**
	 * Get a list of all users that marked this item as helpful
	 * @param string $type the object type
	 * @param int $item the object id
	 * @return array
	 */
	public static function getMarkAsHelpfulList( $type, $item ) {
		$dbr = wfGetDB( DB_REPLICA );

		$conds = [
			'mah_type' => $type,
			'mah_item' => intval( $item )
		];

		$conds[] = 'mah_user_id = user_id';

		// Grab only one record for the 1st phase
		$res = $dbr->select(
			[ 'mark_as_helpful', 'user' ],
			[ 'mah_id', 'user_id', 'user_name' ],
			$conds,
			__METHOD__,
			[ 'LIMIT' => 1 ]
 );

		$list = [];

		foreach ( $res as $val ) {
			$list[$val->user_id] = [ 'user_name' => $val->user_name,
				'user_id' => $val->user_id ];
		}

		return $list;
	}
}

class MWMarkAsHelpFulItemPropertyException extends Exception {
}
class MWMarkAsHelpFulItemSearchKeyException extends Exception {
}
