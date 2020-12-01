<?php

namespace MediaWiki\Extension\FlexiSkin\Plugin;

use File;
use MediaWiki\Extension\FlexiSkin\IFlexiSkin;
use MediaWiki\Extension\FlexiSkin\PluginBase;
use MediaWiki\MediaWikiServices;
use Title;

class Images extends PluginBase {

	/**
	 * @return array with js files
	 */
	public function getJSFiles() {
		return [
			'js/ui/plugin/Images.js'
		];
	}

	/**
	 * @return array with css files
	 */
	public function getCSSFiles() {
		return [
			'all' => [
				'stylesheets/plugin/Images.css'
			]
		];
	}

	/**
	 * @return array valid skinnames
	 */
	public function getValidSkins() {
		return [
			'*'
		];
	}

	/**
	 * @return string Name of the module
	 */
	public function getPluginName() {
		return 'flexiskin.ui.plugin.Images';
	}

	/**
	 * @inheritDoc
	 */
	public function adaptConfiguration( &$config ) {
		if ( !isset( $config['images'] ) || !is_array( $config['images'] ) ) {
			return;
		}
		foreach ( $config['images'] as $key => $data ) {
			$config['images'][$key] = $this->expandUrl( $data );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function setDefaults( IFlexiSkin $skin, &$config ) {
		// Does not make sense for images
	}

	/**
	 * @param array $data
	 * @return mixed
	 */
	private function expandUrl( $data ) {
		if ( !isset( $data['filename'] ) ) {
			return $data;
		}

		$title = Title::makeTitle( NS_FILE, $data['filename'] );
		if ( $title->exists() ) {
			$file = MediaWikiServices::getInstance()->getRepoGroup()->findFile( $title );
			if ( $file instanceof File ) {
				$data['url'] = $file->getFullUrl();
			}
		}

		return $data;
	}
}
