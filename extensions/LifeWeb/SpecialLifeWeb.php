<?php

/**
 * @license GPL-2.0-or-later
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

class SpecialLifeWeb extends \SpecialPage {

	function __construct() {
		parent::__construct( 'LifeWeb' );
	}

	/**
	 * @param string $par Subpage, e.g. taxonList/Abies
	 */
	function execute( $par ) {
		global $wgLWSettings;

		$output = $this->getOutput();
		$this->setHeaders();

		# Get request data from, e.g.
		// $request = $this->getRequest();
		// $param = $request->getText( 'param' );

		if ( $par == 'js' ) {
			$output = $this->getOutput();
			$output->addModules( [ 'wikibase.RepoApi', 'ext.LifeWeb.test' ] );
			$output->addModules( 'wikibase.client.init' );
			$output->addModules( 'mediawiki.special' );
			$output->addHTML( '<p><span id="test">Create new (wbeditentity)</span></p>' );
			return;
		}
		if ( $par == 'importLW' ) {
			$output = $this->getOutput();
			$output->addModules( 'ext.LifeWeb.importLW' );
			$output->addHTML( '<div id="lwImport"></div>' );
			return;
		}
		if ( $par == 'editor' ) {
			$output->addModules( 'ext.LifeWeb.editor' );
			$output->addHTML( '<div id="editor">Editor goes here.</div>' );
			return;
		}
		if ( $par == 'filter' ) {
			$output->addModules( 'ext.LifeWeb.filter' );
			$output->addHTML( '<div id="filter">Filter goes here.</div>' );
			$output->addInlineStyle( '#footer, h1#firstHeading { display: none; }' );
			$output->addInlineStyle(
				'div#content { background-color: transparent; border-color: transparent; }'
			);
			return;
		}
		if ( $par == 'clouds' ) {
			$output->addModules( 'ext.LifeWeb.filter' );
			$output->addHTML( '<div id="filter" topicID="' . $wgLWSettings['topicIDs']['clouds'] .
				'">Filter goes here.</div>' );
			return;
		}

		$this->outputWikiText(
			"=== Identification Key ===\n" .
			"This identification key consists of two parts: The key itself and the editor. " .
			"If you start from scratch, you may want to start with the editor and enter some data:"
		);
		$output->addHTML(
			'<strong>' .
			$this->getLinkRenderer()->makeKnownLink(
				Title::newFromText( 'Special:LifeWeb/editor' ),
				'Editor',
				[],
				[ 'debug' => 'true' ]
			) .
			'</strong> (debug=true is mandatory!)'
		);
		$this->outputWikiText(
			"The identification key for all available topics can be accessed here:"
		);
		$output->addHTML(
			'<strong>' .
			$this->getLinkRenderer()->makeKnownLink(
				Title::newFromText( 'Special:LifeWeb/filter' ),
				'Filter',
				[],
				[ 'debug' => 'true' ]
			) .
			'</strong>'
		);
		$this->outputWikiText(
			"=== Development tools ===\n"
		);
		global $wgScriptPath;
		$output->addHTML(
			'<ul><li><strong>' .
			$this->getLinkRenderer()->makeKnownLink(
				Title::newFromText( 'Special:LifeWeb/importLW' ),
				'Importer',
				[],
				[ 'debug' => 'true' ]
			) .
			'</strong></li><li>API example: ' .
			'<a href="' . $wgScriptPath . '/api.php?action=query&list=LifeWeb&format=json' .
			'&what=taxonData">Taxon data</a></li></ul>'
		);
	}

	private function outputWikiText( $wikitext ) {
		$output = $this->getOutput();
		if ( method_exists( $output, 'addWikiTextAsInterface' ) ) {
			// MW 1.32+
			$output->addWikiTextAsInterface( $wikitext );
		} else {
			$output->addWikiText( $wikitext );
		}
	}

	protected function getGroupName() {
		return 'other';
	}
}
