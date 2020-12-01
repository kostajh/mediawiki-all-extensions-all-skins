<?php
/**
 * Example:
 *
 * setClientTierName.php
 *	--client=8b8d1cb5a0d62029dd0051a9e
 *	--tier="Tier 1"
 */

namespace MediaWiki\Extension\OAuthRateLimiter;

use Maintenance;
use MediaWiki\Extensions\OAuth\Repository\ClientRepository;
use MediaWiki\MediaWikiServices;
use MWException;

/**
 * @ingroup Maintenance
 */

// Security: Disable all stream wrappers and reenable individually as needed
foreach ( stream_get_wrappers() as $wrapper ) {
	stream_wrapper_unregister( $wrapper );
}

stream_wrapper_restore( 'file' );
$basePath = getenv( 'MW_INSTALL_PATH' );
if ( $basePath ) {
	if ( !is_dir( $basePath )
		|| strpos( $basePath, '..' ) !== false
		|| strpos( $basePath, '~' ) !== false
	) {
		throw new MWException( "Bad MediaWiki install path: $basePath" );
	}
} else {
	$basePath = __DIR__ . '/../../..';
}

require_once $basePath . '/maintenance/Maintenance.php';

class SetClientTierName extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( "Add/Update tier name for a client" );
		$this->addOption( 'client', 'Client id of the user', true, true );
		$this->addOption( 'tier', 'Tier name to add to database', true, true );

		$this->requireExtension( 'OAuthRateLimiter' );
	}

	public function execute() {
		global $wgOAuthRateLimiterTierConfig;

		$clientID = $this->getOption( 'client' );
		$tierName = $this->getOption( 'tier' );

		if ( !array_key_exists( $tierName, $wgOAuthRateLimiterTierConfig ) ) {
			$this->fatalError( "$tierName must be set in wgOAuthRateLimiterTierConfig" );
		}

		$services = MediaWikiServices::getInstance();

		// Check if $clientID is valid
		$clientRepository = new ClientRepository();
		$res = $clientRepository->getClientEntity( $clientID );

		if ( $res ) {
			$clientTierStore = $services->getService( 'OAuthRateLimiterClientTierStore' );
			$bool = $clientTierStore->setClientTierName( $clientID, $tierName );

			if ( $bool ) {
				$this->output( "Successfully added tier $tierName for $clientID. \n" );
			} else {
				$this->output( "Error adding $tierName for $clientID. \n" );
			}
		} else {
			$this->fatalError( "$clientID is not a valid client id" );
		}
	}
}

$maintClass = SetClientTierName::class;
require_once RUN_MAINTENANCE_IF_MAIN;
