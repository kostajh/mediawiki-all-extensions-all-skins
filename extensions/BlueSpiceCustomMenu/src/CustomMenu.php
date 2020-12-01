<?php

namespace BlueSpice\CustomMenu;

use BlueSpice\CustomMenu\Renderer\Menu;
use BlueSpice\Data\Record;
use BlueSpice\Data\RecordSet;
use BlueSpice\Renderer\Params;
use BlueSpice\UtilityFactory;
use MediaWiki\MediaWikiServices;

abstract class CustomMenu implements ICustomMenu {

	/**
	 * @var RecordSet
	 */
	protected $data = null;

	/**
	 * @var string
	 */
	protected $key = '';

	/**
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @var UtilityFactory
	 */
	protected $util = null;

	/**
	 * @param \Config $config
	 * @param string $key
	 * @param UtilityFactory $util
	 */
	protected function __construct( \Config $config, $key, UtilityFactory $util ) {
		$this->config = $config;
		$this->key = $key;
		$this->util = $util;
	}

	/**
	 *
	 * @param \Config $config
	 * @param string $key
	 * @param UtilityFactory|null $util
	 * @return CustomMenu
	 */
	public static function getInstance( \Config $config, $key, UtilityFactory $util = null ) {
		if ( !$util ) {
			$util = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' );
		}
		return new static( $config, $key, $util );
	}

	/**
	 * @return Params
	 */
	protected function getParams() {
		return new Params( [
			Menu::PARAM_CUSTOM_MENU => $this
		] );
	}

	/**
	 * @return Menu
	 */
	public function getRenderer() {
		return MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			'custommenu',
			$this->getParams()
		);
	}

	/**
	 * @return RecordSet
	 */
	public function getData() {
		$this->data = $this->util->getCacheHelper()->get( $this->getCacheKey() );
		if ( $this->data ) {
			return $this->data;
		}
		$this->data = new RecordSet( $this->getRecords() );
		$this->util->getCacheHelper()->set(
			$this->getCacheKey(),
			$this->data,
			// max cache time 24h
			60 * 1440
		);
		return $this->data;
	}

	/**
	 * @param Record[] $records
	 * @return Record[]
	 */
	protected function getDefaultRecords( $records = [] ) {
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSCustomMenuDefaultRecords',
			[
				$this->getKey(),
				&$records
			]
		);
		return $records;
	}

	/**
	 * @return Record[]
	 */
	abstract protected function getRecords();

	/**
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * @return string
	 */
	protected function getCacheKey() {
		return $this->util->getCacheHelper()->getCacheKey(
			'BlueSpice',
			'CustomMenu',
			static::class
		);
	}

	public function invalidate() {
		$this->util->getCacheHelper()->invalidate( $this->getCacheKey() );
		$this->data = null;
	}

	/**
	 * @return int
	 */
	public function numberOfLevels() {
		return 1;
	}

	/**
	 * @return int
	 */
	public function numberOfMainEntries() {
		return static::NUM_ENTRIES_UNLIMITED;
	}

	/**
	 * @return int
	 */
	public function numberOfSubEntries() {
		return static::NUM_ENTRIES_UNLIMITED;
	}

	/**
	 * @return string|null
	 */
	public function getEditURL() {
		return null;
	}
}
