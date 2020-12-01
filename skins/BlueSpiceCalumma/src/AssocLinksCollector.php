<?php

namespace BlueSpice\Calumma;

use Exception;

class AssocLinksCollector {

	/**
	 *
	 * @var array
	 */
	private $linkDefs = [];

	/**
	 *
	 * @var \BlueSpice\ExtensionAttributeBasedRegistry
	 */
	private $registry = null;

	/**
	 *
	 * @var \IContextSource
	 */
	private $context = null;

	/**
	 *
	 * @var \Config
	 */
	private $config = null;

	/**
	 *
	 * @var \BlueSpice\Html\Descriptor\ILink[]
	 */
	private $linkDescriptors = [];

	/**
	 *
	 * @param \BlueSpice\ExtensionAttributeBasedRegistry $registry
	 * @param \IContextSource $context
	 * @param \Config $config
	 */
	public function __construct( $registry, $context, $config ) {
		$this->registry = $registry;
		$this->context = $context;
		$this->config = $config;
	}

	/**
	 *
	 * @return array
	 */
	public function getLinkDefs() {
		$this->fetchAssocLinks();
		$this->convertLinkDescriptorsToLinkDefArrays();

		return $this->linkDefs;
	}

	private function fetchAssocLinks() {
		$registeredFactoryCallbacks = $this->registry->getAllValues();
		$activeProviders = $this->config->get( 'BlueSpiceCalummaAssocLinksEnabledProviders' );

		foreach ( $registeredFactoryCallbacks as $callbackKey => $factoryCallback ) {
			if ( !in_array( $callbackKey, $activeProviders ) ) {
				continue;
			}

			if ( !is_callable( $factoryCallback ) ) {
				throw new Exception( "No valid callback for '$callbackKey'!" );
			}

			$this->linkDescriptors += call_user_func_array(
				$factoryCallback,
				[
					$this->context,
					$this->config
				]
			);
		}
	}

	private function convertLinkDescriptorsToLinkDefArrays() {
		foreach ( $this->linkDescriptors as $linkDescriptor ) {
			$this->linkDefs[] = $this->convertLinkDescriptorToLinkDefArray( $linkDescriptor );
		}
	}

	/**
	 *
	 * @param \BlueSpice\Html\Descriptor\ILink $linkDescriptor
	 * @return array
	 */
	private function convertLinkDescriptorToLinkDefArray( $linkDescriptor ) {
		return [
			'id' => $linkDescriptor->getHtmlId(),
			'text' => $linkDescriptor->getLabel()->plain(),
			'title' => $linkDescriptor->getTooltip()->plain(),
			'href' => $linkDescriptor->getHref(),
			'classes' => implode( ' ', $linkDescriptor->getCSSClasses() ),
			'data' => $this->makeDataString( $linkDescriptor )
		];
	}

	/**
	 *
	 * @param \BlueSpice\Html\Descriptor\ILink $linkDescriptor
	 * @return array
	 */
	private function makeDataString( $linkDescriptor ) {
		$dataKVs = [];
		foreach ( $linkDescriptor->getDataAttributes() as $dataKey => $dataValue ) {
			$dataKVs[] = [
				'key' => $dataKey,
				'value' => \FormatJson::encode( $dataValue )
			];
		}
		return $dataKVs;
	}

}
