<?php

namespace MediaWiki\WikispeechSpeechDataCollector\CRUD;

use MediaWiki\WikispeechSpeechDataCollector\Domain\Persistent;
use MediaWiki\WikispeechSpeechDataCollector\Domain\SkippedManuscriptPrompt;

/**
 * Class SkippedManuscriptPromptCRUD
 * @package MediaWiki\WikispeechSpeechDataCollector\CRUD
 * @since 0.1.0
 */
class SkippedManuscriptPromptCRUD extends AbstractUuidCRUD {

	/** @var string Name of table in database. */
	public const TABLE = self::TABLES_PREFIX . 'skipped_manuscript_prompt';

	/**
	 * @return string Name of database table representing this class.
	 */
	protected function getTable(): string {
		return self::TABLE;
	}

	private const CLASS_COLUMNS_PREFIX = self::COLUMNS_PREFIX . 'smp_';

	/**
	 * @return string COLUMNS_PREFIX . 'class prefix' . '_'
	 */
	protected function getClassColumnsPrefix(): string {
		return self::CLASS_COLUMNS_PREFIX;
	}

	private const COLUMN_MANUSCRIPT_PROMPT = self::CLASS_COLUMNS_PREFIX . 'manuscript_prompt';
	private const COLUMN_USER = self::CLASS_COLUMNS_PREFIX . 'user';
	private const COLUMN_SKIPPED = self::CLASS_COLUMNS_PREFIX . 'skipped';

	/**
	 * @return string[] Columns in table required to deserialize an instance, identity excluded.
	 */
	protected function getColumns(): array {
		return [
			self::COLUMN_MANUSCRIPT_PROMPT,
			self::COLUMN_USER,
			self::COLUMN_SKIPPED,
		];
	}

	public function instanceFactory(): Persistent {
		return new SkippedManuscriptPrompt();
	}

	/**
	 * @param SkippedManuscriptPrompt &$instance
	 * @param array $row
	 */
	protected function deserializeRow(
		&$instance,
		array $row
	): void {
		$instance->setManuscriptPrompt( $this->deserializeUuid( $row, self::COLUMN_MANUSCRIPT_PROMPT ) );
		$instance->setUser( $this->deserializeUuid( $row, self::COLUMN_USER ) );
		$instance->setSkipped( $this->deserializeTimestamp( $row, self::COLUMN_SKIPPED ) );
	}

	/**
	 * @param SkippedManuscriptPrompt $instance
	 * @param array &$array
	 */
	protected function serializeFields(
		$instance,
		array &$array
	): void {
		$array[ self::COLUMN_MANUSCRIPT_PROMPT ] = $instance->getManuscriptPrompt();
		$array[ self::COLUMN_USER ] = $instance->getUser();
		$array[ self::COLUMN_SKIPPED ] = $instance->getSkipped()->getTimestamp( TS_MW );
	}

	/**
	 * @param string $user
	 * @return SkippedManuscriptPrompt[]|null
	 */
	public function listByUser(
		string $user
	): ?array {
		return $this->listByConditions( [
			self::COLUMN_USER => $user
		] );
	}

	/**
	 * @param string $manuscriptPrompt
	 * @return SkippedManuscriptPrompt[]|null
	 */
	public function listByManuscriptPrompt(
		string $manuscriptPrompt
	): ?array {
		return $this->listByConditions( [
			self::COLUMN_MANUSCRIPT_PROMPT => $manuscriptPrompt
		] );
	}

	/**
	 * @param string $user
	 * @param string $manuscriptPrompt
	 * @return SkippedManuscriptPrompt|null
	 */
	public function getByUserAndManuscriptPrompt(
		string $user,
		string $manuscriptPrompt
	): ?Persistent {
		return $this->getByConditions( [
			self::COLUMN_USER => $user,
			self::COLUMN_MANUSCRIPT_PROMPT => $manuscriptPrompt
		] );
	}

}
