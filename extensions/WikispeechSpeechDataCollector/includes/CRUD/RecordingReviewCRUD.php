<?php

namespace MediaWiki\WikispeechSpeechDataCollector\CRUD;

use MediaWiki\WikispeechSpeechDataCollector\Domain\Persistent;
use MediaWiki\WikispeechSpeechDataCollector\Domain\RecordingReview;

/**
 * Class RecordingReviewCRUD
 * @package MediaWiki\WikispeechSpeechDataCollector\CRUD
 * @since 0.1.0
 */
class RecordingReviewCRUD extends AbstractUuidCRUD {

	/** @var string Name of table in database. */
	public const TABLE = self::TABLES_PREFIX . 'recording_review';

	/**
	 * @return string Name of database table representing this class.
	 */
	protected function getTable(): string {
		return self::TABLE;
	}

	private const CLASS_COLUMNS_PREFIX = self::COLUMNS_PREFIX . 'rr_';

	/**
	 * @return string COLUMNS_PREFIX . 'class prefix' . '_'
	 */
	protected function getClassColumnsPrefix(): string {
		return self::CLASS_COLUMNS_PREFIX;
	}

	private const COLUMN_CREATED = self::CLASS_COLUMNS_PREFIX . 'created';
	private const COLUMN_VALUE = self::CLASS_COLUMNS_PREFIX . 'value';
	private const COLUMN_REVIEWER = self::CLASS_COLUMNS_PREFIX . 'reviewer';
	private const COLUMN_RECORDING = self::CLASS_COLUMNS_PREFIX . 'recording';

	/**
	 * @return string[] Columns in table required to deserialize an instance, identity excluded.
	 */
	protected function getColumns(): array {
		return [
			self::COLUMN_CREATED,
			self::COLUMN_VALUE,
			self::COLUMN_REVIEWER,
			self::COLUMN_RECORDING
		];
	}

	public function instanceFactory(): Persistent {
		return new RecordingReview();
	}

	/**
	 * @param RecordingReview &$instance
	 * @param array $row
	 */
	protected function deserializeRow(
		&$instance,
		array $row
	): void {
		$instance->setCreated( $this->deserializeTimestamp( $row, self::COLUMN_CREATED ) );
		$instance->setValue( $this->deserializeInt( $row, self::COLUMN_VALUE ) );
		$instance->setReviewer( $this->deserializeUuid( $row, self::COLUMN_REVIEWER ) );
		$instance->setRecording( $this->deserializeUuid( $row, self::COLUMN_RECORDING ) );
	}

	/**
	 * @param RecordingReview $instance
	 * @param array &$array
	 */
	protected function serializeFields(
		$instance,
		array &$array
	): void {
		$array[ self::COLUMN_CREATED ] = $instance->getCreated()->getTimestamp( TS_MW );
		$array[ self::COLUMN_VALUE ] = $instance->getValue();
		$array[ self::COLUMN_REVIEWER ] = $instance->getReviewer();
		$array[ self::COLUMN_RECORDING ] = $instance->getRecording();
	}

	/**
	 * @param string $reviewer
	 * @return RecordingReview[]|null
	 */
	public function listByReviewer(
		string $reviewer
	): ?array {
		return $this->listByConditions( [
			self::COLUMN_REVIEWER => $reviewer
		] );
	}

	/**
	 * @param string $recording
	 * @return RecordingReview[]|null
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
	 * @param string $reviewer
	 * @return RecordingReview|null
	 */
	public function getByRecordingAndReviewer(
		string $recording,
		string $reviewer
	): ?Persistent {
		return $this->getByConditions( [
			self::COLUMN_RECORDING => $recording,
			self::COLUMN_REVIEWER => $reviewer,
		] );
	}
}
