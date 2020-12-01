<?php

namespace MediaWiki\WikispeechSpeechDataCollector\CRUD;

use MediaWiki\WikispeechSpeechDataCollector\Domain\Persistent;
use MediaWiki\WikispeechSpeechDataCollector\Domain\UserDialect;

/**
 * Class UserDialectCRUD
 * @package MediaWiki\WikispeechSpeechDataCollector\CRUD
 * @since 0.1.0
 */
class UserDialectCRUD extends AbstractUuidCRUD {

	/** @var string Name of table in database. */
	public const TABLE = self::TABLES_PREFIX . 'user_dialect';

	/**
	 * @return string Name of database table representing this class.
	 */
	protected function getTable(): string {
		return self::TABLE;
	}

	private const CLASS_COLUMNS_PREFIX = self::COLUMNS_PREFIX . 'ud_';

	/**
	 * @return string COLUMNS_PREFIX . 'class prefix' . '_'
	 */
	protected function getClassColumnsPrefix(): string {
		return self::CLASS_COLUMNS_PREFIX;
	}

	private const COLUMN_USER = self::CLASS_COLUMNS_PREFIX . 'user';
	private const COLUMN_LANGUAGE = self::CLASS_COLUMNS_PREFIX . 'language';
	private const COLUMN_SPOKEN_PROFICIENCY_LEVEL =
		self::CLASS_COLUMNS_PREFIX . 'spoken_proficiency_level';
	private const COLUMN_LOCATION = self::CLASS_COLUMNS_PREFIX . 'location';

	/**
	 * @return string[] Columns in table required to deserialize an instance, identity excluded.
	 */
	protected function getColumns(): array {
		return [
			self::COLUMN_USER,
			self::COLUMN_LANGUAGE,
			self::COLUMN_SPOKEN_PROFICIENCY_LEVEL,
			self::COLUMN_LOCATION
		];
	}

	public function instanceFactory(): Persistent {
		return new UserDialect();
	}

	/**
	 * @param UserDialect &$instance
	 * @param array $row
	 */
	protected function deserializeRow(
		&$instance,
		array $row
	): void {
		$instance->setUser( $this->deserializeUuid( $row, self::COLUMN_USER ) );
		$instance->setLanguage( $this->deserializeUuid( $row, self::COLUMN_LANGUAGE ) );
		$instance->setSpokenProficiencyLevel(
			$this->deserializeInt( $row, self::COLUMN_SPOKEN_PROFICIENCY_LEVEL ) );
		$instance->setLocation( $this->deserializeString( $row, self::COLUMN_LOCATION ) );
	}

	/**
	 * @param UserDialect $instance
	 * @param array &$array
	 */
	protected function serializeFields(
		$instance,
		array &$array
	): void {
		$array[ self::COLUMN_USER ] = $instance->getUser();
		$array[ self::COLUMN_LANGUAGE ] = $instance->getLanguage();
		$array[ self::COLUMN_SPOKEN_PROFICIENCY_LEVEL ] = $instance->getSpokenProficiencyLevel();
		$array[ self::COLUMN_LOCATION ] = $instance->getLocation();
	}

	/**
	 * @param string $user
	 * @return UserDialect[]|null
	 */
	public function listByUser(
		string $user
	): ?array {
		return $this->listByConditions( [
			self::COLUMN_USER => $user
		] );
	}
}
