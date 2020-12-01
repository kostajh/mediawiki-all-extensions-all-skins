<?php
/**
 * Renders the Rating special page.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Patric Wirth
 * @package    BlueSpice Pro
 * @subpackage BlueSpiceRating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\Rating\Special;

use BlueSpice\Special\ManagerBase;
use BsNamespaceHelper;

/**
 * Rating SpecialPage
 * @package BlueSpice_Extensions
 * @subpackage Rating
 */
class Rating extends ManagerBase {

	/**
	 *
	 */
	public function __construct() {
		parent::__construct( 'Rating', 'rating-viewspecialpage', true );
	}

	/**
	 *
	 * @param string $param
	 */
	public function execute( $param ) {
		parent::execute( $param );

		$this->getOutput()->setPageTitle(
			$this->msg( 'bs-rating-special-rating-heading' )->plain()
		);
	}

	/**
	 * @return string ID of the HTML element being added
	 */
	protected function getId() {
		return "bs-ratingarticle-grid";
	}

	/**
	 * @return array
	 */
	protected function getModules() {
		return [
			'ext.bluespice.ratingItemArticle',
			'ext.bluespice.specialRating',
			'ext.bluespice.rating.styles'
		];
	}

	/**
	 *
	 * @return array
	 */
	protected function getJSVars() {
		$enabledNamespaces = $this->getConfig()->get(
			'RatingArticleEnabledNamespaces'
		);

		$namespaces = [];
		foreach ( $enabledNamespaces as $nsIdx ) {
			$namespaces[$nsIdx] = BsNamespaceHelper::getNamespaceName(
				$nsIdx
			);
		}

		return [
			'bsgRatingArticleAcitveNamespaces' => $namespaces
		];
	}
}
