<?php
namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\SkinDataFieldDefinition as SDFD;
use BlueSpice\Calumma\TemplateComponent;
use BlueSpice\DynamicFileDispatcher\Params;
use BlueSpice\DynamicFileDispatcher\UrlBuilder;
use BlueSpice\DynamicFileDispatcher\UserProfileImage;
use MediaWiki\MediaWikiServices;

class UserButton extends TemplateComponent {

	/**
	 *
	 * @return string
	 */
	protected function getTemplatePathName() {
		return 'Calumma.Components.UserButton';
	}

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$args = parent::getTemplateArgs();
		if ( $this->getSkin()->getUser()->isLoggedIn() ) {
			$args['user'] = $this->getUserButton();
		} else {
			$args['anon'] = $this->getLoginButton();
		}
		return $args;
	}

	/**
	 *
	 * @return array
	 */
	protected function getUserButton() {
		$user = $this->getSkin()->getUser();
		$imageUrlBuilder = MediaWikiServices::getInstance()->getService(
			'BSDynamicFileDispatcherUrlBuilder'
		);
		$imageUrlBuilder instanceof UrlBuilder;

		$values = [];

		$util = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' );
		$values['username'] = $user->getName();
		$values['personalname'] = $util->getUserHelper( $user )->getDisplayName();
		$baseSize = 48;
		$values['width'] = $values['height'] = "{$baseSize}px";

		$realsize = (int)$baseSize * 1.4;
		$userImageParams = [
			Params::MODULE => 'userprofileimage',
			UserProfileImage::USERNAME => $user->getName(),
			UserProfileImage::HEIGHT => $realsize,
			UserProfileImage::WIDTH => $realsize,
		];

		// userimage
		$values['src'] = $imageUrlBuilder->build( new Params( $userImageParams ) );

		// personal tools
		$values['personaltools'] = $this->getPersonalTools();

		// count notifications
		$notifications = SDFD::countNotifications( $this->getSkinTemplate() );
		$values['notifications'] = $notifications;

		// specialpage link
		$specialNotifications = \Title::newFromText( 'Notifications', NS_SPECIAL );
		$values['notifications']['notifications-link'] = [
			'href' => $specialNotifications->getFullURL(),
			'text' => wfMessage( 'bs-userbutton-notifications-link-text' )->text(),
			'class' => 'bs-userbutton-notifications-link',
			'data-notifications-badge-active' => $notifications['notifications-badge-active'],
			'data-notifications-badge-text' => $notifications['notifications-badge-text']
		];

		if ( $values['notifications']['notifications-badge-text'] === '' ) {
			unset( $values['notifications']['notifications-badge-text'] );
		}

		return $values;
	}

	/**
	 *
	 * @return array
	 */
	protected function getPersonalTools() {
		$ptools = $this->getSkinTemplate()->getPersonalTools();

		unset( $ptools['notifications-alert'] );
		unset( $ptools['notifications-notice'] );

		$tools = [];

		foreach ( $ptools as $ptool ) {
			if ( $ptool['id'] === 'pt-userpage' ) {
				$tools['userpage'][] = [
					'id' => $ptool['id'],
					'text' => wfMessage( 'bs-userbutton-userpage-link-text' )->plain(),
					'href' => $ptool['links'][0]['href']
				];
			} elseif ( $ptool['id'] === 'pt-watchlist' ) {
				$tools['watchlist'][] = [
					'id' => $ptool['id'],
					'text' => $ptool['links'][0]['text'],
					'href' => $ptool['links'][0]['href']
				];
			} elseif ( $ptool['id'] === 'pt-logout' ) {
				$tools['logout'][] = [
					'id' => $ptool['id'],
					'text' => $ptool['links'][0]['text'],
					'href' => $ptool['links'][0]['href']
				];
			} elseif ( $ptool['id'] === 'pt-preferences' ) {
				$tools['preferences'][] = [
					'id' => $ptool['id'],
					'text' => $ptool['links'][0]['text'],
					'href' => $ptool['links'][0]['href']
				];
			} elseif ( $ptool['id'] === 'pt-mytalk' ) {
				$tools['mytalk'][] = [
					'id' => $ptool['id'],
					'text' => $ptool['links'][0]['text'],
					'href' => $ptool['links'][0]['href']
				];
			} elseif ( $ptool['id'] === 'pt-my_reminder' ) {
				$tools['links'][] = [
					'id' => $ptool['id'],
					'text' => $ptool['links'][0]['text'],
					'href' => $ptool['links'][0]['href']
				];
			} elseif ( $ptool['id'] === 'pt-userdashboard' ) {
				$tools['dashboards'][] = [
					'id' => $ptool['id'],
					'text' => $ptool['links'][0]['text'],
					'href' => $ptool['links'][0]['href']
				];
			} elseif ( $ptool['id'] === 'pt-admindashboard' ) {
				$tools['dashboards'][] = [
					'id' => $ptool['id'],
					'text' => $ptool['links'][0]['text'],
					'href' => $ptool['links'][0]['href']
				];
			} else {
				$tools['links'][] = [
					'id' => $ptool['id'],
					'text' => $ptool['links'][0]['text'],
					'href' => $ptool['links'][0]['href']
				];
			}
		}

		return $tools;
	}

	/**
	 *
	 * @return array
	 */
	protected function getLoginButton() {
			$returnToPage = $this->getSkin()->getTitle();

			if ( $returnToPage->equals( \SpecialPage::getTitleFor( 'Badtitle' ) ) ) {
				$request = $this->getSkin()->getRequest();
				$requestTitle = \Title::newFromText( $request->getVal( 'title', '' ) );
				if ( $requestTitle instanceof \Title ) {
					$returnToPage = $requestTitle;
				} else {
					$returnToPage = \Title::newMainPage();
				}
			}

			$returnTarget = 'returnto=' . $returnToPage->getPrefixedDBkey();

			$oLogin = \Title::newFromText( 'Login', NS_SPECIAL );

			$login = [];
			$login['url'] = $oLogin->getFullURL( $returnTarget );
			$login['text'] = wfMessage( 'Login' )->plain();

			return $login;
	}
}
