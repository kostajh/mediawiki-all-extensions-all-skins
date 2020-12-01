<?php

namespace MediaWiki\Extensions\TrustedXFF;

use Wikimedia\IPSet;
use Wikimedia\IPUtils;

class TrustedXFF {
	/**
	 * @internal For tests only
	 */
	public static $instance;

	// FIXME: IPv6 ranges need to be put here for now, there is no
	// trusted-hosts.txt support. The ranges are too large to be expanded with
	// the current CDB system.
	private const IPV6_RANGES = [
		// Opera Mini
		// Source: Email 22-May-2013
		'2001:4c28:1::/48',
		'2001:4c28:2000::/36',
		'2001:4c28:3000::/36'
	];

	/** @var IPSet|null */
	private $ipv6Set;

	/** @var array|null */
	private $hosts;

	/**
	 * @param string &$ip
	 * @param bool &$trusted
	 * @return bool
	 */
	public static function onIsTrustedProxy( &$ip, &$trusted ) {
		// Don't want to override hosts that are already trusted
		if ( !$trusted ) {
			$trusted = self::getInstance()->isTrusted( $ip );
		}
		return true;
	}

	/**
	 * @return TrustedXFF
	 */
	private static function getInstance() {
		if ( !self::$instance ) {
			self::$instance = new TrustedXFF;
		}
		return self::$instance;
	}

	/**
	 * @param string $ip
	 * @return bool
	 */
	private function isTrusted( $ip ) {
		if ( $this->hosts === null ) {
			$this->hosts = require dirname( __DIR__ ) . '/trusted-hosts.php';
		}

		// Try single host
		$hex = IPUtils::toHex( $ip );
		$data = $this->hosts[ $hex ] ?? null;
		if ( $data ) {
			return true;
		}

		// Try IPv6 ranges
		if ( substr( $hex, 0, 2 ) === 'v6' ) {
			if ( $this->ipv6Set === null ) {
				$this->ipv6Set = new IPSet( self::IPV6_RANGES );
			}

			return $this->ipv6Set->match( $ip );
		}

		return false;
	}
}
