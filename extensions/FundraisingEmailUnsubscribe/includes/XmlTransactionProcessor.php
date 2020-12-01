<?php
/**
 * -- License --
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

/**
 * Class that prepares an XML transaction and executes it. Right now it's probably way specific
 * to the SilverPop method. Hopefully easy to extend if required?
 */
class XmlTransactionProcessor {

	/** @var array[] */
	private $mTransactionMap = [];
	/** @var array */
	private $mEnvelope = [];
	/** @var string|int */
	private $mTimeout = 'default';
	/** @var string */
	private $mURL = '';

	/**
	 * Set the remote server we will be connecting too
	 * @param string $url
	 */
	public function setEndpointURL( $url ) {
		$this->mURL = $url;
	}

	/**
	 * Maximum cURL operation time
	 * @param int $timeout
	 */
	public function setTimeout( $timeout ) {
		$this->mTimeout = $timeout;
	}

	/**
	 * The transaction map is an array, keyed on transaction name. Each node should have two
	 * leaves: 'in' and 'out'. Out should be an array of 'key' which is the XML node name, and 'value'
	 * which is the name of the key in the data structure passed to doTransaction(). 'In' should
	 * be a function name that will process the return data. The 'in' function will be called in
	 * the context of a callbackObj - see doTransaction().
	 *
	 * In functions should accept three arguments:
	 *  - string        : Transaction name
	 *  - DOMDocument   : The returned XML object
	 *  - this          : In case the function needs to modify this processor object
	 *
	 * @param array[] $map
	 */
	public function setTransactionMap( array $map ) {
		$this->mTransactionMap = $map;
	}

	/**
	 * Wrap the output XML in more nodes! Each entry in the envelope is a new wrapping element.
	 * @param array $envelope
	 */
	public function setEnvelope( array $envelope ) {
		$this->mEnvelope = $envelope;
	}

	/**
	 * Performs an XML transaction!
	 *
	 * @param string $txnName Name of transaction in transaction map
	 * @param mixed $callbackObj Reference to class that holds the callback function
	 * @param array $outParams Map of parameter data
	 *
	 * @return bool If true, the transaction was completely successful.
	 */
	public function doTransaction( $txnName, $callbackObj, $outParams = [] ) {
		global $wgFundraisingEmailUnsubscribeLogXmlTransactions;

		Logger::pushLabel( 'XMLTransaction' );
		$retval = false;

		// Brief sanity check
		if ( !array_key_exists( $txnName, $this->mTransactionMap ) ) {
			Logger::log( "Transaction '$txnName' does not exist in map!", LOG_ERR, 'XMLTransaction' );

		// And a further check to ensure that the processing callback function does exist
		} elseif ( !method_exists( $callbackObj, $this->mTransactionMap[$txnName]['in'] ) ) {
			$errstr = "Transaction '$txnName' specifies callback function that does not exist in provided object!";
			Logger::log( $errstr, LOG_ERR, 'XMLTransaction' );
			throw new MWException( $errstr );

		// We're sane; process the data
		} else {
			// Construct the DOM tree
			$dom = new DOMDocument( '1.0', 'utf-8' );
			$root = $dom;

			foreach ( $this->mEnvelope as $element ) {
				$el = new DOMElement( $element );
				$root->appendChild( $el );
				$root = $el;
			}
			$el = new DOMElement( $txnName );
			$root->appendChild( $el );
			$root = $el;

			$this->constructDomFromArray( $this->mTransactionMap[$txnName]['out'], $outParams, $root );

			// Send it!
			Logger::log( "Sending transaction '$txnName'" );
			if ( $wgFundraisingEmailUnsubscribeLogXmlTransactions ) {
				Logger::log( "Transaction content: " . $dom->saveXML() );
			}
			$retData = $this->doHttpTransaction( $dom->saveXML(), $this->mTimeout );

			// Call the processing hook if everything was OK
			$retDOM = new DOMDocument();
			if ( ( $retData != false ) && ( $retDOM->loadXML( $retData ) ) ) {
				$function = $this->mTransactionMap[$txnName]['in'];
				$retval = $callbackObj->$function( $txnName, $retDOM, $this );
			} else {
				Logger::log( "Server did not return useful content: " . json_encode( $retData ) );
			}
		}

		Logger::popLabel();
		return $retval;
	}

	/**
	 * Helper function which will create a DOM tree based on an array tree. Will lookup key data
	 * in the $data array.
	 *
	 * @param array $domArray The starting DOM array -- which is not a DOM object
	 * @param array $data The source parameter data
	 * @param DOMElement|DOMNode &$dom The resulting DOM object
	 *
	 * @throws MWException
	 */
	protected function constructDomFromArray( array $domArray, $data, &$dom ) {
		foreach ( $domArray as $element => $value ) {
			if ( is_array( $value ) ) {
				$el = new DOMElement( $element );
				$this->constructDomFromArray( $value, $data, $el );
			} else {
				if ( array_key_exists( $value, $data ) ) {
					$el = new DOMElement( $element, (string)$data[$value] );
				} else {
					throw new MWException( "Data key '$value' does not exist! Node '$element' cannot be created." );
				}
			}

			$dom->appendChild( $el );
		}
	}

	/**
	 * Actually perform the HTTP transaction.
	 *
	 * @param string|array $data
	 * @param string $timeout
	 *
	 * @return bool|string
	 */
	protected function doHttpTransaction( $data, $timeout = 'default' ) {
		$options['method'] = 'POST';
		$options['timeout'] = $this->mTimeout;

		$req = MWHttpRequest::factory( $this->mURL, $options );
		if ( !is_array( $data ) ) {
			$req->setHeader( 'Content-Type', 'text/xml; charset=utf8' );
			$req->setHeader( 'Content-Length', strlen( $data ) );
		}
		$req->setData( $data );
		$status = $req->execute();

		if ( $status->isOK() ) {
			return $req->getContent();
		} else {
			$errors = json_encode( [ 'curl' => $status->errors, 'request' => $req->status->getErrorsArray() ] );
			Logger::log( "Communications failed with : $errors", LOG_ERR, 'XMLTransaction' );
			return false;
		}
	}
}
