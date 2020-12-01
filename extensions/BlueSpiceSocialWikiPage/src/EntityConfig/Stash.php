<?php

namespace BlueSpice\Social\WikiPage\EntityConfig;

use BlueSpice\Data\FieldType;
use BlueSpice\Social\Data\Entity\Schema;
use BlueSpice\Social\EntityConfig\Text;
use BlueSpice\Social\WikiPage\Entity\Stash as Entity;
use MediaWiki\MediaWikiServices;

/**
 * Stash class for BSSocial extension
 * @package BlueSpiceStashs
 * @subpackage BSSocial
 */
class Stash extends Text {

	/**
	 *
	 * @return array
	 */
	public function addGetterDefaults() {
		return [];
	}

	/**
	 *
	 * @return string
	 */
	public function get_EntityClass() {
		return "\\BlueSpice\\Social\\WikiPage\\Entity\\Stash";
	}

	/**
	 *
	 * @return string
	 */
	protected function get_Renderer() {
		return 'social-wikipage-entity-stash';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_ModuleScripts() {
		$dropzone = MediaWikiServices::getInstance()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceMultiUpload' )
			? [ "ext.bluespice.social.entity.stash.dropzone" ]
			: [];
		$insertFile = [ "ext.bluespice.social.entity.stash.insertfile" ];
		$insertLink = MediaWikiServices::getInstance()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceInsertLink' )
			? [ "ext.bluespice.social.entity.stash.insertlink" ]
			: [];
		// InserLink is also not working currently
		$insertLink = [];
		return array_merge(
			parent::get_ModuleScripts(),
			[ 'ext.bluespice.social.entity.stash' ],
			$dropzone,
			$insertFile,
			$insertLink
		);
	}

	/**
	 *
	 * @return array
	 */
	protected function get_ModuleStyles() {
		$insertFile = MediaWikiServices::getInstance()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceInsertFile' )
			? [ "ext.bluespice.insertFile.styles" ]
			: [];
		return array_merge(
			parent::get_ModuleStyles(),
			$insertFile
		);
	}

	/**
	 *
	 * @return string[]
	 */
	protected function get_ModuleEditScripts() {
		return array_merge( parent::get_ModuleEditScripts(), [
			'ext.bluespice.social.entity.editor.stash'
		] );
	}

	/**
	 *
	 * @return string
	 */
	protected function get_TypeMessageKey() {
		return 'bs-socialwikipage-stashtype';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_HeaderMessageKeyCreateNew() {
		return 'bs-socialwikipage-entitystash-header-create';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_HeaderMessageKey() {
		return 'bs-socialwikipage-entitystash-header';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_VarMessageKeys() {
		return array_merge(
			parent::get_VarMessageKeys(),
			[
				Entity::ATTR_WIKI_PAGE_ID => 'bs-socialwikipage-var-wikipageid'
			]
		);
	}

	/**
	 *
	 * @return array
	 */
	protected function get_AttributeDefinitions() {
		return array_merge(
			parent::get_AttributeDefinitions(),
			[
				Entity::ATTR_WIKI_PAGE_ID => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => true,
					Schema::TYPE => FieldType::INT,
					Schema::INDEXABLE => true,
					Schema::STORABLE => true,
				],
			]
		);
	}

}
