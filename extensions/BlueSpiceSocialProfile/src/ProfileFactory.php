<?php
/**
 * ProfileFactory class for BlueSpice
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

use BlueSpice\Data\Filter\Numeric;
use BlueSpice\Data\ReaderParams;
use BlueSpice\EntityFactory;
use BlueSpice\Social\Profile\Entity\Profile;
use BlueSpice\Social\Profile\EntityListContext\SpecialProfiles;
use MediaWiki\MediaWikiServices;

class ProfileFactory extends EntityFactory {

	/**
	 *
	 * @var Profile[]
	 */
	protected $profileInstances = [];

	/**
	 * @param \User $user
	 * @return Profile | null
	 */
	public function newFromUser( \User $user ) {
		if ( $user->isAnon() ) {
			return null;
		}
		if ( isset( $this->profileInstances[$user->getId()] ) ) {
			return $this->profileInstances[$user->getId()];
		}

		$context = new \BlueSpice\Context(
			\RequestContext::getMain(),
			MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' )
		);
		$serviceUser = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getMaintenanceUser()->getUser();

		$listContext = new SpecialProfiles(
			$context,
			$context->getConfig(),
			$serviceUser,
			null
		);
		$filters = $listContext->getFilters();
		$filters[] = (object)[
			Numeric::KEY_PROPERTY => Profile::ATTR_OWNER_ID,
			Numeric::KEY_VALUE => $user->getId(),
			Numeric::KEY_COMPARISON => Numeric::COMPARISON_EQUALS,
			Numeric::KEY_TYPE => 'numeric'
		];

		$instance = null;
		$params = new ReaderParams( [
			'filter' => $filters,
			'sort' => $listContext->getSort(),
			'limit' => 1,
			'start' => 0,
		] );
		$res = $this->getStore()->getReader( $listContext )->read( $params );
		foreach ( $res->getRecords() as $row ) {
			$instance = $this->newFromObject( $row->getData() );
		}
		if ( !$instance ) {
			$instance = $this->newFromObject( (object)[
				Profile::ATTR_OWNER_ID => $user->getId(),
				Profile::ATTR_TYPE => Profile::TYPE
			] );
		}
		$this->profileInstances[$user->getId()] = $instance;
		return $instance;
	}

	/**
	 *
	 * @return \BlueSpice\Social\Data\Entity\Store
	 * @throws \MWException
	 */
	protected function getStore() {
		$config = $this->configFactory->newFromType( Profile::TYPE );
		$storeClass = $config->get( 'StoreClass' );
		if ( !class_exists( $storeClass ) ) {
			throw new \MWException( "Store class '$storeClass' not found" );
		}
		return new $storeClass();
	}
}
