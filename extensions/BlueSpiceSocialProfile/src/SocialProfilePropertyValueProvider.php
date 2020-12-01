<?php

namespace BlueSpice\Social\Profile;

use BlueSpice\SMWConnector\PropertyValueProvider;
use BlueSpice\Social\Profile\Field\Title;
use MediaWiki\MediaWikiServices;
use SMWDataItem;
use SMWDIBlob;
use User;

class SocialProfilePropertyValueProvider extends PropertyValueProvider {

	/**
	 *
	 * @return \BlueSpice\SMWConnector\IPropertyValueProvider[]
	 */
	public static function factory() {
		$factory = MediaWikiServices::getInstance()->getService(
			'BSSocialProfileCustomFieldsFactory'
		);
		$propertyValueProviders = [];
		foreach ( $factory->getFieldDefinitions() as $name => $definition ) {
			$smwName = preg_replace( '/\PL/u', '', strtoupper( $name ) );
			if ( empty( $smwName ) ) {
				continue;
			}
			$alias = empty( $definition[Field::KEY_I18N] )
				? $name
				: $definition[Field::KEY_I18N];

			$propertyValueProviders[] = new self( $name, $smwName, $alias, $factory );
		}

		return $propertyValueProviders;
	}

	/**
	 *
	 * @var string
	 */
	private $name = '';

	/**
	 *
	 * @var string
	 */
	private $smwName = '';

	/**
	 *
	 * @var string
	 */
	private $alias = '';

	/**
	 *
	 * @var CustomFieldsFactory
	 */
	protected $factory = null;

	/**
	 *
	 * @param string $name
	 * @param string $smwName
	 * @param string $alias
	 * @param CustomFieldsFactory $factory
	 */
	public function __construct( $name, $smwName, $alias, $factory ) {
		$this->name = $name;
		$this->smwName = $smwName;
		$this->alias = $alias;
		$this->factory = $factory;
	}

	/**
	 *
	 * @var User
	 */
	private $user = null;

	/**
	 *
	 * @param \SESP\AppFactory $appFactory
	 * @param \SMW\DIProperty $property
	 * @param \SMW\SemanticData $semanticData
	 * @return \SMWDataItem
	 */
	public function addAnnotation( $appFactory, $property, $semanticData ) {
		$maybeUserPage = $semanticData->getSubject()->getTitle();
		if ( !$this->initUser( $maybeUserPage ) ) {
			return null;
		}

		$field = $this->factory->factory( $this->name, $this->user );
		if ( $field->isHidden() ) {
			return null;
		}

		return new SMWDIBlob( $field->getValue() );
	}

	/**
	 *
	 * @param Title|null $title
	 * @return bool
	 */
	private function initUser( $title ) {
		if ( $title === null ) {
			return false;
		}

		if ( $title->getNamespace() !== NS_USER || $title->isSubpage() ) {
			return false;
		}

		$this->user = User::newFromName( $title->getText() );
		if ( !$this->user ) {
			return false;
		}

		if ( $this->user->isAnon() ) {
			return false;
		}

		return true;
	}

	/**
	 *
	 * @return int
	 */
	public function getType() {
		return SMWDataItem::TYPE_BLOB;
	}

	/**
	 *
	 * @return string
	 */
	public function getAliasMessageKey() {
		return $this->alias;
	}

	/**
	 *
	 * @return string
	 */
	public function getDescriptionMessageKey() {
		return "{$this->alias}-desc";
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return "_PROFILEINFO{$this->smwName}";
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return "Profile/{$this->name}";
	}

}
