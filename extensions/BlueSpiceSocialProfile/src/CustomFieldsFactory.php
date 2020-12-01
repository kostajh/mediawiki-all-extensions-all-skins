<?php
/**
 * CustomFieldsFactory class for BlueSpice
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

use BlueSpice\IRegistry;
use Config;

class CustomFieldsFactory extends FieldsFactory {
	const KEY_TYPE = 'type';

	/**
	 *
	 * @var IRegistry
	 */
	protected $registry = null;

	/**
	 *
	 * @param Config $config
	 * @param IRegistry $registry
	 */
	public function __construct( $config, $registry ) {
		$this->registry = $registry;
		parent::__construct( $config );
	}

	/**
	 *
	 * @param string $type
	 * @return string | false
	 */
	protected function getCallback( $type ) {
		return $this->registry->getValue( $type, false );
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

		$cnfgDefs = $this->config->get( 'BSSocialProfileCustomFields' );
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
	 *
	 * @param string $name
	 * @param array $definition
	 * @return array
	 */
	protected function makeFieldDefinition( $name, $definition ) {
		if ( !empty( $definition[static::KEY_CALLBACK] ) ) {
			return parent::makeFieldDefinition( $name, $definition );
		}
		$definition[static::KEY_CALLBACK] = $this->getCallback(
			$definition[static::KEY_TYPE]
		);
		if ( !$definition[static::KEY_CALLBACK] ) {
			return false;
		}
		return parent::makeFieldDefinition( $name, $definition );
	}
}
