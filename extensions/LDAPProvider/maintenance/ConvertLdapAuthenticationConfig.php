<?php

namespace MediaWiki\Extension\LDAPProvider\Maintenance;

$maintPath = ( getenv( 'MW_INSTALL_PATH' ) !== false
			  ? getenv( 'MW_INSTALL_PATH' )
			  : __DIR__ . '/../../..' ) . '/maintenance/Maintenance.php';
if ( !file_exists( $maintPath ) ) {
	echo "Please set the environment variable MW_INSTALL_PATH "
		. "to your MediaWiki installation.\n";
	exit( 1 );
}
require_once $maintPath;

use FormatJson;
use Maintenance;
use SplFileInfo;

class ConvertLdapAuthenticationConfig extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addOption(
			'output', 'Where to put the json file', true, true
		);
		$this->requireExtension( 'LDAPProvider' );
	}

	protected $newConfig = [];

	protected $oldConfigVarNames = [
		'wgLDAPServerNames' => 'connection.server',
		'wgLDAPProxyAgent' => 'connection.user',
		'wgLDAPProxyAgentPassword' => 'connection.password',
		'wgLDAPOptions' => 'connection.options',
		'wgLDAPPort' => 'connection.port',
		'wgLDAPEncryptionType' => 'connection.enctype',
		'wgLDAPBaseDNs' => 'connection.basedn',
		'wgLDAPGroupBaseDNs' => 'connection.groupbasedn',
		'wgLDAPUserBaseDNs' => 'connection.userbasedn',
		'wgLDAPSearchAttributes' => 'connection.userdnsearchattribute',
		'wgLDAPSearchString' => 'connection.searchstring',
		// 'wgLDAPGroupObjectclass' => 'basic.group.objectclass',
		// 'wgLDAPGroupAttribute' => 'basic.group.attribute',
		// 'wgLDAPGroupsUseMemberOf' => 'basic.group.use-member-of',
		'wgLDAPPreferences' => 'userinfo.attributes-map',
		'wgLDAPRequiredGroups' => 'authorization.rules.groups.required',
		'wgLDAPExcludedGroups' => 'authorization.rules.groups.excluded',
		'wgLDAPLocallyManagedGroups' => 'groupsync.locally-managed',
		// 'wgLDAPGroupsPrevail' => 'groupsync.prevail'
	];

	/**
	 * Where the action happens
	 */
	public function execute() {
		foreach ( $this->oldConfigVarNames as $varName => $newSettingPath ) {
			$this->addToNewConfig( $varName, $newSettingPath );
		}

		$file = new SplFileInfo( $this->getOption( 'output' ) );
		$filename = $file->getPathname();
		if ( $file->isDir() ) {
			$filename .= '/' . wfWikiID() . '.ldap.json';
		}

		file_put_contents(
			$filename,
			FormatJson::encode( $this->newConfig, true )
		);
	}

	/**
	 * Add an old global variable to the config
	 *
	 * @param string $varName the global variable name to get
	 * @param string $newSettingPath where the new storage place is
	 * @SuppressWarnings(SuperGlobals)
	 */
	protected function addToNewConfig( $varName, $newSettingPath ) {
		if ( !isset( $GLOBALS[$varName] ) ) {
			return;
		}
		foreach ( $GLOBALS[$varName] as $domain => $oldConfig ) {
			$parts = explode( '.', "$domain.$newSettingPath" );
			$config =& $this->newConfig;
			foreach ( $parts as $part ) {
				if ( !isset( $config[$part] ) ) {
					$config[$part] = [];
				}
				$config =& $config[$part];
			}
			$config = $oldConfig;
		}
	}
}

$maintClass = __NAMESPACE__ . '\\ConvertLdapAuthenticationConfig';
require_once RUN_MAINTENANCE_IF_MAIN;
