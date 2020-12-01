<?php
/**
 * Model database abstraction class.
 *
 * Supprted types are: int, string, timestamp
 *
 * @file Model.php
 * @ingroup ORM
 *
 * @licence GNU GPL v3 or later
 * @author Vedmaka < god.vedmaka@gmail.com >
 */

abstract class Model
{

	/*
	 * Result of creation
	 * @var bool
	 */
	public $error;

	/*
	 * Id of entity
	 * @var int
	 */
	protected $id;

	/*
	 * Table name where entities stored
	 * @var string
	 */
	protected static $table;

	/*
	 * Array of column names
	 * @var array
	 */
	protected $properties = array();

	/*
	 * Array of values
	 * @var array
	 */
	protected $values = array();

	/*
	 * Flag for new enitity of loaded one
	 * @var bool
	 */
	private $isNew = false;

	/**
	 * Constructor receives id of target entity or null to create one
	 * @param null $id
	 */
	function __construct( $id = null )
	{

		$this->error = false;

		/* Id auto-increment on all entities */
		$this->properties['id'] = 'int';

		if ( $id == null ) {

			/* Create new entity */
			$this->isNew = true;

			foreach ( $this->properties as $prop => $type ) {
				$this->values[$prop] = null;
			}

		} else {

			$this->load( $id );

		}

	}

	/**
	 * Loads entity from database.
	 *
	 * @param $id
	 * @return bool
	 * @throws Exception
	 */
	public function load( $id )
	{

		$this->isNew = false;

		$dbr = wfGetDB( DB_REPLICA );

		/* Fetch entity by id */
		$entity = $dbr->selectRow( static::$table,
			'*',
			array( 'id' => intval( $id ) )
		);

		if ( !$entity ) {
			$this->error = true;
			return false;
			throw new Exception(get_called_class() . ": there is no entity with id = $id in table " . static::$table);
		}

		/* Set all properties by $properties array */
		foreach ( $this->properties as $prop => $type ) {

			if ( !isset($entity->$prop) && $entity->$prop !== null ) throw new Exception(get_called_class() . ": No such field $prop in table " . static::$table . ".");

			if ( $prop == 'id' ) {
				$this->id = $entity->$prop;
				continue;
			}

			/* Data type: int or string */
			if ( $type == 'int' ) {
				$this->values[$prop] = intval( $entity->$prop );
			} else {
				$this->values[$prop] = $entity->$prop;
			}

		}

		return true;

	}

	/**
	 * Count entities. There some code that should be fixed.
	 * @param string $where
	 * @param array $options
	 * @return int
	 */
	public function count( $where = 'all', $options = array() )
	{

		$dbr = wfGetDB( DB_REPLICA );

		if ( !is_array( $where ) && $where == 'all' ) {

			/* Fetch all entities */
			$collection = $dbr->select( static::$table,
				'id',
				array(),
				__METHOD__,
				$options
			);

		} else {

			/* Fetch conditional entities */
			$collection = $dbr->select( static::$table,
				'id',
				$where,
				__METHOD__,
				$options
			);

		}

		/* Check result */
		if ( !$collection ) return 0;

		$count = $dbr->numRows( $collection );

		return $count;

	}

	/**
	 * Save entity instance in database.
	 * @param null $propvalues
	 * @param bool $reserved
	 * @return int
	 * @throws Exception
	 */
	public function save( $propvalues = null, $reserved = false )
	{

		$dbr = wfGetDB( DB_MASTER );
		$dbr->begin();

		$sourceprops = $this->properties;
		$insertId = null;

		foreach ( $sourceprops as $prop => $type ) {
			$sourceprops[$prop] = (isset($this->values[$prop])) ? $this->values[$prop] : '';
		}

		/* New values from passed $propvalues array  */
		if ( $propvalues != null && is_array( $propvalues ) ) {

			if ( array_key_exists( 'id', $propvalues ) )
				throw new Exception('ORM.Model: `id` parameter cant be passed in poperties array.');

			foreach ( $propvalues as $prop => $value ) {
				if ( isset($this->properties[$prop]) ) $sourceprops[$prop] = $value;
			}
		}

		$sourceprops['id'] = $this->id;

		if ( $this->isNew ) {

			/* New entity */
			$dbr->insert( static::$table,
				$sourceprops,
				__METHOD__
			);

			$insertId = $dbr->insertId();

		} else {

			/* Check id, just for sure */
			if ( !is_numeric( $this->id ) ) throw new Exception('WikivoteVoting: There is no `id` field on exesting entity update. Fatal error.');

			/* Update existing entity */
			$dbr->update( static::$table,
				$sourceprops,
				array( 'id' => $this->id ),
				__METHOD__
			);

			$insertId = $this->id;

		}

		$dbr->commit();

		return $insertId;

	}

