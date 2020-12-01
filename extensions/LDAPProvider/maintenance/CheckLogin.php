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

class CheckLogin extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addOption(
			'username',
			"The user name you want to get the infos",
			true,
			true,
			'u'
		);
		$this->addOption(
			'domain',
			"The domain you want to search in",
			true,
			true,
			'd'
		);
		$this->requireExtension( 'LDAPProvider' );
	}

	/**
	 * Where the action happens
	 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
	 */
	public function execute() {
		$username = $this->getOption( "username" );
		$domain = $this->getOption( "domain" );
		$password = $this->readconsole( 'Password:' );

		$factory = ClientFactory::getInstance();
		$client = $factory->getForDomain( $domain );

		$canBind = $client->canBindAs( $username, $password );
		if ( $canBind ) {
			$this->output( "OK\n" );
		} else {
			$this->output( "FAILED\n" );
		}
	}

	private function showValue( array $obj, $recursion = 0 ) {
		foreach ( $obj as $key => $value ) {
			for ( $i = 0; $i < $recursion; $i++ ) {
				$this->output( '  ' );
			}

			if ( is_array( $value ) ) {
				$this->output( $key . ' =>' . "\n" );
				$this->showValue( $value, ++$recursion );
				continue;
			}

			$this->output( $key . ' => ' . $value . "\n" );
		}
	}
}

$maintClass = __NAMESPACE__ . '\\CheckLogin';
require_once RUN_MAINTENANCE_IF_MAIN;
