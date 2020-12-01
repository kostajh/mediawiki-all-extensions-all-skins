<?php
/**
 * FieldsFactory class for BlueSpice
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Patric Wirth
 * @package    BlueSpiceFoundation
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\Social\Profile;

use BlueSpice\Social\Data\Entity\Schema;
use Config;
use MWException;
use RequestContext;
use User;

class FieldsFactory {
	const KEY_CALLBACK = 'callback';

	/**
	 *
	 * @var array
	 */
	protected $defintions = null;
	protected $fields = null;

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 * @param Config $config
	 */
	public function __construct( $config ) {
		$this->config = $config;
	}

	/**
	 *
	 * @param string $name
	 * @param User|null $user
	 * @return FieldDefinition | false
	 */
	public function factory( $name, User $user = null ) {
		if ( empty( $name ) ) {
			return false;
		}
		if ( !$user instanceof User ) {
			$user = RequestContext::getMain();
		}
		if ( $user->isAnon() ) {
			return false;
		}
		$definition = $this->getFieldDefinition( $name );
		if ( !$definition ) {
			return false;
		}
		if ( !isset( $definition[static::KEY_CALLBACK] )
			|| !is_callable( $definition[static::KEY_CALLBACK] ) ) {
			return false;
		}
		$field = call_user_func_array( $definition[static::KEY_CALLBACK], [
			$this->config,
			$name,
			$definition,
			$user
		] );
		if ( !$field instanceof IField ) {
			return false;
		}
		return $field;
	}

	/**
	 *
	 * @return array
	 */
	public function getRegisteredDefinitions() {
		return array_keys( $this->getFieldDefinitions() );
	}

	/**
	 *
	 * @return array
	 */
	public function getFieldDefinitions() {
		if ( $this->defintions ) {
			return $this->defintions;
		}
		$this->defintions = [];
		$cnfgDefs = $this->config->get( 'BSSocialProfileFields' );
		foreach ( $cnfgDefs as $name => $cnfgDef ) {
			$definition = $this->makeFieldDefinition( $name, $cnfgDef );
			if ( !$definition ) {
				continue;
			}
			$this->defintions[$name] = $definition;
		}
		return $this->defintions;
	}

	/**
	 * @param string $name
	 * @return array
	 */
	protected function getFieldDefinition( $name ) {
		$definitions = $this->getFieldDefinitions();
		if ( !isset( $definitions[$name] ) ) {
			return false;
		}
		return $definitions[$name];
	}

	/**
	 * @param string $name
	 * @param array $definition
	 * @return array
	 */
	protected function makeFieldDefinition( $name, $definition ) {
		if ( !isset( $definition[static::KEY_CALLBACK] ) ) {
			throw new MWException(
				"Missing " . static::KEY_CALLBACK . " for $name"
			);
		}
		list( $classname, $callback ) = explode(
			'::',
			$definition[static::KEY_CALLBACK]
		);
		if ( !class_exists( $classname ) ) {
			throw new MWException( "Class '$classname' does not exist!" );
		}
		$definition = array_merge(
			$this->getSchemaDefintion( $classname ),
			$definition
		);
		return $definition;
	}

	/**
	 *
	 * @param string $classname
	 * @return array
	 */
	public function getSchemaDefintion( $classname ) {
		return [
			Schema::FILTERABLE => $classname::DFLT_FILTERABLE,
			Schema::SORTABLE => $classname::DFLT_SORTABLE,
			Schema::STORABLE => $classname::DFLT_STORABLE,
			Schema::INDEXABLE => $classname::DFLT_INDEXABLE,
			Schema::TYPE => $classname::DFLT_TYPE,
		];
	}
}
