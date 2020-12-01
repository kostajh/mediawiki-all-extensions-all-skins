<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\Calumma\SkinDataFieldDefinition as SDFD;
use BlueSpice\DynamicFileDispatcher\Params;
use BlueSpice\DynamicFileDispatcher\UserProfileImage;
use MediaWiki\MediaWikiServices;
use QuickTemplate;

class MobileUserContainer extends BasePanel {

	/**
	 *
	 * @var QuickTemplate
	 */
	protected $skintemplate = null;

	/**
	 *
	 * @param \QuickTemplate $skintemplate
	 */
	public function __construct( QuickTemplate $skintemplate ) {
		$this->skintemplate = $skintemplate;
	}

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		$template = $this->skintemplate;

		$separator = '<div class="separator"></div>';

		$html =
			'<div class="bs-mobile-user-button dropdown calumma-mobile-visible calumma-tablet-visible">';

		if ( $template->getSkin()->getUser()->isLoggedIn() ) {
			$imageUrlBuilder = MediaWikiServices::getInstance()->getService(
				'BSDynamicFileDispatcherUrlBuilder'
			);
			$imageUrlBuilder instanceof UrlBuilder;

			$util = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' );
			$displayName = $util->getUserHelper(
				$template->getSkin()->getUser()
			)->getDisplayName();
			$userName = $template->getSkin()->getUser()->getName();

			$userImageParams = [
				Params::MODULE => 'userprofileimage',
				UserProfileImage::USERNAME => $userName,
				UserProfileImage::HEIGHT => 48,
				UserProfileImage::WIDTH => 48
			];

			$imageSrc = $imageUrlBuilder->build( new Params( $userImageParams ) );

			$ptools = $template->getPersonalTools();
			unset( $ptools['notifications-alert'] );
			unset( $ptools['notifications-notice'] );

			$notifications = SDFD::countNotifications( $template );

			// mediawiki-sidebar usermenu button
			$html .= '<a role="button" class="dropdown-toggle" data-toggle="dropdown" ';
			$html .= 'data-notification-badge="' . $notifications['notifications-badge-active'] . '"';
			$html .= '><img src="' . $imageSrc . '" ';
			$html .= 'class="bs-profile-img" ';
			$html .= 'title="' . $displayName;
			$html .= '" alt="' . $userName . '" width="24px" height="24px" />';
			$html .= '<i></i><span>' . $template->getSkin()->getUser() . '</span></a>';

			// usermenu
			$html .= '<div class="dropdown-menu bs-personal-menu-container">';
			$html .= '<ul class="bs-personal-menu">';

			// head with chevron, image and name
			$html .= '<li class="bs-personal-menu-container-user"><i class="bs-navigation-main-back"></i>';
			$html .= '<img src="' . $imageSrc . '" class="bs-profile-img" ';
			$html .= 'title="' . $displayName;
			$html .= '" alt="' . $userName . '" width="32px" height="32px" />';
			$html .= '<span>' . $userName . '</span>';
			$html .= '</li>';

			$html .= $separator;

			// userpage
			$userPageMessage = wfMessage( 'bs-userbutton-userpage-link-text' );
			$html .= '<li><a text="' . $displayName;
			$html .= '" href="' . $template->getSkin()->getUser()->getUserPage()->getFullURL() . '">';
			$html .= '<i class="bs-icon-user"></i>';
			$html .= '<span>' . $userPageMessage->plain() . '</span></a></li>';

			// notifications
			$specialNotifications = \Title::newFromText( 'Notifications', NS_SPECIAL );

			$notificationsMessage = wfMessage( 'bs-userbutton-notifications-link-text' );
			$html .= '<li><a text="' . wfMessage( 'bs-userbutton-notifications-link-text' )->plain();
			$html .= '" href="' . $specialNotifications->getFullURL() . '">';
			$html .= '<i class="bs-notifications ' . $notifications['notifications-badge-active'] . '"></i>';
			$html .= '<span>' . $notificationsMessage->plain() . '</span></a></li>';

			// personal-tools
			array_shift( $ptools );
			$tools = [];
			$tools = $this->sortPersonalTools( $ptools );

			$html .= $separator;

			if ( isset( $tools['links'] ) ) {
				$html .= $this->makePersonalToolsListItem( $tools['links'] );

				$html .= $separator;
			}

			if ( isset( $tools['dashboards'] ) ) {
				$html .= $this->makePersonalToolsListItem( $tools['dashboards'] );

				$html .= $separator;
			}

			if ( isset( $tools['preferences'] ) ) {
				$html .= $this->makePersonalToolsListItem( $tools['preferences'] );
			}

			if ( isset( $tools['logout'] ) ) {
				$html .= $this->makePersonalToolsListItem( $tools['logout'] );
			}

			$html .= '</ul>';
			$html .= '</div>';
		} else {
			$returnToPage = $template->getSkin()->getTitle();
			$returnTarget = 'returnto=' . $returnToPage->getTitleValue()->getDBkey();

			$specialLogin = \Title::newFromText( 'Login', NS_SPECIAL );

			$html .= '<span class="bs-personal-not-loggedin">';
			$html .= '<a class="bs-personal-not-loggedin list-group-item" href="';
			$html .= $specialLogin->getFullURL( $returnTarget );
			$html .= '" title="' . wfMessage( 'Login' )->plain() . '">';
			$html .= '<i class="bs-icon-key"></i>';
			$html .= '<span>' . wfMessage( 'Login' )->plain() . '</span></a>';
			$html .= '</span>';
			$html .= '<div class="spacer"></div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return 'bs-mobile-user-container';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-sitenav-navigation-section-mobileusercontainer' );
	}

	/**
	 *
	 * @param array $ptools
	 * @return array
	 */
	public function sortPersonalTools( $ptools ) {
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
	 * @param array $items
	 * @return string
	 */
	protected function makePersonalToolsListItem( $items ) {
		$ret = '';
		foreach ( $items as $item ) {
			$ret .= \Html::openElement( 'li' );

			$ret .= \Html::openElement(
				'a',
				[
					'title' => $item['text'],
					'href' => $item['href']
				]
			);

			$ret .= \Html::element( 'i' );
			$ret .= $item['text'];

			$ret .= \Html::closeElement( 'a' );

			$ret .= \Html::closeElement( 'li' );
		}

		return $ret;
	}

}
