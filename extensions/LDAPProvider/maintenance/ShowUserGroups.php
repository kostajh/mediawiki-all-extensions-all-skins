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

class ShowUserGroups extends Maintenance {

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

		$factory = ClientFactory::getInstance();
		$client = $factory->getForDomain( $domain );
		$userGroups = $client->getUserGroups( $username );

		$this->showValue( $userGroups );
	}

	/**
	 *
	 * @param \MediaWiki\Extension\LDAPProvider\GroupList $groupList
	 */
	private function showValue( $groupList ) {
		$this->output( "Full DNs:\n" );
		foreach ( $groupList->getFullDNs() as $fullDN ) {
			$this->output( "\t$fullDN\n" );
		}

		$this->output( "Short names:\n" );
		foreach ( $groupList->getShortNames() as $shortName ) {
			$this->output( "\t$shortName\n" );
		}
	}
}

$maintClass = __NAMESPACE__ . '\\ShowUserGroups';
require_once RUN_MAINTENANCE_IF_MAIN;
