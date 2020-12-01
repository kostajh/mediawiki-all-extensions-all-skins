<?php

namespace MediaWiki\WikispeechSpeechDataCollector\CRUD;

use MediaWiki\WikispeechSpeechDataCollector\Domain\Manuscript;
use MediaWiki\WikispeechSpeechDataCollector\Domain\Persistent;

/**
 * Class ManuscriptCRUD
 * @package MediaWiki\WikispeechSpeechDataCollector\CRUD
 * @since 0.1.0
 */
class ManuscriptCRUD extends AbstractUuidCRUD {

	/** @var string Name of table in database. */
	public const TABLE = self::TABLES_PREFIX . 'manuscript';

	/**
	 * @return string Name of database table representing this class.
	 */
	protected function getTable(): string {
		return self::TABLE;
	}

	private const CLASS_COLUMNS_PREFIX = self::COLUMNS_PREFIX . 'm_';

	/**
	 * @return string COLUMNS_PREFIX . 'class prefix' . '_'
	 */
	protected function getClassColumnsPrefix(): string {
		return self::CLASS_COLUMNS_PREFIX;
	}

	private const COLUMN_NAME = self::CLASS_COLUMNS_PREFIX . 'name';
	private const COLUMN_CREATED = self::CLASS_COLUMNS_PREFIX . 'created';
	private const COLUMN_DISABLED = self::CLASS_COLUMNS_PREFIX . 'disabled';
	private const COLUMN_LANGUAGE = self::CLASS_COLUMNS_PREFIX . 'language';
	private const COLUMN_DOMAIN = self::CLASS_COLUMNS_PREFIX . 'domain';

	/**
	 * @return string[] Columns in table required to deserialize an instance, identity excluded.
	 */
	protected function getColumns(): array {
		return [
			self::COLUMN_NAME,
			self::COLUMN_CREATED,
			self::COLUMN_DISABLED,
			self::COLUMN_LANGUAGE,
			self::COLUMN_DOMAIN
		];
	}

	public function instanceFactory(): Persistent {
		return new Manuscript();
	}

	/**
	 * @param Manuscript &$instance
	 * @param array $row
	 */
	protected function deserializeRow(
		&$instance,
		array $row
	): void {
		$instance->setName( $this->deserializeString( $row, self::COLUMN_NAME ) );
		$instance->setCreated( $this->deserializeTimestamp( $row, self::COLUMN_CREATED ) );
		$instance->setDisabled( $this->deserializeTimestamp( $row, self::COLUMN_DISABLED ) );
		$instance->setLanguage( $this->deserializeUuid( $row, self::COLUMN_LANGUAGE ) );
		$instance->setDomain( $this->deserializeUuid( $row, self::COLUMN_DOMAIN ) );
	}

	/**
	 * @param Manuscript $instance
	 * @param array &$array
	 */
	protected function serializeFields(
		$instance,
		array &$array
	): void {
		$array[ self::COLUMN_NAME ] = $instance->getName();
		$array[ self::COLUMN_CREATED ] = $instance->getCreated()->getTimestamp( TS_MW );
		$array[ self::COLUMN_DISABLED ] =
			$instance->getDisabled() ? $instance->getDisabled()->getTimestamp( TS_MW ) : null;
		$array[ self::COLUMN_LANGUAGE ] = $instance->getLanguage();
		$array[ self::COLUMN_DOMAIN ] = $instance->getDomain();
	}

	/**
	 * @param string $domain
	 * @return Manuscript[]|null
	 */
	public function listByDomain(
		string $domain
	): ?array {
		return $this->listByConditions( [
			self::COLUMN_DOMAIN => $domain
		] );
	}

	/**
	 * @param string $language
	 * @return Manuscript[]|null
	 */
	public function listByLanguage(
		string $language
	): ?array {
		return $this->listByConditions( [
			self::COLUMN_LANGUAGE => $language
		] );
	}

}
