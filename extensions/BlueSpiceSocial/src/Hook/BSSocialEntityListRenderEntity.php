<?php
/**
 * Hook handler base class for BlueSpice hook BSSocialEntityListRenderEntity
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
 * @package    BlueSpiceSocial
 * @copyright  Copyright (C) 2018 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */
namespace BlueSpice\Social\Hook;

use BlueSpice\Social\Entity;
use BlueSpice\Social\Renderer\Entity as Renderer;
use BlueSpice\Social\Renderer\EntityList;

abstract class BSSocialEntityListRenderEntity extends \BlueSpice\Hook {

	/**
	 * Instance of the entity list
	 * @var EntityList
	 */
	protected $entityList = null;

	/**
	 *
	 * @var Entity
	 */
	protected $entity = null;

	/**
	 *
	 * @var Renderer
	 */
	protected $renderer = null;

	/**
	 *
	 * @var string
	 */
	protected $renderType = null;

	/**
	 * Located in \BlueSpice\Social\Renderer\EntityList::renderEntitiy. Before
	 * a single entity get rendered.
	 * @param EntityList $entityList
	 * @param Entity $entity
	 * @param Renderer &$renderer
	 * @param string &$renderType
	 * @return bool
	 */
	public static function callback( $entityList, $entity, &$renderer, &$renderType ) {
		$className = static::class;
		$hookHandler = new $className(
			$entityList->getContext(),
			$entity->getConfig(),
			$entityList,
			$entity,
			$renderer,
			$renderType
		);
		return $hookHandler->process();
	}

	/**
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param EntityList $entityList
	 * @param Entity $entity
	 * @param Renderer &$renderer
	 * @param string &$renderType
	 */
	public function __construct( $context, $config, $entityList, $entity, &$renderer,
		&$renderType ) {
		parent::__construct( $context, $config );

		$this->entityList = $entityList;
		$this->entity = $entity;
		$this->renderer = &$renderer;
		$this->renderType = &$renderType;
	}
}
