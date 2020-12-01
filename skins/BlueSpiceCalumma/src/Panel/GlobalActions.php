<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\Calumma\Components\CollapsibleGroup;
use BlueSpice\Calumma\Components\SimpleLinkListGroup;
use BlueSpice\Calumma\CookieHandler;
use BlueSpice\SkinData;
use QuickTemplate;

class GlobalActions extends BasePanel {

	/**
	 *
	 * @var QuickTemplate
	 */
	protected $skintemplate = null;

	/**
	 *
	 * @var string
	 */
	protected $sectionId = '';

	/**
	 *
	 * @param QuickTemplate $skintemplate
	 */
	public function __construct( QuickTemplate $skintemplate ) {
		$this->skintemplate = $skintemplate;
	}

	/**
	 *
	 * @return bool
	 */
	public function isEmpty() {
		return empty( $this->skintemplate->get( SkinData::GLOBAL_ACTIONS ) )
			&& empty( $this->skintemplate->get( SkinData::ADMIN_LINKS ) );
	}

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		$globalActions = $this->sortLinksAlphabetically(
				$this->skintemplate->get( SkinData::GLOBAL_ACTIONS )
			);

		$sections = [
			'bs-sitenav-globalactions-section-globalactions' => $globalActions
		];

		if ( $this->skintemplate->getSkin()->getUser()->isLoggedIn() ) {
			$management = $this->sortLinksAlphabetically(
					$this->skintemplate->get( SkinData::ADMIN_LINKS )
				);

			$sections += [
				'bs-sitenav-globalactions-section-management' => $management
			];
		}

		$html = '';

		foreach ( $sections as $section => $links ) {
			$linklistgroup = new SimpleLinkListGroup( array_values( $links ) );

			$this->sectionId = str_replace( ' ', '-', $section );

			$collapsibleGroup = new CollapsibleGroup( [
				'id' => $this->sectionId,
				'title' => wfMessage( $section ),
				'content' => $linklistgroup->getHtml(),
				'collapse' => $this->getPanelCollapseState()
			] );

			$html .= $collapsibleGroup->getHtml();
		}

		return $html;
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return 'bs-globalactions';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-sitenav-globalactions-title' );
	}

	/**
	 *
	 * @return array
	 */
	public function getContainerClasses() {
		return [ 'calumma-navigation-mobile-hidden' ];
	}

	/**
	 *
	 * @param array $links
	 * @return array
	 */
	protected function sortLinksAlphabetically( $links ) {
		$helper = [];

		foreach ( $links as $linkid => $link ) {
			if ( !isset( $link['id'] ) || empty( $link['id'] ) ) {
				$link['id'] = "bs-ga-link-$linkid";
			}
			if ( $link['text'] instanceof \Message ) {
				$text = $link['text']->text();
				$helper[$text] = $link;
			} else {
				$helper[$link['text']] = $link;
			}
		}

		ksort( $helper );

		return array_values( $helper );
	}

	/**
	 *
	 * @return bool
	 */
	public function getPanelCollapseState() {
		$cookiePrefix = $this->getCookiePrefix();
		$cookieName = $cookiePrefix . 'collapse-' . $this->sectionId;
		$cookieHandler = new CookieHandler( $this->skintemplate->getSkin()->getRequest() );
		$cookie = $cookieHandler->getCookie( $cookieName );

		if ( $cookie === 'false' ) {
			return false;
		} elseif ( $cookie === 'true' ) {
			return true;
		} else {
			$states = $this->skintemplate->getSkin()->getConfig()->get(
				'BlueSpiceCalummaPanelCollapseState'
			);

			if ( array_key_exists( $this->sectionId, $states ) &&
				( $states[$this->sectionId] === true || $states[$this->sectionId] === 1 ) ) {
					return true;
			}

			return false;
		}
	}
}
