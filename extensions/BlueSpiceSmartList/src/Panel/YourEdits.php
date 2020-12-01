<?php

namespace BlueSpice\SmartList\Panel;

use BlueSpice\Calumma\IPanel;
use BlueSpice\Calumma\Panel\BasePanel;
use QuickTemplate;
use Skins\Chameleon\IdRegistry;
use Title;
use User;

class YourEdits extends BasePanel implements IPanel {
	/**
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 *
	 * @param QuickTemplate $sktemplate
	 * @param array $params
	 * @return YourEdits
	 */
	public static function factory( QuickTemplate $sktemplate, $params ) {
		return new self( $sktemplate, $params );
	}

	/**
	 *
	 * @param QuickTemplate $skintemplate
	 * @param array $params
	 */
	public function __construct( QuickTemplate $skintemplate, $params ) {
		parent::__construct( $skintemplate );
		$this->params = $params;
	}

	/**
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-smartlist-lastedits' );
	}

	/**
	 * @return string
	 */
	public function getBody() {
		$count = 5;
		if ( isset( $this->params['count'] ) ) {
			$count = (int)$this->params['count'];
		}

		$edits = \SmartList::getYourEditsTitles( $this->getUser(), $count );

		$links = [];
		foreach ( $edits as $edit ) {
			$link = [
				'href' => $edit['title']->getFullURL(),
				'text' => $edit['displayText'],
				'title' => $edit['displayText'],
				'classes' => ' bs-usersidebar-internal '
			];
			$links[] = $link;
		}

		$linkListGroup = new \BlueSpice\Calumma\Components\SimpleLinkListGroup( $links );

		return $linkListGroup->getHtml();
	}

	/**
	 *
	 * @return User
	 */
	protected function getUser() {
		return $this->skintemplate->getSkin()->getUser();
	}

	/**
	 *
	 * @return Title
	 */
	protected function getTitle() {
		return $this->skintemplate->getSkin()->getTitle();
	}

	/**
	 *
	 * @var string
	 */
	protected $htmlId = null;

	/**
	 * The HTML ID for thie component
	 * @return string
	 */
	public function getHtmlId() {
		if ( ( $this->htmlId === null ) && ( isset( $this->params['panelId'] ) ) ) {
			$this->htmlId = IdRegistry::getRegistry()->getId( $this->params['panelId'] );
		} elseif ( $this->htmlId === null ) {
			$this->htmlId = IdRegistry::getRegistry()->getId();
		}
		return $this->htmlId;
	}

	/**
	 *
	 * @return bool
	 */
	public function getPanelCollapseState() {
		$htmlId = $this->htmlId;

		$cookieName = $this->getCookiePrefix() . $htmlId;
		$skin = $this->skintemplate->getSkin();
		$cookie = $skin->getRequest()->getCookie( $cookieName );

		if ( $cookie === 'true' ) {
			return true;
		} else {
			return false;
		}
	}
}
