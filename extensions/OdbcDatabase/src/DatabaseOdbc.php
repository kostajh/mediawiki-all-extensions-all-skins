<?php
/**
 * Databse implementation for ODBC databases.
 *
 * @file
 * @ingroup Database
 * @author Roger Cass
 */
class DatabaseOdbc extends DatabaseBase {

	public $mAffectedRows = 0;
	public $mRowNum = 0;

	/**
	 * @return string
	 */
	function getType() {
		return 'odbc';
	}

	/**
	 * @param string $sql
	 * @return resource
	 */
	protected function doQuery( $sql ) {
		$res = odbc_exec( $this->mConn, $sql );
		if ( $res ) {
			$this->mAffectedRows = odbc_num_rows( $res );
			$res = new ResultWrapper( $this, $res );
			$this->mRowNum = 0;
		}
		return $res;
	}

	/**
	 * @param string $server
	 * @param string $user
	 * @param string $password
	 * @param string $dbName
	 * @return bool
	 * @throws DBConnectionError
	 */
	function open( $server, $user, $password, $dbName ) {
		if ( !function_exists( 'odbc_connect' ) ) {
			throw new DBConnectionError( $this, wfMessage( 'odbcdatabase-odbc-missing' ) . "\n" );
		}

		$success = false;

		$this->close();
		$this->mServer = $server;
		$this->mUser = $user;
		$this->mPassword = $password;
		$this->mDBname = $dbName;

		$this->mConn = odbc_connect( $server, $user, $password );

		if ( !$this->mConn ) {
			$error = $this->lastError();
			if ( !$error ) {
				$error = $phpError;
			}
			wfLogDBError( wfMessage( 'odbcdatabase-connection-error', $this->mServer, $error ) . "\n" );
		} else {
			$success = true;
		}

		$this->mOpened = $success;
		return $success;
	}

	/**
	 * @return bool
	 */
	function closeConnection() {
		if ( $this->mConn ) {
			return odbc_close( $this->mConn );
		} else {
			return true;
		}
	}

	/**
	 * @param ResultWrapper $res
	 * @throws DBUnexpectedError
	 */
	function freeResult( $res ) {
		if ( $res instanceof ResultWrapper ) {
			$res->free();
		} else {
			$ok = odbc_free_result( $res );
			if ( !$ok ) {
				throw new DBUnexpectedError( $this, wfMessage( 'odbcdatabase-free-error' ) . "\n" );
			}
		}
		$this->mAffectedRows = 0;
		$this->mRowNum = 0;
	}

	/**
	 * @param ResultWrapper $res
	 * @return object|stdClass
	 * @throws DBUnexpectedError
	 */
	function fetchObject( $res ) {
		if ( $res instanceof ResultWrapper ) {
			$res = $res->result;
		}
		$row = odbc_fetch_object( $res );
		if ( $row ) {
			$this->mRowNum++;
		} elseif ( $this->mRowNum <= $this->mAffectedRows ) {
			if ( $this->lastErrno() ) {
				throw new DBUnexpectedError( $this, wfMessage( 'odbcdatabase-fetch-object-error', $this->lastErrno(), htmlspecialchars( $this->lastError() ) ) );
			}
		}
		return $row;
	}

	/**
	 * @param ResultWrapper $res
	 * @return array
	 * @throws DBUnexpectedError
	 */
	function fetchRow( $res ) {
		if ( $res instanceof ResultWrapper ) {
			$res = $res->result;
		}

		$array = null;
		$row = odbc_fetch_row( $res );
		if ( $row ) {
			$this->mRowNum++;
			$nCols = odbc_num_fields( $res );
			for ( $i = 0; $i < $nCols; $i++ ) {
				$array[$i] = odbc_result( $res, $i + 1 );
			}
		} elseif ( $this->mRowNum <= $this->mAffectedRows ) {
			if ( $this->lastErrno() ) {
				throw new DBUnexpectedError( $this, wfMessage( 'odbcdatabase-fetch-row-error', $this->lastErrno(), htmlspecialchars( $this->lastError() ) ) );
			}
		}
		return $array;
	}

	/**
	 * @throws DBUnexpectedError
	 * @param ResultWrapper $res
	 * @return int
	 */
	function numRows( $res ) {
		return $this->mAffectedRows;
	}

	/**
	 * @param ResultWrapper $res
	 * @return int
	 */
	function numFields( $res ) {
		if ( $res instanceof ResultWrapper ) {
			$res = $res->result;
		}
		return odbc_num_fields( $res );
	}

