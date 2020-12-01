<?php

namespace MediaWiki\WikispeechSpeechDataCollector\CRUD;

use MediaWiki\WikispeechSpeechDataCollector\Domain\Language;
use MediaWiki\WikispeechSpeechDataCollector\Domain\Persistent;

/**
 * Class LanguageCRUD
 * @package MediaWiki\WikispeechSpeechDataCollector\CRUD
 * @since 0.1.0
 */
class LanguageCRUD extends AbstractUuidCRUD {

	/** @var string Name of table in database. */
	public const TABLE = self::TABLES_PREFIX . 'language';

	private const CLASS_COLUMNS_PREFIX = self::COLUMNS_PREFIX . 'l_';

	/**
	 * @return string COLUMNS_PREFIX . 'class prefix' . '_'
	 */
	protected function getClassColumnsPrefix(): string {
		return self::CLASS_COLUMNS_PREFIX;
	}

	private const COLUMN_NATIVE_NAME = self::CLASS_COLUMNS_PREFIX . 'native_name';
	private const COLUMN_ISO639A1 = self::CLASS_COLUMNS_PREFIX . 'iso639a1';
	private const COLUMN_ISO639A2B = self::CLASS_COLUMNS_PREFIX . 'iso639a2b';
	private const COLUMN_ISO639A2T = self::CLASS_COLUMNS_PREFIX . 'iso639a2t';
	private const COLUMN_ISO639A3 = self::CLASS_COLUMNS_PREFIX . 'iso639a3';

	/**
	 * @return string Name of database table representing this class.
	 */
	protected function getTable(): string {
		return self::TABLE;
	}

	/**
	 * @return string[] Columns in table required to deserialize an instance, identity excluded.
	 */
	protected function getColumns(): array {
		return [
			self::COLUMN_NATIVE_NAME,
			self::COLUMN_ISO639A1,
			self::COLUMN_ISO639A2B,
			self::COLUMN_ISO639A2T,
			self::COLUMN_ISO639A3
		];
	}

	public function instanceFactory(): Persistent {
		return new Language();
	}

	/**
	 * @param Language &$instance Instance
	 * @param array $row
	 */
	protected function deserializeRow(
		&$instance,
		array $row
	): void {
		$instance->setNativeName( $this->deserializeString( $row, self::COLUMN_NATIVE_NAME ) );
		$instance->setIso639a1( $this->deserializeString( $row, self::COLUMN_ISO639A1 ) );
		$instance->setIso639a2b( $this->deserializeString( $row, self::COLUMN_ISO639A2B ) );
		$instance->setIso639a2t( $this->deserializeString( $row, self::COLUMN_ISO639A2T ) );
		$instance->setIso639a3( $this->deserializeString( $row, self::COLUMN_ISO639A3 ) );
	}

	/**
	 * @param Language $instance
	 * @param array &$array
	 */
	protected function serializeFields(
		$instance,
		array &$array
	): void {
		$array[ self::COLUMN_NATIVE_NAME ] = $instance->getNativeName();
		$array[ self::COLUMN_ISO639A1 ] = $instance->getIso639a1();
		$array[ self::COLUMN_ISO639A2B ] = $instance->getIso639a2b();
		$array[ self::COLUMN_ISO639A2T ] = $instance->getIso639a2t();
		$array[ self::COLUMN_ISO639A3 ] = $instance->getIso639a3();
	}

	/**
	 * @param string $iso639a1
	 * @return Language|null
	 */
	public function findLanguageByIso639a1(
		string $iso639a1
	): ?Persistent {
		return $this->getByConditions( [
			self::COLUMN_ISO639A1 => $iso639a1
		] );
	}

	/**
	 * @param string $iso639a2b
	 * @return Language|null
	 */
	public function findLanguageByIso639a2b(
		string $iso639a2b
	): ?Persistent {
		return $this->getByConditions( [
			self::COLUMN_ISO639A2B => $iso639a2b
		] );
	}

	/**
	 * @param string $iso639a2t
	 * @return Language|null
	 */
	public function findLanguageByIso639a2t(
		string $iso639a2t
	): ?Persistent {
		return $this->getByConditions( [
			self::COLUMN_ISO639A2T => $iso639a2t
		] );
	}

	/**
	 * @param string $iso639a3
	 * @return Language|null
	 */
	public function findLanguageByIso639a3(
		string $iso639a3
	): ?Persistent {
		return $this->getByConditions( [
			self::COLUMN_ISO639A3 => $iso639a3
		] );
	}
}
