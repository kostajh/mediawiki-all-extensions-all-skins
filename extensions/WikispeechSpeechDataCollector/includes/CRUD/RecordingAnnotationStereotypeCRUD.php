<?php

namespace MediaWiki\WikispeechSpeechDataCollector\CRUD;

use MediaWiki\WikispeechSpeechDataCollector\Domain\Persistent;
use MediaWiki\WikispeechSpeechDataCollector\Domain\RecordingAnnotationStereotype;

/**
 * Class RecordingAnnotationStereotypeCRUD
 * @package MediaWiki\WikispeechSpeechDataCollector\CRUD
 * @since 0.1.0
 */
class RecordingAnnotationStereotypeCRUD extends AbstractUuidCRUD {

	/** @var string Name of table in database. */
	public const TABLE = self::TABLES_PREFIX . 'recording_annotation_stereotype';

	/**
	 * @return string Name of database table representing this class.
	 */
	protected function getTable(): string {
		return self::TABLE;
	}

	private const CLASS_COLUMNS_PREFIX = self::COLUMNS_PREFIX . 'ras_';

	/**
	 * @return string COLUMNS_PREFIX . 'class prefix' . '_'
	 */
	protected function getClassColumnsPrefix(): string {
		return self::CLASS_COLUMNS_PREFIX;
	}

	private const COLUMN_VALUE_CLASS = self::CLASS_COLUMNS_PREFIX . 'value_class';
	private const COLUMN_DESCRIPTION = self::CLASS_COLUMNS_PREFIX . 'description';

	/**
	 * @return string[] Columns in table required to deserialize an instance, identity excluded.
	 */
	protected function getColumns(): array {
		return [
			self::COLUMN_VALUE_CLASS,
			self::COLUMN_DESCRIPTION,
		];
	}

	public function instanceFactory(): Persistent {
		return new RecordingAnnotationStereotype();
	}

	/**
	 * @param RecordingAnnotationStereotype &$instance
	 * @param array $row
	 */
	protected function deserializeRow(
		&$instance,
		array $row
	): void {
		$instance->setValueClass( $this->deserializeString( $row, self::COLUMN_VALUE_CLASS ) );
		$instance->setDescription( $this->deserializeString( $row, self::COLUMN_DESCRIPTION ) );
	}

	/**
	 * @param RecordingAnnotationStereotype $instance
	 * @param array &$array
	 */
	protected function serializeFields(
		$instance,
		array &$array
	): void {
		$array[ self::COLUMN_VALUE_CLASS ] = $instance->getValueClass();
		$array[ self::COLUMN_DESCRIPTION ] = $instance->getDescription();
	}
}
