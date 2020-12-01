<?php

namespace BlueSpice\UserSidebar\Panel;

use BlueSpice\Calumma\IPanel;
use BlueSpice\Calumma\Panel\PanelContainer;
use BlueSpice\UserSidebar\SidebarParser;
use MediaWiki\MediaWikiServices;
use Message;
use QuickTemplate;

class UserSidebarNav extends PanelContainer {

	protected $sidebarParser;
	protected $userSidebarTitle;
	protected $linkRenderer;

	/**
	 *
	 * @param QuickTemplate $skintemplate
	 */
	public function __construct( $skintemplate ) {
		parent::__construct( $skintemplate );
		$user = $this->skintemplate->getSkin()->getUser();
		$this->userSidebarTitle = \Title::makeTitle( NS_USER, $user->getName() . '/Sidebar' );

		$this->widgetRegistry = \ExtensionRegistry::getInstance()->getAttribute(
			'BlueSpiceUserSidebarWidgets'
		);
		$this->sidebarParser = new SidebarParser( $this->userSidebarTitle, $this->widgetRegistry );

		$this->linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
	}

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		$html = parent::getBody();
		$html .= $this->getEditLink();
		return $html;
	}

	/**
	 *
	 * @return \BlueSpice\UserSidebar\Panel\CollapsibleLinks[]
	 */
	protected function makePanels() {
		if ( $this->userSidebarTitle->exists() ) {
			$this->sidebarParser->parse();
			$sidebarItems = $this->sidebarParser->getItems();
		} else {
			// If user sidebar page is not available show only widgets
			$sidebarItems = $this->getWidgets();
		}

		$panels = [];

		$cnt = 0;
		foreach ( $sidebarItems as $section => $links ) {
			$cnt++;
			$sectionId = "bs-usersidebar-$cnt";
			if ( $this->isWidget( $section ) ) {
				if ( isset( $links['callback'] ) && is_callable( $links['callback'] ) ) {
					$params = [];
					if ( isset( $links['params'] ) ) {
						$params = $links['params'];
					}
					$params['panelId'] = $sectionId;
					$params['panelIdPrefix'] = "bs-usersidebar-";

					$widgetPanel = call_user_func_array( $links['callback'], [ $this->skintemplate, $params ] );
					if ( $widgetPanel instanceof IPanel ) {
						$panels[$sectionId] = $widgetPanel;
					}
				}
				continue;
			}

			$panels[$sectionId] = new CollapsibleLinks( $this->skintemplate, $section, $links, $sectionId );
		}
		return $panels;
	}

	/**
	 *
	 * @return Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-usersidebar-nav-title' );
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return 'bs-nav-section-bs-usersidebar';
	}

	/**
	 *
	 * @param string $name
	 * @return bool
	 */
	protected function isWidget( $name ) {
		if ( isset( $this->widgetRegistry[$name] ) ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @return array
	 */
	protected function getWidgets() {
		$widgets = [];
		foreach ( $this->widgetRegistry as $key => $config ) {
			$widgets[$key] = [
				'callback' => $config['callback']
			];
		}
		return $widgets;
	}

	/**
	 *
	 * @return string
	 */
	protected function getEditLink() {
		$text = \Html::element(
					'span',
					[
						'class' => 'label'
					],
					wfMessage( 'bs-edit-user-sidebar-link-text' )->plain()
				);

		$editLink = $this->linkRenderer->makeLink(
			$this->userSidebarTitle,
			new \HtmlArmor( $text ),
			[
				'id' => 'bs-usersidebar-edit',
				'title' => wfMessage( 'bs-edit-user-sidebar-link-title' )->plain(),
				'class' => 'bs-usersidebar-edit-link bs-calumma-sidebar-edit-link'
			],
			[
				'action' => 'edit',
				'preload' => ''
			]
		);

		// Temporary
		return $editLink;
	}
}