	/*
	 * Removes entity from database
	 */
	public function delete()
	{

		$dbr = wfGetDB( DB_MASTER );

		$dbr->delete( static::$table,
			array( 'id' => $this->id )
		);

		$dbr->commit();

	}

	/**
	 * Fetch collection of entities
	 * @param string|array $options
	 */
	public static function find( $where = 'all', $options = array() )
	{

		$dbr = wfGetDB( DB_REPLICA );

		if ( !is_array( $where ) && $where == 'all' ) {

			/* Fetch all entities */
			$collection = $dbr->select( static::$table,
				'id',
				array(),
				__METHOD__,
				$options
			);

		} else {

			/* Fetch conditional entities */
			$collection = $dbr->select( static::$table,
				'id',
				$where,
				__METHOD__,
				$options
			);

		}

		/* Check result */
		if ( !$collection ) return array();

		/* Generate array of entities for return */
		$entities = array();
		foreach ( $collection as $row ) {

			/* Create entity */
			$modelClass = get_called_class();
			$entity = new $modelClass($row->id);

			$entities[] = $entity;

		}

		if ( !count( $entities ) ) return array();

		return $entities;

	}

	/**
	 * Provides properties auto setter override in inherited class
	 * @param $name
	 * @param $value
	 */
	public function __set( $name, $value )
	{

		/* Property from array set */
		if ( array_key_exists( $name, $this->properties ) ) {

			$method = '_set' . mb_strtoupper( $name[0] ) . substr( $name, 1 );

			/* Call override method */
			if ( method_exists( $this, $method ) ) {

				/* Method sould return value */
				$this->values[$name] = $this->$method( $value );

			} else {

				/* Just write value to storage */

				if ( $this->properties[$name] == 'int' && !is_integer( $value ) ) {
					$value = intval( $value );
				}
				if ( $this->properties[$name] == 'string' && is_integer( $value ) ) {
					$value = "$value";
				}
				if ( $this->properties[$name] == 'timestamp' && is_integer( $value ) ) {
					$dbr = wfGetDB( DB_REPLICA );
					$value = $dbr->timestamp( $value );
				}

				$this->values[$name] = $value;
			}

		} else {

			//TODO: decide if this necessary
			$this->$name = $value;

		}

	}

	/**
	 * Provides properties auto getter override in inherited class
	 * @param $name
	 * @return null
	 */
	public function __get( $name )
	{

		/* Property from array set */
		if ( array_key_exists( $name, $this->properties ) ) {

			$method = '_get' . mb_strtoupper( $name[0] ) . substr( $name, 1 );

			/* Call override method */
			if ( method_exists( $this, $method ) ) {

				/* Method should return value */
				return $this->$method( $this->values[$name] );

			} else {

				/* Just return value from storage */
				return $this->values[$name];

			}

		}

		return null;

	}

	/**
	 * Invalidates model from request
	 * @param null $input
	 */
	public function validate( $req = array(), $input = null )
	{

		global $wgRequest;

		if ( !is_array( $req ) ) $req = array( $req );

		if ( $input == null ) {
			$input = $wgRequest->getValues();
		}

		/* Fix: mediawiki pass `title` param and override POST `title` param. fix it */
		if ( isset($_POST['title']) ) {
			$input['title'] = htmlspecialchars( $_POST['title'] );
		}

		foreach ( $input as $param => $value ) {

			#if (is_array($value) && !method_exists($this,'_set'.$param)) continue;

			if ( array_key_exists( $param, $this->properties ) ) {

				/* Validation rules
				*  TODO: make them work
				*/
				switch ( $this->properties[$param] ) {

					case 'int':
						//if (!is_integer($value)) return false;
						break;

					case 'string':
						//if (!is_string($value)) return false;
						break;

				}

				/* Required */
				if ( (empty($value) || $value == '') && array_key_exists( $param, $req ) ) return false;

				$this->$param = $value;

			}

		}

		return true;

	}

	/**
	 * Returns id of entity
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}


}
