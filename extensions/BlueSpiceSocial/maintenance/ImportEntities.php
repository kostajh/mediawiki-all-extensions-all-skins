<?php

$IP = dirname( dirname( dirname( dirname( __DIR__ ) ) ) );

require_once "$IP/maintenance/Maintenance.php";

class ImportEntities extends Maintenance {
	public function __construct() {
		parent::__construct();
		// $this->requireExtension( 'BlueSpiceSocial' ); //Enable for REL1_28+

		$this->addOption( 'src', 'Path to a JSON file with entitiy data', true );
	}

	public function execute() {
		$oFile = new SplFileInfo( $this->getOption( 'src' ) );

		$this->importJSONFile( $oFile );
	}

	/**
	 *
	 * @param SplFileInfo $oFile
	 */
	protected function importJSONFile( $oFile ) {
		$oData = FormatJson::decode( file_get_contents( $oFile->getPathname() ) );
		if ( isset( $oData->entities ) ) {
			foreach ( $oData->entities as $oEntiy ) {
				$oEntity = BSSocialEntity::newFromObject( $oEntiy );
				if ( $oEntity instanceof BSSocialEntity === false ) {
					$this->output( 'E' );
					continue;
				}
				$oStatus = $oEntity->save();
				if ( $oStatus->isOK() ) {
					$this->output( '.' );
				} else {
					$this->output( 'F' );
				}
			}
		}
	}

}

$maintClass = 'importEntities';
require_once RUN_MAINTENANCE_IF_MAIN;
