<?php

namespace MediaWiki\Extension\FlexiSkin;

use ExtensionRegistry;
use FormatJson;
use MWException;

class FlexiSkinManager implements IFlexiSkinManager {
	/** @var string */
	private $fsPath = '';
	/** @var string */
	private $fileName = 'default.json';
	/** @var IFlexiSkin|null */
	private $currentSkin = null;

	/**
	 * @param string $path
	 */
	public function __construct( $path ) {
		$this->fsPath = $path;
	}

	/**
	 * @param IFlexiSkin $flexiSkin
	 * @return int
	 */
	public function save( IFlexiSkin $flexiSkin ) {
		$fullPath = $this->getFullPath();

		if ( $flexiSkin->getId() === null ) {
			$flexiSkin = new FlexiSkin(
				1,
				$flexiSkin->getName(),
				$flexiSkin->getConfig(),
				$flexiSkin->isActive()
			);
		}

		$json = FormatJson::encode( $flexiSkin );
		$res = (bool)file_put_contents( $fullPath, $json );
		if ( $res ) {
			$this->currentSkin = $flexiSkin;
		}

		return $res;
	}

	/**
	 * @return int
	 */
	public function delete() {
		$fullPath = $this->getFullPath();
		$this->currentSkin = null;
		if ( file_exists( $fullPath ) ) {
			return unlink( $fullPath );
		}

		return true;
	}

	/**
	 * @return IFlexiSkin|null
	 */
	public function getFlexiSkin(): ?IFlexiSkin {
		$this->load();
		return $this->currentSkin;
	}

	/**
	 * @param bool|null $reload
	 * @throws MWException
	 * @return void
	 */
	private function load( $reload = false ) {
		if ( $this->currentSkin === null || $reload ) {
			$fullPath = $this->getFullPath();
			if ( !file_exists( $fullPath ) ) {
				return null;
			}
			$json = file_get_contents( $fullPath );
			$this->currentSkin = FlexiSkin::newFromData( FormatJson::decode( $json, 1 ) );
		}
	}

	/**
	 * @return IFlexiSkin|null
	 */
	public function getActive() : ?IFlexiSkin {
		$current = $this->getFlexiSkin();
		if ( $current && $current->isActive() ) {
			return $current;
		}
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function setActive( $active = true ) : bool {
		$current = $this->getFlexiSkin();
		if ( $current === null ) {
			$current = $this->create( 'default', [] );
		}
		$newSkin = new FlexiSkin(
			$current->getId(),
			$current->getName(),
			$current->getConfig(),
			$active
		);

		return (bool)$this->save( $newSkin );
	}

	/**
	 * @param string $name
	 * @param array $config
	 * @return IFlexiSkin
	 */
	public function create( $name, $config ): IFlexiSkin {
		$existingSkin = $this->getFlexiSkin();
		if ( $existingSkin === null ) {
			return new FlexiSkin( null, $name, $config );
		}

		return $existingSkin;
	}

	/**
	 * @return IPlugin[]
	 */
	public function getPlugins(): array {
		return $this->getRegistryInstances( 'FlexiSkinPluginRegistry', IPlugin::class );
	}

	/**
	 * @return IFlexiSkinSubscriber[]
	 */
	public function getSubscribers() {
		return $this->getRegistryInstances(
			'FlexiSkinSubscriberRegistry',
			IFlexiSkinSubscriber::class
		);
	}

	/**
	 * Make sure filesystem path is available
	 */
	private function assertPath() {
		if ( !file_exists( $this->fsPath ) ) {
			mkdir( $this->fsPath, 0755, true );
		}
	}

	/**
	 * @return string
	 */
	private function getFullPath() {
		$this->assertPath();
		return $this->fsPath . $this->fileName;
	}

	/**
	 *
	 * @param string $name
	 * @param string $targetClass
	 * @return array
	 */
	public function getRegistryInstances( $name, $targetClass ) {
		$values = ExtensionRegistry::getInstance()->getAttribute( $name );
		$items = [];
		foreach ( $values as $key => $factory ) {
			$item = call_user_func_array( $factory, [] );
			if ( $item instanceof $targetClass ) {
				$items[$key] = $item;
			}
		}

		return $items;
	}
}
