<?php
/*
 * This file is part of Extension:BreadCrumbs2
 *
 * Copyright (C) 2007; Eric Hartwell and Ike Hecht.
 *
 * Distributed under the terms of the CC-BY-3.0 license.
 * Terms and conditions of the license can be found at
 * <https://creativecommons.org/licenses/by/3.0/>
 *
 * @author Eric Hartwell (http://www.ehartwell.com/InfoDabble/BreadCrumbs2)
 * @author Ike Hecht
 * @license CC-BY-3.0
 */

use MediaWiki\MediaWikiServices;

class BreadCrumbs2 {

	/**
	 * Constants
	 */
	const DELIM = '@';

	/**
	 * Full text for the breadcrumbs, if any
	 *
	 * @var string
	 */
	private $crumbPath;

	/**
	 * Sidebar text to look for
	 *
	 * @var string
	 */
	private $sidebarText;

	/**
	 * Path to the logo, to be appended to $wgScriptPath
	 *
	 * @var string
	 */
	private $logoPath;

	/**
	 * Final full breadcrumb path, including page title
	 * Will always contain at least the current page title
	 *
	 * @var string
	 */
	private $breadcrumb;

	/**
	 * Stores the title of the first category in the page. Used for FIRSTCATEGORY variable.
	 *
	 * @var string
	 */
	private $firstCategoryInPage;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * Does this page have breadcrumbs defined for it?
	 *
	 * @return bool
	 */
	public function hasBreadCrumbs() {
		return (bool)$this->crumbPath;
	}

	/**
	 * Get the full output to be inserted into the wiki page
	 *
	 * @return string HTML
	 */
	public function getOutput() {
		return Html::rawElement( 'div', [ 'id' => 'breadcrumbs2' ], $this->breadcrumb );
	}

	/**
	 *
	 * @param array $categories
	 * @param Title $title
	 * @param User $user
	 */
	public function __construct( array $categories, Title $title, User $user ) {
		$this->user = $user;
		if ( !empty( $categories ) ) {
			$this->firstCategoryInPage = $categories[0];
		}

		/** @todo Support main namespace */
		# Treat the namespace as a category too
		if ( $title->getNsText() ) {
			$categories[] = $title->getNsText();
		}

		$crumbs = $this->matchFirstCategory( $categories );

		$this->crumbPath = $crumbs[0];

		# add current title
		$currentTitle = Html::rawElement( 'span', [ 'id' => 'breadcrumbs2-currentitle' ], $title->getText() );
		$this->breadcrumb = trim( $this->crumbPath . ' ' . $currentTitle );

		$categories[] = $title->getText();

		# Mark the corresponding tab of the sidebar as active
		$crumbs = $this->matchFirstCategory( $categories );
		$this->sidebarText = $crumbs[1];
		$this->logoPath = $crumbs[2];

		return true;
	}

	/**
	 * Look up the menu corresponding to the first matching category from the list
	 *
	 * @param array $categories
	 * @return array
	 */
	function matchFirstCategory( array $categories ) {
		# First load and parse the template page
		$content = $this->loadTemplate();
		# Navigation list
		$breadcrumb = '';
		preg_match_all( "`<li>\s*?(.*?)\s*</li>`", $content, $matches, PREG_PATTERN_ORDER );

		# Look for the first matching category or a default string
		foreach ( $matches[1] as $nav ) {
			$pos = strpos( $nav, self::DELIM ); // End of category
			if ( $pos !== false ) {
				$cat = trim( substr( $nav, 0, $pos ) );
				$crumb = trim( substr( $nav, $pos + 1 ) );
				// Is there a match for any of our page's categories?
				if ( $cat == 'default' ) {
					$breadcrumb = $crumb;
				} elseif ( in_array( $cat, $categories ) ) {
					$breadcrumb = $crumb;
					break;
				}
			}
		}

		return self::normalizeParameters( $breadcrumb, self::DELIM, 3 );
	}

	/**
	 * Loads and preprocesses the template page
	 *
	 * @return string
	 */
	function loadTemplate() {
		$msg = wfMessage( 'breadcrumbs' );
		$template = $msg->plain();
		if ( $template ) {
			# Drop leading and trailing blanks and escape delimiter before parsing
			# Substitute a few skin-related variables before parsing
			$template = preg_replace( '/(^\s+|\s+$)/m', '', $template );
			$template = str_replace( self::DELIM . self::DELIM . self::DELIM, "\x07", $template );
			$template = preg_replace_callback(
				'/' . self::DELIM . self::DELIM . '(.*?)' . self::DELIM . self::DELIM . '/',
				[ __CLASS__, 'translate_variable' ],
				$template
			);

			# Use the parser preprocessor to evaluate conditionals in the template
			# Copy the parser to make sure we don't trash the parser state too much
			$parser = clone self::getParser();
			// It is needed for MW older 1.34,
			// in other case $msg->getTitle() throws exception: Call to a member function equals() on boolean
			$msg->inLanguage( RequestContext::getMain()->getLanguage() );
			$template = $parser->parse(
				$template,
				$msg->getTitle(),
				ParserOptions::newFromUser( $this->user )
			);
			try {
				$template = str_replace( '&nbsp;', ' ', $template->getText() );
			} catch ( MWException $e ) {
				MWDebug::warning( $e->getText() );
				$template = '';
			}
			return $template;
		}

		return '';
	}

	/**
	 * Normalize a delimited parameter line: trim leading and trailing blanks,
	 * restore escaped delimiter characters, add null elements until all optional
	 * parameters are accounted for, and drop extra parameters
	 *
	 * @param string $input
	 * @param string $delimiter
	 * @param int $count
	 * @return array
	 */
	private static function normalizeParameters( $input, $delimiter, $count ) {
		# Split the parameters into an array
		$params = explode( $delimiter, $input );
		$output = [];
		for ( $i = 0; $i < $count; $i++ ) {
			$output[] = str_replace(
				"\x07", $delimiter, ( $i < count( $params ) ) ? trim( $params[$i] ) : '' );
		}
		return $output;
	}

	/**
	 * Returns HTML text for the specified pseudo-variable
	 *
	 * @param array $matches
	 * @return string|null
	 */
	function translate_variable( $matches ) {
		$tag = $matches[1];

		switch ( strtoupper( $tag ) ) {
			case 'USERGROUPS': // @@USERGROUPS@@ pseudo-variable: Groups this user belongs to
				self::disableCache();
				$ugm = MediaWikiServices::getInstance()->getUserGroupManager();
				$groups = $ugm->getUserGroups( $this->user );
				return implode( ",", $groups );
			case 'USERID':  // @@USERID@@ pseudo-variable: User Name, blank if anonymous
				self::disableCache();
				# getName() returns IP for anonymous users, so check if logged in first
				return $this->user->isLoggedIn() ? $this->user->getName() : '';
			case 'FIRSTCATEGORY':
				return $this->firstCategoryInPage;
		}
		return null;
	}

	/**
	 * Set a flag in the output object indicating that the content is dynamic and
	 * shouldn't be cached.
	 */
	private static function disableCache() {
		self::getParser()->getOutput()->updateCacheExpiry( 0 );
	}

	/**
	 * @return Parser
	 */
	private static function getParser() {
		return MediaWikiServices::getInstance()->getParser();
	}

	public function getSidebarText() {
		return $this->sidebarText;
	}

	public function getLogoPath() {
		return $this->logoPath;
	}
}
