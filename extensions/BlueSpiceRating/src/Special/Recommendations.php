<?php
/**
 * Renders the Recommendations special page.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Patric Wirth
 * @package    BlueSpice Pro
 * @subpackage BlueSpiceRecommendations
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\Rating\Special;

use BlueSpice\Special\ManagerBase;
use BsNamespaceHelper;

/**
 * Recommendations SpecialPage
 * @package BlueSpice_Extensions
 * @subpackage Recommendations
 */
class Recommendations extends ManagerBase {

	/**
	 *
	 */
	public function __construct() {
		parent::__construct( 'Recommendations', 'rating-viewspecialpage', true );
	}

	/**
	 *
	 * @param string $param
	 */
	public function execute( $param ) {
		parent::execute( $param );

		$this->getOutput()->setPageTitle(
			$this->msg( 'bs-rating-special-recommendations-heading' )->plain()
		);
	}

	/**
	 * @return string ID of the HTML element being added
	 */
	protected function getId() {
		return 'bs-ratingarticlelike-grid';
	}

	/**
	 * @return array
	 */
	protected function getModules() {
		return [
			'ext.bluespice.ratingItemArticleLike',
			'ext.bluespice.specialRecommendations',
			'ext.bluespice.ratingItemArticleLike.styles'
		];
	}

	/**
	 *
	 * @return array
	 */
	protected function getJSVars() {
		$enabledNamespaces = $this->getConfig()->get(
			'RatingArticleLikeEnabledNamespaces'
		);
		$namespaces = [];
		foreach ( $enabledNamespaces as $nsIdx ) {
			$namespaces[$nsIdx] = BsNamespaceHelper::getNamespaceName(
				$nsIdx
			);
		}

		return [
			'bsgRatingArticleLikeActiveNamespaces' => $namespaces
		];
	}
}
