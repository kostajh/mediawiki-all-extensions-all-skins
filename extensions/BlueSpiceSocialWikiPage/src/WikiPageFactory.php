<?php
/**
 * WikiPageEntityFactory class for BlueSpice
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
namespace BlueSpice\Social\WikiPage;

use BlueSpice\Data\Filter\Numeric;
use BlueSpice\Data\ReaderParams;
use BlueSpice\EntityFactory;
use BlueSpice\Social\WikiPage\Entity\WikiPage;
use BlueSpice\Social\WikiPage\EntityListContext\SpecialWikiPages;
use MediaWiki\MediaWikiServices;

class WikiPageFactory extends EntityFactory {

	/**
	 *
	 * @var WikiPage[]
	 */
	protected $wikiPageInstances = [];

	/**
	 * @param \Title $title
	 * @return WikiPage | null
	 */
	public function newFromTitle( \Title $title ) {
		if ( !$title->exists() || $title->isTalkPage() ) {
			return null;
		}

		if ( isset( $this->wikiPageInstances[$title->getArticleID()] ) ) {
			return $this->wikiPageInstances[$title->getArticleID()];
		}

		$context = new \BlueSpice\Context(
			\RequestContext::getMain(),
			$this->config
		);
		$serviceUser = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getMaintenanceUser()->getUser();

		$listContext = new SpecialWikiPages(
			$context,
			$context->getConfig(),
			$serviceUser,
			null
		);
		$filters = $listContext->getFilters();
		$filters[] = (object)[
			Numeric::KEY_PROPERTY => WikiPage::ATTR_WIKI_PAGE_ID,
			Numeric::KEY_VALUE => $title->getArticleID(),
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
				WikiPage::ATTR_WIKI_PAGE_ID => $title->getArticleID(),
				WikiPage::ATTR_TYPE => WikiPage::TYPE
			] );
		}
		$this->wikiPageInstances[$title->getArticleID()] = $instance;
		return $instance;
	}

	/**
	 *
	 * @return \BlueSpice\Social\Data\Entity\Store
	 * @throws \MWException
	 */
	protected function getStore() {
		$config = $this->configFactory->newFromType( WikiPage::TYPE );
		$storeClass = $config->get( 'StoreClass' );
		if ( !class_exists( $storeClass ) ) {
			throw new \MWException( "Store class '$storeClass' not found" );
		}
		return new $storeClass();
	}
}
