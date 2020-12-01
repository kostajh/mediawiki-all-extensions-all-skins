<?php

require_once dirname( dirname( dirname( __DIR__ ) ) ) . '/maintenance/Maintenance.php';

use BlueSpice\Context;
use BlueSpice\Data\FieldType;
use BlueSpice\Data\Filter\StringValue;
use BlueSpice\Data\Sort;
use BlueSpice\EntityConfigFactory;
use BlueSpice\ExtendedStatistics\Data\Aggregate;
use BlueSpice\ExtendedStatistics\Data\Entity\Collection\Schema;
use BlueSpice\ExtendedStatistics\Data\Entity\Collection\Store;
use BlueSpice\ExtendedStatistics\Data\ReaderParams;
use BlueSpice\ExtendedStatistics\Entity\Collection;
use MediaWiki\MediaWikiServices;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExportCollection extends Maintenance {

	/**
	 *
	 * @var Config
	 */
	private $config = null;

	/**
	 *
	 * @var string[]
	 */
	private $allowedExtensions = [
		'csv',
		'xls',
		'xlsx'
	];

	public function __construct() {
		parent::__construct();
		$this->requireExtension( 'BlueSpiceExtendedStatistics' );
		$this->addOption( 'src', 'Path to a JSON file with reader params data', true );
		$this->addOption( 'output', 'Path to the file, data will be saved in', true );
	}

	public function execute() {
		$this->output( "-------Export---------\n\n" );
		$data = [];
		$this->output( " * Read JSON ..." );
		$file = new SplFileInfo( $this->getOption( 'src' ) );
		if ( !$file->isReadable() ) {
			$this->output( "ERROR: file not readable at {$this->getOption( 'src' )}\n" );
			return;
		}
		$this->output( " OK\n" );
		$this->output( " * Check output format..." );
		$outputFile = new SplFileInfo( $this->getOption( 'output' ) );
		if ( !$outputFile ) {
			$this->output( "output - not a valid file path" );
			return;
		}
		if ( !in_array( $outputFile->getExtension(), $this->allowedExtensions ) ) {
			$this->output( "output - file is in a non supported format {$this->getOption( 'output' )}" );
			$this->output( "supported formats are: " . implode( ',', $this->allowedExtensions ) );
			return;
		}
		$this->output( " OK\n" );
		$data = (array)$this->importJSONFile( $file );
		if ( empty( $data ) ) {
			$data = [];
		}
		$context = $this->getContext();
		$reader = $this->getStore()->getReader( $context );
		$this->output( " * Create reader params..." );
		try {
			$params = new ReaderParams( $this->getParams( $data ) );
		} catch ( Exception $e ) {
			$this->output( $e->getMessage() );
			return;
		}
		$this->output( " OK\n" );
		$this->output( " * Collect data..." );
		$res = $reader->read( $params );
		$this->output( " OK\n" );
		$this->output( " * Create spreadsheet..." );
		$rows = [];
		$type = array_filter( $data[ReaderParams::PARAM_FILTER], function ( $e ) {
			return $e->{StringValue::KEY_PROPERTY} === Collection::ATTR_TYPE;
		} );
		$config = $this->getFactory()->newFromType( $type[0]->{StringValue::KEY_VALUE} );
		$fields = array_merge(
			[ $this->getAggregate( $data )[0]->{Aggregate::KEY_PROPERTY} ],
			array_keys( $config->get( 'PrimaryAttributeDefinitions' ) )
		);
		$schema = $reader->getSchema();
		$rows[] = $fields;
		foreach ( $res->getRecords() as $record ) {
			$col = [];
			foreach ( $fields as $name ) {
				$value = $record->get( $name );
				if ( isset( $schema[$name][Schema::TYPE] ) ) {
					switch ( $schema[$name][Schema::TYPE] ) {
						case FieldType::INT:
							$value = (int)$value;
							break;
						case FieldType::DATE:
							$date = \DateTime::createFromFormat( 'YmdHis', $value );
							$value = $date->format( 'd.m.Y' );
							break;
					}
				}
				$col[] = $value;
			}
			$rows[] = $col;
		}
		try {
			$spreadsheet = new Spreadsheet;
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->fromArray( $rows );
		} catch ( Exception $e ) {
			$this->output( $e->getMessage() );
			return;
		}
		$this->output( " OK\n" );
		$this->output( " * Write output file..." );
		error_log( var_export( $outputFile->getPathname(), 1 ) );
		try {
			$writer = IOFactory::createWriter(
				$spreadsheet,
				ucfirst( $outputFile->getExtension() )
			);
			$writer->save( $outputFile->getPathname() );
		} catch ( Exception $e ) {
			$this->output( $e->getMessage() );
			return;
		}
		$this->output( " OK\n" );
		$this->output( "\n" );
		if ( empty( $outputFile->getRealPath() ) ) {
			$this->output(
				"Unknown error while writing to output file: {$outputFile->getPathname()}"
			);
			return;
		}
		$this->output( $outputFile->getRealPath() );
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected function getParams( array $data ) {
		return [
			ReaderParams::PARAM_LIMIT => $this->getLimit( $data ),
			ReaderParams::PARAM_START => $this->getStart( $data ),
			ReaderParams::PARAM_SORT => $this->getSort( $data ),
			ReaderParams::PARAM_FILTER => $this->getFilter( $data ),
			ReaderParams::PARAM_AGGREGATOR => $this->getAggregate( $data )
		];
	}

	/**
	 * @param array $data
	 * @return int
	 */
	protected function getLimit( array $data ) {
		return ReaderParams::LIMIT_INFINITE;
	}

	/**
	 * @param array $data
	 * @return int
	 */
	protected function getStart( array $data ) {
		return 0;
	}

	/**
	 * @param array $data
	 * @return int
	 */
	protected function getSort( array $data ) {
		if ( !empty( $data[ReaderParams::PARAM_SORT] ) ) {
			return $data[ReaderParams::PARAM_SORT];
		}
		return [ (object)[
			Sort::KEY_PROPERTY => Collection::ATTR_TIMESTAMP_CREATED,
			Sort::KEY_DIRECTION => Sort::ASCENDING
		] ];
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected function getFilter( array $data ) {
		if ( empty( $data[ReaderParams::PARAM_FILTER] ) ) {
			throw new Exception(
				ReaderParams::PARAM_FILTER . " is required"
			);
		}
		if ( !is_array( $data[ReaderParams::PARAM_FILTER] ) ) {
			throw new Exception(
				ReaderParams::PARAM_FILTER . " must be an array of objects"
			);
		}
		$type = array_filter( $data[ReaderParams::PARAM_FILTER], function ( $e ) {
			return $e->{StringValue::KEY_PROPERTY} === Collection::ATTR_TYPE;
		} );
		if ( empty( $type ) ) {
			throw new Exception(
				ReaderParams::PARAM_FILTER . " must contain at least " . Collection::ATTR_TYPE
			);
		}
		$config = $this->getFactory()->newFromType( $type[0]->{StringValue::KEY_VALUE} );
		if ( !$config ) {
			throw new Exception(
				ReaderParams::PARAM_FILTER . ": invalid " . Collection::ATTR_TYPE
				. ": {$type[0]->{StringValue::KEY_VALUE}}"
			);
		}
		return $data[ReaderParams::PARAM_FILTER];
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected function getAggregate( array $data ) {
		if ( empty( $data[ReaderParams::PARAM_AGGREGATOR] ) ) {
			throw new Exception(
				ReaderParams::PARAM_AGGREGATOR . " is required"
			);
		}
		if ( !is_array( $data[ReaderParams::PARAM_AGGREGATOR] ) ) {
			throw new Exception(
				ReaderParams::PARAM_AGGREGATOR . " must be an array of objects"
			);
		}
		$type = array_filter( $data[ReaderParams::PARAM_FILTER], function ( $e ) {
			return $e->{StringValue::KEY_PROPERTY} === Collection::ATTR_TYPE;
		} );
		$config = $this->getFactory()->newFromType( $type[0]->{StringValue::KEY_VALUE} );
		$field = $data[ReaderParams::PARAM_AGGREGATOR][0];
		if ( !isset( $config->get( 'AttributeDefinitions' )[$field->{StringValue::KEY_VALUE}] ) ) {
			if ( !is_array( $data[ReaderParams::PARAM_AGGREGATOR] ) ) {
				throw new Exception(
					$field->{StringValue::KEY_PROPERTY} . " is not aggregatable"
				);
			}
		}
		return $data[ReaderParams::PARAM_AGGREGATOR];
	}

	/**
	 *
	 * @return Store
	 */
	protected function getStore() {
		return new Store;
	}

	/**
	 *
	 * @return IContextSource
	 */
	protected function getContext() {
		$context = RequestContext::getMain();
		$context->setUser( $this->getUser() );
		return new Context( $context, $this->getConfig() );
	}

	/**
	 *
	 * @return User
	 */
	protected function getUser() {
		return $this->getServices()->getService( 'BSUtilityFactory' )->getMaintenanceUser()
			->getUser();
	}

	/**
	 *
	 * @return MediaWikiServices
	 */
	protected function getServices() {
		return MediaWikiServices::getInstance();
	}

	/**
	 *
	 * @return Config
	 */
	public function getConfig() {
		if ( $this->config !== null ) {
			return $this->config;
		}
		$this->config = $this->getServices()->getConfigFactory()->makeConfig( 'bsg' );

		return $this->config;
	}

	/**
	 *
	 * @param SplFileInfo $file
	 * @return \stdClass
	 */
	private function importJSONFile( $file ) {
		return FormatJson::decode( file_get_contents( $file->getPathname() ) );
	}

	/**
	 *
	 * @return EntityConfigFactory
	 */
	private function getFactory() {
		return $this->getServices()->getService( 'BSEntityConfigFactory' );
	}
}

$maintClass = "ExportCollection";
require_once RUN_MAINTENANCE_IF_MAIN;
