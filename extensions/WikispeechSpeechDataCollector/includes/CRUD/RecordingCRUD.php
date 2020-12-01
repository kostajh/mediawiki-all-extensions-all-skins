<?php

namespace MediaWiki\WikispeechSpeechDataCollector\CRUD;

use MediaWiki\WikispeechSpeechDataCollector\Domain\Persistent;
use MediaWiki\WikispeechSpeechDataCollector\Domain\Recording;

/**
 * Class RecordingCRUD
 * @package MediaWiki\WikispeechSpeechDataCollector\CRUD
 * @since 0.1.0
 */
class RecordingCRUD extends AbstractUuidCRUD {

	/** @var string Name of table in database. */
	public const TABLE = self::TABLES_PREFIX . 'recording';

	/**
	 * @return string Name of database table representing this class.
	 */
	protected function getTable(): string {
		return self::TABLE;
	}

	private const CLASS_COLUMNS_PREFIX = self::COLUMNS_PREFIX . 'r_';

	/**
	 * @return string COLUMNS_PREFIX . 'class prefix' . '_'
	 */
	protected function getClassColumnsPrefix(): string {
		return self::CLASS_COLUMNS_PREFIX;
	}

	private const COLUMN_RECORDED = self::CLASS_COLUMNS_PREFIX . 'recorded';
	private const COLUMN_VOICE_OF = self::CLASS_COLUMNS_PREFIX . 'voice_of';
	private const COLUMN_SPOKEN_DIALECT = self::CLASS_COLUMNS_PREFIX . 'spoken_dialect';
	private const COLUMN_MANUSCRIPT_PROMPT = self::CLASS_COLUMNS_PREFIX . 'manuscript_prompt';

	/**
	 * @return string[] Columns in table required to deserialize an instance, identity excluded.
	 */
	protected function getColumns(): array {
		return [
			self::COLUMN_RECORDED,
			self::COLUMN_VOICE_OF,
			self::COLUMN_SPOKEN_DIALECT,
			self::COLUMN_MANUSCRIPT_PROMPT
		];
	}

	public function instanceFactory(): Persistent {
		return new Recording();
	}

	/**
	 * @param Recording &$instance
	 * @param array $row
	 */
	protected function deserializeRow(
		&$instance,
		array $row
	): void {
		$instance->setRecorded( $this->deserializeTimestamp( $row, self::COLUMN_RECORDED ) );
		$instance->setVoiceOf( $this->deserializeUuid( $row, self::COLUMN_VOICE_OF ) );
		$instance->setSpokenDialect( $this->deserializeUuid( $row, self::COLUMN_SPOKEN_DIALECT ) );
		$instance->setManuscriptPrompt( $this->deserializeUuid( $row, self::COLUMN_MANUSCRIPT_PROMPT ) );
	}

	/**
	 * @param Recording $instance
	 * @param array &$array
	 */
	protected function serializeFields(
		$instance,
		array &$array
	): void {
		$array[ self::COLUMN_RECORDED ] = $instance->getRecorded()->getTimestamp( TS_MW );
		$array[ self::COLUMN_VOICE_OF ] = $instance->getVoiceOf();
		$array[ self::COLUMN_SPOKEN_DIALECT ] = $instance->getSpokenDialect();
		$array[ self::COLUMN_MANUSCRIPT_PROMPT ] = $instance->getManuscriptPrompt();
	}

	/**
	 * @param string $voiceOf
	 * @return Recording[]|null
	 */
	public function listByVoiceOf(
		string $voiceOf
	): ?array {
		return $this->listByConditions( [
			self::COLUMN_VOICE_OF => $voiceOf
		] );
	}

	/**
	 * @param string $manuscriptPrompt
	 * @return Recording[]|null
	 */
	public function listByManuscriptPrompt(
		string $manuscriptPrompt
	): ?array {
		return $this->listByConditions( [
			self::COLUMN_MANUSCRIPT_PROMPT => $manuscriptPrompt
		] );
	}

	/**
	 * @param string $voiceOf
	 * @param string $manuscriptPrompt
	 * @return Recording|null
	 */
	public function getByVoiceOfAndManuscriptPrompt(
		string $voiceOf,
		string $manuscriptPrompt
	): ?Persistent {
		return $this->getByConditions( [
			self::COLUMN_VOICE_OF => $voiceOf,
			self::COLUMN_MANUSCRIPT_PROMPT => $manuscriptPrompt,
		] );
	}
}
