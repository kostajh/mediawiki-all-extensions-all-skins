<?php
/**
 * Hook handler base class for Cameleon hook ChameleonSkinTemplateOutputPageBeforeExec
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
namespace BlueSpice\Social\Tags\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\Context;
use BlueSpice\Renderer\Params;
use BlueSpice\SkinData;
use BlueSpice\Social\Tags\EntityListContext\AfterContent;

class AddTimeline extends ChameleonSkinTemplateOutputPageBeforeExec {

	protected function skipProcessing() {
		if ( !$this->skin->getTitle()->exists() ) {
			return true;
		}
		if ( !$this->getConfig()->get( 'SocialTagsTimelineAfterContentShow' ) ) {
			return true;
		}
		$namespace = $this->skin->getTitle()->getNamespace();
		$nsBlackList = $this->getConfig()->get(
			'SocialTagsTimelineAfterContentNamespaceBlackList'
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

		return false;
	}

	protected function doProcess() {
		$context = new AfterContent(
			new Context(
				$this->getContext(),
				$this->getConfig()
			),
			$this->getConfig(),
			$this->getContext()->getUser()
		);
		$renderer = $this->getServices()->getService( 'BSRendererFactory' )->get(
			'entitylist',
			new Params( [ 'context' => $context ] )
		);
		$item = [
			'socialtags' => $renderer->render(),
		];

		$this->mergeSkinDataArray( SkinData::AFTER_CONTENT, $item );
		return true;
	}

}