	/**
	 * @param ResultWrapper $res
	 * @param string $n
	 * @return string
	 */
	function fieldName( $res, $n ) {
		if ( $res instanceof ResultWrapper ) {
			$res = $res->result;
		}
		return odbc_field_name( $res, $n );
	}

	/**
	 * @return int
	 */
	function insertId() {
		throw new DBUnexpectedError( $this, wfMessage( 'odbcdatabase-insert-id-unsupported' ) );
		// phpcs:ignore Squiz.PHP.NonExecutableCode.Unreachable
		return 0;
	}

	/**
	 * @param ResultWrapper $res
	 * @return bool
	 */
	function dataSeek( $res ) {
		if ( $res instanceof ResultWrapper ) {
			$res = $res->result;
			$row = odbc_fetch_row( $res );
		}
		return odbc_fetch_row( $res, $row );
	}

	/**
	 * @return int
	 */
	function lastErrno() {
		if ( $this->mConn ) {
			return odbc_error( $this->mConn );
		} else {
			return odbc_error();
		}
	}

	/**
	 * @return string
	 */
	function lastError() {
		if ( $this->mConn ) {
			$error = odbc_errormsg( $this->mConn );
		} else {
			$error = odbc_errormsg();
		}
		if ( $error ) {
			$error .= ' (' . $this->mServer . ')';
		}
		return $error;
	}

	/**
	 * @param string $table
	 * @param string $field
	 * @return bool|MySQLField
	 */
	function fieldInfo( $table, $field ) {
		$table = $this->tableName( $table );
		$res = $this->query( "SELECT * FROM $table LIMIT 1", __METHOD__, true );
		if ( !$res ) {
			return false;
		} elseif ( $res instanceof ResultWrapper ) {
			$res = $res->result;
		}
		$n = odbc_num_fields( $res );
		for ( $i = 0; $i < $n; $i++ ) {
			$name = odbc_field_name( $res, $i );
			if ( $field == $name ) {
				return new OdbcField( $table, $res, $i );
			}
		}
		return false;
	}

	/**
	 * Get information about an index into an object
	 * Returns false if the index does not exist
	 *
	 * @param string $table
	 * @param string $index
	 * @param string $fname
	 * @return false|array
	 */
	function indexInfo( $table, $index, $fname = __METHOD__ ) {
		/*
		 * For now, always return false. Not sure how
		 * to generically find this info using no
		 * knowledge of the underlying DB.
		 */
		return false;
	}

	/**
	 * @return int
	 */
	function affectedRows() {
		return $this->mAffectedRows;
	}

	/**
	 * @param string $s
	 *
	 * @return string
	 */
	function strencode( $s ) {
	# Should not be called by us
		return str_replace( "'", "''", $s );
	}

	/**
	 * If it's a string, adds quotes and backslashes
	 * Otherwise returns as-is
	 *
	 * @param string $s
	 *
	 * @return string
	 */
	function addQuotes( $s ) {
		if ( $s instanceof Blob ) {
			return "'" . $s->fetch( $s ) . "'";
		} else {
			return parent::addQuotes( $s );
		}
	}

	/**
	 * @return string
	 */
	function getServerVersion() {
		$ver = wfMessage( 'odbcdatabase-unknown-server-version' );
		$result = odbc_data_source( $this->mConn, SQL_FETCH_FIRST );
		while ( $result ) {
			if ( strtolower( $this->mServer ) == strtolower( $result['server'] ) ) {
				$ver = $result['description'];
				break;
			} else { $result = odbc_data_source( $this->mConn, SQL_FETCH_NEXT );
			}
		}
		return $ver;
	}

	/**
	 * @return string
	 */
	public static function getSoftwareLink() {
		return '[http://php.net/manual/en/book.uodbc.php]';
	}

	/**
	 * End a transaction
	 *
	 * @param string $fname
	 */
	function doCommit( $fname = 'DatabaseBase::commit' ) {
		if ( $this->mTrxLevel ) {
			// $this->query( 'COMMIT TRAN', $fname );
			$this->mTrxLevel = 0;
		}
	}

	/**
	 * Begin a transaction, committing any previously open transaction
	 * @param string $fname
	 */
	function doBegin( $fname = 'DatabaseBase::begin' ) {
		// $this->query( 'BEGIN', $fname );
		$this->mTrxLevel = 1;
	}
}
