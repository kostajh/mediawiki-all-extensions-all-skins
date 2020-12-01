<?php

namespace BlueSpice\Calumma;

use BlueSpice\Calumma\DataProvider\FeaturedActionsData;
use BlueSpice\Calumma\DataProvider\MobileMoreMenuData;
use BlueSpice\Calumma\Panel\GlobalActions;
use BlueSpice\Calumma\Panel\PageTools;
use BlueSpice\Calumma\Panel\QualityManagement;
use BlueSpice\Calumma\Panel\SiteNavigation;
use BlueSpice\SkinData;
use QuickTemplate;

class SkinDataFieldDefinition {

	public const MOBILE_MORE_MENU = 'bs_mobile_more_menu';
	public const LOGO = 'bs_wiki_logo';
	public const CONTENT_NAVIGATION_GROUP = 'bs_content_navigation_group';
	public const CONTENT_NAVIGATION_DATA = 'bs_content_navigation_data';

	/**
	 *
	 * @var QuickTemplate
	 */
	protected $skintemplate = null;

	/**
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * @param QuickTemplate $skintemplate
	 * @param array &$data
	 */
	public function __construct( QuickTemplate $skintemplate, &$data ) {
		$this->skintemplate = $skintemplate;
		$this->data =& $data;
	}

	/**
	 *
	 */
	public function init() {
		$this->data[SkinData::SITE_NAV] = [];
		$this->data[SkinData::GLOBAL_ACTIONS] = [];
		$this->data[SkinData::ADMIN_LINKS] = [];

		/* Menu top - FeaturedAction buttons */
		$this->data[SkinData::FEATURED_ACTIONS] = [];

		$this->data[SkinData::PAGE_INFOS_PANEL] = [];
		$this->data[SkinData::PAGE_DOCUMENTS_PANEL] = [];
		$this->data[SkinData::PAGE_TOOLS_PANEL] = [];

		/* Used in SiteToolsData */
		$this->data[SkinData::PAGE_INFOS] = [];
		$this->data[SkinData::PAGE_DOCUMENTS] = [];
		$this->data[SkinData::PAGE_SETTINGS] = [];
		$this->data[SkinData::PAGE_TOOLS] = [];

		/* Mobile more menu */
		$this->data[static::MOBILE_MORE_MENU] = [];

		/* Page tools sidebar */
		$this->data[SkinData::EDIT_MENU] = [];
		$this->data[SkinData::VIEW_MENU] = [];
		$this->data[SkinData::EXPORT_MENU] = [];
		$this->data[SkinData::EDIT_MENU_BLACKLIST] = [ 'watch', 'unwatch' ];
		$this->data[SkinData::VIEW_MENU_BLACKLIST] = [];
		$this->data[SkinData::TOOLBOX_BLACKLIST] = [ 'upload', 'specialpages', 'print' ];

		$this->initLogo();
		$this->initSiteNav();
		$this->initSiteTools();

		/* Content navigation group map */
		$this->data[static::CONTENT_NAVIGATION_DATA] = [];
		$this->data[static::CONTENT_NAVIGATION_GROUP] = [];
		$this->data[static::CONTENT_NAVIGATION_GROUP] += [
			've-edit' => [
				'bs-group' => 'featuredActionsEdit',
				'position' => 01,
				'title' => wfMessage( 'bs-action-edit-ve-edit-title' )->plain()
			],
			'edit' => [
				'bs-group' => 'featuredActionsEdit',
				'position' => 02,
				'title' => wfMessage( 'bs-action-edit-edit-title' )->plain()
			],
			'view' => [
				'bs-group' => 'featuredActionsEdit',
				'position' => 99,
				'title' => wfMessage( 'bs-action-edit-view-title' )->plain(),
				'text' => wfMessage( 'bs-action-edit-view-text' )->plain()
			],
			'watch' => [
					'bs-group' => 'pageactions',
					'position' => 40
			],

			'unwatch' => [
					'bs-group' => 'pageactions',
					'position' => 40
			],

			'delete' => [
					'bs-group' => 'pageactions',
					'position' => 40
			],
			'move' => [
					'bs-group' => 'pageactions',
					'position' => 30
			],
			'protect' => [
					'bs-group' => 'pageactions',
					'position' => 30
			],
			'purge' => [ 'bs-group' => 'pageactions' ]
		];

		/* Search */
		$this->initSearch();
	}

	/**
	 *
	 */
	public function populateDefaultData() {
		/* group content_navigation (ca-actions) and nav_url (toolbox)
		 * and split to SkinDataFiels
		 */
		$this->groupContentNavigation( $this->getSkin(), $this->skintemplate, $this->data );

		/* append GlobalActions with link to Special:AllPages with preset for namespace 10 (templates) */
		$this->makeGlobalActionsLinkAllTemplates( $this->skintemplate );

		/* populate navigation elements*/
		FeaturedActionsData::populate( $this->getSkin(), $this->skintemplate, $this->data );
		MobileMoreMenuData::populate( $this->getSkin(), $this->skintemplate, $this->data );
	}

	/**
	 *
	 * @return \Skin
	 */
	protected function getSkin() {
		return $this->skintemplate->getSkin();
	}

	/**
	 *
	 * @param string $param
	 * @return \Message
	 */
	protected function getMsg( $param ) {
		return wfMessage( $param );
	}

	private function initSearch() {
		$this->data['bs_search_input'] = $this->data['bs_search_mobile_input'] = [
			'id' => 'searchInput',
			'name' => 'search',
			'type' => 'text',
			'placeholder' => wfMessage( 'searchbutton' )
		];

		$this->data['bs_search_action'] = \SpecialPage::getTitleFor( 'Search' )->getLocalURL();

		$this->data['bs_search_method'] = 'GET';

		$this->data['bs_search_hidden_fields'] = [ [
			'fieldName' => 'fulltext',
			'fieldValue' => '1'
		] ];

		$this->data['bs_search_id'] = 'bs_search_box';
	}

