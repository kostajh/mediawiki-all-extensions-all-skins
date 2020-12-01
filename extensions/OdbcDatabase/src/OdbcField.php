<?php
/**
 * Utility class.
 * @ingroup Database
 */
class OdbcField implements Field {
	private $name, $tableName, $type;

	/**
	 * @param string $tableName
	 * @param ResultWrapper $res
	 * @param string $n
	 */
	function __construct( $tableName, $res, $n ) {
		$this->name = odbc_field_name( $res, $n );
		$this->tableName = $tableName;
		$this->type = odbc_field_type( $res, $n );
	}

	/**
	 * @return string
	 */
	function name() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	function tableName() {
		return $this->tableName;
	}

	/**
	 * @return string
	 */
	function type() {
		return $this->type;
	}

	/**
	 * @return bool
	 */
	function isNullable() {
		return $this->false;
	}
}
