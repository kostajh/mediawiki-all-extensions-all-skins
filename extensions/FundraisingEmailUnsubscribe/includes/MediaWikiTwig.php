<?php
/**
 * Nice little wrapper around Twig for mediawiki. Exposes some MW functions
 * to a twig template as well.
 *
 * -- License --
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

/**
 * Wrapper around the Twig templating engine that allows MW code to easily integrate HTML templates.
 * Those templates can also easily call MW templates and code in turn. This is not the safest thing
 * in the world. Escaping is required and needs to be looked at very carefully!
 *
 * NOTE: Twig autoescaping is DISABLED! It plays havock with MW template autoexpansion.
 */
class MediaWikiTwig {
	/** @var Twig_Environment */
	protected $mTwig;
	/** @var array */
	protected $mCallbacks;

	/**
	 * @param string $templatePath Absolute path to root of Twig templates directory. All
	 * templates must include the relative path from this root.
	 * @param IContextSource $context The page context. IE from a MW page $this->getContext()
	 */
	public function __construct( $templatePath, IContextSource $context ) {
		global $wgTwigCachePath;

		$loader = new MediaWikiTwigLoader( $templatePath, $context );
		$this->mTwig = new Twig_Environment( $loader, [
			'cache' => $wgTwigCachePath . '/' . md5( $templatePath ),
			'auto_reload' => true,
			'autoescape' => false,
		] );
		$this->mTwig->addExtension( new MediaWikiTwigCallbacks( $context ) );
	}

	/**
	 * Renders a Twig template and returns the processed HTML data.
	 *
	 * @param string $template Relative path to the Twig template
	 * @param array $params Any key->value parameters that the template requires
	 *
	 * @return string Rendered HTML data
	 */
	public function render( $template, $params = [] ) {
		return $this->mTwig->render( $template, $params );
	}
}

/**
 * All exposed MW functions to Twig Templates
 */
class MediaWikiTwigCallbacks extends Twig_Extension {
	protected $mContext;

	public function __construct( IContextSource $context ) {
		$this->mContext = $context;
	}

	public function getName() {
		return 'MediaWiki Twig Handler';
	}

	public function getFunctions() {
		return [
			'wfMessage' => new Twig_Function_Method( $this, 'twig_wfMessage' ),
			'wfText' => new Twig_Function_Method( $this, 'twig_wfText' ),
			'wfWikiText' => new Twig_Function_Method( $this, 'twig_wfWikiText' ),
		];
	}

	/**
	 * Fully parses a MW i18n message into HTML
	 *
	 * @param string $message Message name
	 * @param array $params Any parameters for the message; straight array, no K/V
	 *
	 * @return string Parsed HTML string
	 */
	public function twig_wfMessage( $message, $params = [] ) {
		return wfMessage( $message, $params )->parse();
	}

	/**
	 * Retrieves non-wiki-parsed MW i18n message
	 *
	 * @param string $message Message name
	 * @param array $params Any parameters for the message; straight array, no K/V
	 *
	 * @return string HTML string
	 */
	public function twig_wfText( $message, $params = [] ) {
		return wfMessage( $message, $params )->text();
	}

	/**
	 * Fully parses arbitrary wiki text; use for things like template inclusion. Message inclusion
	 * should use wfMessage()
	 *
	 * @param string $wikiText Arbitrary wiki text
	 *
	 * @return string Fully parsed HTML string
	 */
	public function twig_wfWikiText( $wikiText ) {
		return $this->mContext->getOutput()->parseInlineAsInterface( $wikiText );
	}
}