	/**
	 *
	 */
	public function initLogo() {
		global $wgLogo;

		$title = \Title::newMainPage();
		$this->data[static::LOGO] = [
			'desktop' => [
				'position' => 10,
				'src' => $wgLogo,
				'href' => $title->getLocalURL(),
				'text' => $title->getText(),
				'title' => $title->getText(),
				'class' => ''
			]
		];
	}

	/**
	 *
	 * @param \Skin $skin
	 * @param QuickTemplate $skintemplate
	 * @param array &$data
	 * @return bool
	 */
	protected function groupContentNavigation( $skin, QuickTemplate $skintemplate, &$data ) {
		$group = $data[static::CONTENT_NAVIGATION_GROUP];
		$linklist = [];
		$items = [];

		foreach ( $data['content_navigation'] as $nav => $items ) {
			if ( ( $nav === 'namespaces' ) || ( $nav === 'views' ) ) {
				foreach ( $items as $key => $value ) {
					$value['position'] = 0;
					$value['content_nav_group'] = $nav;
					$value['content_nav_key'] = $key;
					$value['bs-group'] = 'views';

					if ( array_key_exists( $key, $group ) ) {
						$group_params = $group[$key];

						foreach ( $group_params as $param => $param_value ) {
							$value[$param] = $param_value;
						}
					}
					$linklist[$key] = $value;
				}
			} else {
				foreach ( $items as $key => $value ) {
					$value['position'] = 0;
					$value['content_nav_group'] = $nav;
					$value['content_nav_key'] = $key;

					if ( !isset( $value['bs-group'] ) ) {
						$value['bs-group'] = 'pageactions';
					}

					if ( array_key_exists( $key, $group ) ) {
						$group_params = $group[$key];

						foreach ( $group_params as $param => $param_value ) {
							$value[$param] = $param_value;
						}
					}
				$linklist[$key] = $value;
				}
			}
		}
		/*toolbox*/
		foreach ( $data['nav_urls'] as $key => $value ) {

			if ( !is_array( $value ) ) {
				continue;
			}
			if ( !isset( $value['text'] ) ) {
				continue;
			}

			$value['position'] = 0;
			$value['content_nav_group'] = 'toolbox';
			$value['content_nav_key'] = $key;

			if ( !isset( $value['bs-group'] ) ) {
				$value['bs-group'] = 'toolbox';
			}

			if ( array_key_exists( $key, $group ) ) {
				$group_params = $group[$key];

				foreach ( $group_params as $param => $param_value ) {
					$value[$param] = $param_value;
				}
			}
			$linklist[$key] = $value;
		}

		$data[static::CONTENT_NAVIGATION_DATA] = $linklist;
		return true;
	}

	private function initSiteNav() {
		$this->data[SkinData::SITE_NAV] += [
			'navigation' => [
				'position' => 10,
				'callback' => function ( $skintemplate ) {
					return new SiteNavigation( $skintemplate );
				}
			],
			'globalactions' => [
				'position' => 100,
				'callback' => function ( $skintemplate ) {
					return new GlobalActions( $skintemplate );
				}
			]
		];
	}

	private function initSiteTools() {
		$this->data[SkinData::SITE_TOOLS] = [
			'quality-management' => [
				'position' => 30,
				'callback' => function ( $skintemplate ) {
					return new QualityManagement( $skintemplate );
				}
			]
		];

		$this->data[SkinData::SITE_TOOLS] += [
			'page-tools' => [
				'position' => 20,
				'callback' => function ( $skintemplate ) {
					return new PageTools( $skintemplate );
				}
			]
		];
	}

	/**
	 *
	 * @param \BaseTemplate $template
	 * @return array
	 */
	public static function countNotifications( $template ) {
		$ptools = $template->getPersonalTools();

		$notifications = [
			'notifications-badge-counter' => 0,
			'notifications-badge-active' => '',
			'notifications-badge-text' => ''
		];
		$counter = 0;

		if ( isset( $ptools['notifications-alert'] ) ) {
			$num = $ptools['notifications-alert']['links'][0]['data']['counter-num'];
			$notifications += [
				'notifications-alert' => [
					'counter-num' => (int)$num,
					'active' => ( $num > 0 ) ? 'active' : ''
				]
			];
			$counter += $num;
		}

		if ( isset( $ptools['notifications-notice'] ) ) {
			$num = $ptools['notifications-notice']['links'][0]['data']['counter-num'];
			$notifications += [
				'notifications-notice' => [
					'counter-num' => (int)$num,
					'active' => ( $num > 0 ) ? 'active' : ''
					]
				];
			$counter += $num;
		}

		if ( $counter > 0 ) {
			$notifications['notifications-badge-active'] = 'active';
			$notifications['notifications-badge-counter'] = $counter;
			$notifications['notifications-badge-text'] =
				( $counter < 100 )
				? (string)$counter
				: wfMessage( 'notifications-badge-counter-gt-100' )->plain();
		}

		return $notifications;
	}

	private static function makeGlobalActionsLinkAllTemplates( $template ) {
		$specialPage = \Title::makeTitleSafe( NS_SPECIAL, 'AllPages' );
		$specialPageLink = $specialPage->getFullURL( 'namespace=10' );

		$template->data[SkinData::GLOBAL_ACTIONS] += [
			'bs-all-templates' => [
					'href' => $specialPageLink,
					'text' => wfMessage( 'bs-all-templates-link-text' )->plain(),
					'title' => wfMessage( 'bs-all-templates-link-title' )->plain(),
					'iconClass' => 'icon-file-text'
				]
		];
	}

}
