<?php

namespace MediaWiki\Extensions\NewUserActions\Actions;

use User;
use WikiPage;

abstract class CreateWikiPage extends Action {

	///
	/// Protected properties
	///

	/**
	 * The editor of the page to create
	 *
	 * @var User
	 */
	protected $editor;

	/**
	 * The title of the page
	 *
	 * @var Title
	 */
	protected $title;

	/**
	 * The content of the page
	 *
	 * @var string
	 */
	protected $content;

	/**
	 * The edit summary of the page
	 *
	 * @var string
	 */
	protected $editSummary;

	/**
	 * The flags
	 *
	 * @var int
	 */
	protected $flags = EDIT_NEW;

	///
	/// Run action
	///

	public function run() {
		if ( $this->shouldCreatePage() ) {
			$this->createPage();
		}
	}

	/**
	 * Creates a page.
	 */
	public function createPage() {
		$page = WikiPage::factory( $this->title );
		$flags = $page->checkFlags( $this->flags );

		$status = $page->doEdit(
			$this->content,
			$this->editSummary,
			$flags,
			false,
			$this->editor
		);

		if ( !$status->isGood() ) {
			throw new RuntimeException( "Can't create page." );
		}
	}

	///
	/// Helper methods
	///

	/**
	 * Determines if the page should be created.
	 *
	 * @return bool
	 */
	protected function shouldCreatePage() {
		return !$this->title->exists() && $this->editorCanEdit();
	}

	/**
	 * Determines if the editor is able and allowed to create a page.
	 *
	 * @return bool
	 */
	protected function editorCanEdit() {
		return $this->editor && !$this->editor->isBlocked();
	}

}
