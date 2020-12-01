<?php

namespace MediaWiki\Extension\LDAPProvider;

class GroupList {

	/**
	 *
	 * @var array
	 */
	protected $shortNames = [];

	/**
	 *
	 * @var array
	 */
	protected $fullDNs = [];

	/**
	 * @param array $fullDNs the full DNs to handle
	 */
	public function __construct( $fullDNs ) {
		$this->fullDNs = $fullDNs;
	}

	/**
	 * Normalized to lowercase
	 * @return string[]
	 */
	public function getShortNames() {
		if ( !$this->shortNames ) {
			$this->shortNames = $this->makeShortNames();
		}
		return $this->shortNames;
	}

	/**
	 * Raw format
	 * @return string[]
	 */
	public function getFullDNs() {
		return $this->fullDNs;
	}

	/**
	 * Group names to be used in MediaWiki
	 *
	 * @return array
	 */
	protected function makeShortNames() {
		$shortNames = [];
		foreach ( $this->fullDNs as $fullDN ) {
			$dnAttrs = explode( ',', strtolower( $fullDN ) );
			if ( isset( $dnAttrs[0] ) ) {
				$dnAttrs = explode( '=', $dnAttrs[0] );
				if ( isset( $dnAttrs[1] ) ) {
					$shortNames[] = strtolower( $dnAttrs[1] );
				}
			}
		}
		return $shortNames;
	}
}
