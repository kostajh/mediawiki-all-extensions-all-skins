<?php

namespace MediaWiki\WikispeechSpeechDataCollector\CRUD;

use MediaWiki\WikispeechSpeechDataCollector\Domain\Persistent;
use MediaWiki\WikispeechSpeechDataCollector\Domain\RecordingAnnotation;

/**
 * Class RecordingAnnotationCRUD
 * @package MediaWiki\WikispeechSpeechDataCollector\CRUD
 * @since 0.1.0
 */
class RecordingAnnotationCRUD extends AbstractUuidCRUD {

	/** @var string Name of table in database. */
	public const TABLE = self::TABLES_PREFIX . 'recording_annotation';

	/**
	 * @return string Name of database table representing this class.
	 */
	protected function getTable(): string {
		return self::TABLE;
	}

	private const CLASS_COLUMNS_PREFIX = self::COLUMNS_PREFIX . 'ra_';

	/**
	 * @return string COLUMNS_PREFIX . 'class prefix' . '_'
	 */
	protected function getClassColumnsPrefix(): string {
		return self::CLASS_COLUMNS_PREFIX;
	}

	private const COLUMN_RECORDING = self::CLASS_COLUMNS_PREFIX . 'recording';
	private const COLUMN_START = self::CLASS_COLUMNS_PREFIX . 'start';
	private const COLUMN_END = self::CLASS_COLUMNS_PREFIX . 'end';
	private const COLUMN_STEREOTYPE = self::CLASS_COLUMNS_PREFIX . 'stereotype';
	private const COLUMN_VALUE = self::CLASS_COLUMNS_PREFIX . 'value';

	/**
	 * @return string[] Columns in table required to deserialize an instance, identity excluded.
	 */
	protected function getColumns(): array {
		return [
			self::COLUMN_RECORDING,
			self::COLUMN_START,
			self::COLUMN_END,
			self::COLUMN_STEREOTYPE,
			self::COLUMN_VALUE
		];
	}

	public function instanceFactory(): Persistent {
		return new RecordingAnnotation();
	}

	/**
	 * @param RecordingAnnotation &$instance
	 * @param array $row
	 */
	protected function deserializeRow(
		&$instance,
		array $row
	): void {
		$instance->setRecording( $this->deserializeUuid( $row, self::COLUMN_RECORDING ) );
		$instance->setStart( $this->deserializeInt( $row, self::COLUMN_START ) );
		$instance->setEnd( $this->deserializeInt( $row, self::COLUMN_END ) );
		$instance->setStereotype( $this->deserializeUuid( $row, self::COLUMN_STEREOTYPE ) );
		$instance->setValue( $this->deserializeString( $row, self::COLUMN_VALUE ) );
	}

	/**
	 * @param RecordingAnnotation $instance
	 * @param array &$array
	 */
	protected function serializeFields(
		$instance,
		array &$array
	): void {
		$array[ self::COLUMN_RECORDING ] = $instance->getRecording();
		$array[ self::COLUMN_START ] = $instance->getStart();
		$array[ self::COLUMN_END ] = $instance->getEnd();
		$array[ self::COLUMN_STEREOTYPE ] = $instance->getStereotype();
		$array[ self::COLUMN_VALUE ] = $instance->getValue();
	}

	/**
	 * @param string $recording
	 * @return RecordingAnnotation[]|null
	 */
	public function listByRecording(
		string $recording
	): ?array {
		return $this->listByConditions( [
			self::COLUMN_RECORDING => $recording
		] );
	}

	/**
	 * @param string $recording
	 * @param string $stereotype
	 * @return RecordingAnnotation[]|null
	 */
	public function listByRecordingAndStereotype(
		string $recording,
		string $stereotype
	): ?array {
		return $this->listByConditions( [
			self::COLUMN_RECORDING => $recording,
			self::COLUMN_STEREOTYPE => $stereotype
		] );
	}
}
