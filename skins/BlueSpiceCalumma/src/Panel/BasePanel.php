<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\Calumma\CookieHandler;
use BlueSpice\Calumma\IPanel;
use QuickTemplate;
use Skins\Chameleon\IdRegistry;

abstract class BasePanel implements IPanel {

	/**
	 *
	 * @var QuickTemplate
	 */
	protected $skintemplate = null;

	/**
	 *
	 * @param QuickTemplate $skintemplate
	 */
	public function __construct( QuickTemplate $skintemplate ) {
		$this->skintemplate = $skintemplate;
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return IdRegistry::getRegistry()->getId();
	}

	/**
	 *
	 * @return string
	 */
	public function getBadge() {
		return '';
	}

	/**
	 *
	 * @return array
	 */
	public function getContainerClasses() {
		return [];
	}

	/**
	 *
	 * @return array
	 */
	public function getContainerData() {
		return [];
	}

	/**
	 *
	 * @return string
	 */
	public function getTool() {
		return '';
	}

	/**
	 *
	 * @return string
	 */
	public function getTriggerCallbackFunctionName() {
		return '';
	}

	/**
	 * @return array|string[]
	 */
	public function getTriggerRLDependencies() {
		return [];
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @return bool
	 */
	public function shouldRender( $context ) {
		if ( $context->getOutput()->isPrintable() ) {
			return false;
		}
		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getCookiePrefix() {
		return 'Calumma_CollapsePanel_';
	}

	/**
	 *
	 * @return bool
	 */
	public function getPanelCollapseState() {
		$htmlId = $this->getHtmlId();
		$cookiePrefix = $this->getCookiePrefix();

		$cookieHandler = new CookieHandler( $this->skintemplate->getSkin()->getRequest() );
		$cookie = $cookieHandler->getCookie( $cookiePrefix . $htmlId );

		if ( $cookie === 'false' ) {
			return false;
		} elseif ( $cookie === 'true' ) {
			return true;
		} else {
			$states = $this->skintemplate->getSkin()->getConfig()->get(
				'BlueSpiceCalummaPanelCollapseState'
			);

			if ( array_key_exists( $htmlId, $states ) &&
				( $states[$htmlId] === true || $states[$htmlId] === 1 ) ) {
					return true;
			}

			return false;
		}
	}
}
