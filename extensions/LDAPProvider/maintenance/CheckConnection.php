<?php

namespace MediaWiki\Extension\LDAPProvider\Maintenance;

use Maintenance;
use MediaWiki\Extension\LDAPProvider\ClientFactory;

$maintPath = ( getenv( 'MW_INSTALL_PATH' ) !== false
			  ? getenv( 'MW_INSTALL_PATH' )
			  : __DIR__ . '/../../..' ) . '/maintenance/Maintenance.php';
if ( !file_exists( $maintPath ) ) {
	echo "Please set the environment variable MW_INSTALL_PATH "
		. "to your MediaWiki installation.\n";
	exit( 1 );
}
require_once $maintPath;

class CheckConnection extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addOption(
			'config', 'The json config to test', true, true, "c"
		);
		$this->addOption( 'domain', 'The domain to test', true, true, "d" );
		$this->addArg( 'search', 'Search to execute.', true );
		$this->requireExtension( 'LDAPProvider' );
	}

	/**
	 * Where the action happens
	 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
	 */
	public function execute() {
		// @codingStandardsIgnoreStart
		global $LDAPProviderDomainConfigs;
		// @codingStandardsIgnoreEnd

		$LDAPProviderDomainConfigs = $this->getOption( "config" );
		$factory = ClientFactory::getInstance();
		$client = $factory->getForDomain( $this->getOption( "domain" ) );
		$res = $client->search( $this->getArg( 0 ) );

		$this->showResult( $res );
	}

	private function showResult( array $res ) {
		$this->output( "Found " . $res['count'] . " match(es).\n" );

		foreach ( $res as $index => $val ) {
			if ( is_int( $index ) ) {
				$this->showValue( $val );
			}
		}
	}

	private function showValue( array $obj ) {
		$this->output( "dn: " . $obj['dn'] . "\n" );

		foreach ( $obj as $key => $val ) {
			if ( is_string( $key ) && is_array( $val ) ) {
				$this->output( "  $key:\n" );
				foreach ( $val as $index => $value ) {
					if ( is_int( $index ) ) {
						$this->output( "    $value\n" );
					}
				}
			}
		}
	}
}

$maintClass = __NAMESPACE__ . '\\CheckConnection';
require_once RUN_MAINTENANCE_IF_MAIN;
