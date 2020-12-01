<?php

/**
 * A script that generates a JSON that can be used
 * to run Mocha tests for all enabled extensions
 */

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/Maintenance.php";

class GenerateMochaConfig extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->requireExtension( 'GenerateMochaConfig' );
		$this->addOption( 'config', 'Load an existing mocharc.json file', false, true );
	}

	public function execute() {
		$extensions = ExtensionRegistry::getInstance()->getAllThings();
		$config = [];
		$results = [];

		if ( $this->hasOption( 'config' ) ) {
			global $IP;
			$configValue = $this->getOption( 'config' );

			if ( strpos( $configValue, '.json' ) == false ) {
				$this->fatalError( "--config only accepts .json files" );
			}

			$file = $IP . '/' . $configValue;

			if ( file_exists( $file ) ) {
				$fileContent = file_get_contents( $file );
				$status = FormatJson::parse( $fileContent, FormatJson::FORCE_ASSOC );

				if ( !$status->isOK() ) {
					$this->fatalError( "Unable to parse $configValue due to: " . $status->getMessage() );
				}

				$config = $status->getValue();
			} else {
				$this->fatalError( "No file found at: $file" );
			}
		}

		foreach ( $extensions as $extension ) {
			$path = $extension['path'];

			if ( strpos( $path, 'extension.json' ) !== false ) {
				$directory = explode( 'extension.json', $path )[0];
				$testDirectory = $directory . 'tests/mocha';

				if ( is_dir( $testDirectory ) ) {
					$results[] = $testDirectory;
				}
			}
		}

		$defaultConfig = [
			'exit' => true,
			'recursive' => true,
			'timeout' => 0,
			'extension' => [ 'js' ],
		];

		$finalConfig = array_merge( $defaultConfig, $config );
		$finalConfig['extension'] = array_unique( array_merge( $defaultConfig['extension'],
			$config['extension'] ?? [] ) );
		$finalConfig['spec'] = array_unique( array_merge( $results, $config['spec'] ?? [] ) );

		echo FormatJson::encode( $finalConfig, true ) . "\n";
	}
}

$maintClass = GenerateMochaConfig::class;
require_once RUN_MAINTENANCE_IF_MAIN;
