<?php
/**
 * Hook handler base class for Chameleon hook ChameleonSkinTemplateOutputPageBeforeExec
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
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\Social\WikiPage\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\Context;
use BlueSpice\Renderer\Params;
use BlueSpice\Social\Renderer\EntityList;
use BlueSpice\Social\WikiPage\EntityListContext\AfterContent;

class AddTimeline extends ChameleonSkinTemplateOutputPageBeforeExec {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( !$this->skin->getTitle()->exists() ) {
			return true;
		}
		if ( !$this->getConfig()->get( 'SocialWikiPageTimelineAfterContentShow' ) ) {
			return true;
		}
		$namespace = $this->skin->getTitle()->getNamespace();
		$nsBlackList = $this->getConfig()->get(
			'SocialWikiPageTimelineAfterContentNamespaceBlackList'
		);

		if ( in_array( $namespace, $nsBlackList ) ) {
			return true;
		}

		if ( $this->skin->getTitle()->isTalkPage() ) {
			return true;
		}

		$action = $this->getContext()->getRequest()->getVal( 'action', 'view' );
		if ( $action != 'view' && $action != 'submit' ) {
			return true;
		}

		$prop = $this->getServices()->getService( 'BSUtilityFactory' )
			->getPagePropHelper( $this->skin->getTitle() )
			->getPageProp( 'bs_nostash' );
		if ( $prop !== null ) {
			return true;
		}

		return false;
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$renderer = $this->getTimeLineRenderer();

		$this->mergeSkinDataArray(
			\BlueSpice\SkinData::AFTER_CONTENT,
			[ 'socialwikipagestash' => $renderer->render() ]
		);
		return true;
	}

	/**
	 *
	 * @return AfterContent
	 */
	protected function getContext() {
		$factory = $this->getServices()->getService(
			'BSSocialWikiPageEntityFactory'
		);
		$entity = $factory->newFromTitle( $this->skin->getTitle() );
		return new AfterContent(
			new Context(
				parent::getContext(),
				$this->getConfig()
			),
			$this->getConfig(),
			parent::getContext()->getUser(),
			$entity,
			$this->skin->getTitle()
		);
	}

	/**
	 *
	 * @return EntityList
	 */
	protected function getTimeLineRenderer() {
		return $this->getServices()->getService( 'BSRendererFactory' )->get(
			$this->getContext()->getRendererName(),
			new Params( [ 'context' => $this->getContext() ] )
		);
	}

}
