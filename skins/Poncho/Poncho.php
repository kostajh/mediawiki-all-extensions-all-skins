<?php

class SkinPoncho extends SkinTemplate {

	public $skinname = 'poncho';

	public $template = 'PonchoTemplate';

	public function initPage( OutputPage $out ) {
		parent::initPage( $out );
		$out->enableOOUI();
		$out->addModuleStyles( 'skins.poncho.style' );
		$out->addModules( 'skins.poncho.script' );
		$out->addMeta( 'viewport', 'width=device-width,user-scalable=no' );
	}
}

class PonchoTemplate extends BaseTemplate {

	/**
	 * Print the search bar
	 */
	function searchInput() {
		global $wgSitename;
		echo new MediaWiki\Widget\SearchInputWidget([
			'name' => 'search',
			'placeholder' => wfMessage( 'search' )
		]);
	}

	/**
	 * Print the talk page
	 */
	function talkPage() {
		
	}

	/**
	 * Print the attributes of the logo
	 */
	function logoAttributes() {
		global $wgLogo;
		list( $width, $height ) = getimagesize( $wgLogo );
		$attributes = Linker::tooltipAndAccesskeyAttribs( 'p-logo' );
		$attributes['href'] = htmlspecialchars( $this->data['nav_urls']['mainpage']['href'] );
		$attributes['style'] = 'background-image: url("' . $wgLogo . '");';
		$attributes = Xml::expandAttributes( $attributes );
		echo $attributes;
	}

	/**
	 * Print the site name
	 */
	function siteName() {
		global $wgSitename, $wgPonchoSitename;
		if ( $wgPonchoSitename === false ) {
			echo $wgSitename;
		} else {
			echo $wgPonchoSitename;
		}
	}

	/**
	 * Print the path to the skin
	 */
	function skinPath() {
		global $wgResourceBasePath;
		echo $wgResourceBasePath . '/skins/Poncho/';
	}

	/**
	 * Return the main menu of the header
	 */
	function getMainMenu() {
		return array_shift( $this->data['sidebar'] );
	}

	/**
	 * Return the user menu of the header
	 */
	function getUserMenu() {
		$userMenu = $this->getPersonalTools();
		unset( $userMenu['uls'] );
		unset( $userMenu['notifications-alert'] );
		unset( $userMenu['notifications-notice'] );
		return $userMenu;
	}

	/**
	 * Get the main actions
	 */
	function getMainActions() {
		$actions = [];
		$views = $this->data['content_navigation']['views'];
		$namespaces = $this->data['content_navigation']['namespaces'];
		$action = $this->getSkin()->getRequest()->getVal( 'action', 'view' ); // Current action
		$title = $this->getSkin()->getTitle();
		if ( $title->isTalkPage() ) {
			$actions[] = array_shift( $namespaces );
		}
		if ( $action !== 'view' and array_key_exists( 'view', $views ) ) {
			$actions[] = $views['view'];
		}
		if ( $action !== 've-edit' and array_key_exists( 've-edit', $views ) ) {
			$actions[] = $views['ve-edit'];
		}
		if ( $action !== 'formedit' and array_key_exists( 'formedit', $views ) ) {
			$actions[] = $views['formedit'];
		}
		if ( $action !== 'edit' and array_key_exists( 'edit', $views ) ) {
			$actions[] = $views['edit'];
		}
		if ( $action !== 'history' and array_key_exists( 'history', $views ) ) {
			$actions[] = $views['history'];
		}
		if ( count( $namespaces ) > 1 ) {
			$actions[] = array_pop( $namespaces );
		}
		return $actions;
	}

	/**
	 * Get the other actions
	 */
	function getMoreActions() {
		$actions = array_merge(
			$this->data['content_navigation']['views'],
			$this->data['content_navigation']['actions'],
			$this->data['content_navigation']['variants']
		);
		unset( $actions['view'] );
		unset( $actions['ve-edit'] );
		unset( $actions['formedit'] );
		unset( $actions['edit'] );
		unset( $actions['history'] );
		return $actions;
	}

	/**
	 * Get the latest notifications
	 * process them and return them in a format fit for BaseTemplate::makeListItem
	 *
	 * @todo This method uses an internal API call which should be replaced by the proper Echo classes
	 */
	function getNotifications() {
		$notifications = [];
		$user = $this->getSkin()->getUser();
		if ( $user->isLoggedIn() ) {
			global $wgRequest;
			$params = new DerivativeRequest(
				$wgRequest,
				[
					'action' => 'query',
					'meta' => 'notifications',
					'notformat' => 'model',
					'notlimit' => 10,
					'format' => 'json',
				]
			);
			$api = new ApiMain( $params );
			$api->execute();
			$data = $api->getResult()->getResultData();
			$list = $data['query']['notifications']['list'];
			$list = array_reverse( $list );
			foreach ( $list as $key => $item ) {
				if ( ! is_int( $key ) ) {
					continue;
				}
				$content = $item['*'];
				$id = "notification-$key";
				$text = strip_tags( $content['header'] );
				$href = $content['links']['primary']['url'] ?? null;
				$active = array_key_exists( 'read', $item ) ? false : true;
				$link = [
					'id' => $id,
					'text' => $text,
					'href' => $href,
				];
				$notification = [
					'id' => $id,
					'links' => [ $link ],
					'class' => $href ? 'link' : 'text',
					'active' => $active,
				];
				$notifications[ $id ] = $notification;
			}
		}
		if ( ! $notifications ) {
			$notifications[] = [
				'id' => 'notification-0',
				'text' => wfMessage( 'echo-none' ),
				'class' => 'text',
			];
		}
		return $notifications;
	}

	/**
	 * Get the notifications alert of the Echo extension
	 */
	function getNotificationsAlert() {
		$userLinks = $this->getPersonalTools();
		return $userLinks['notifications-alert'];
	}

	/**
	 * Get the notifications alert of the Echo extension
	 */
	function getNotificationsNotice() {
		$userLinks = $this->getPersonalTools();
		return $userLinks['notifications-notice'];
	}

	/**
	 * Output the page
	 */
	function execute() {
		include 'Poncho.phtml';
	}
}