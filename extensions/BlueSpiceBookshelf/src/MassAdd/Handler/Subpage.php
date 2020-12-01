<?php
namespace BlueSpice\Bookshelf\MassAdd\Handler;

class Subpage implements \BlueSpice\Bookshelf\MassAdd\IHandler {
	/**
	 * Title of the page we want to
	 * retrieve subpages for
	 *
	 * @var string
	 */
	protected $root;

	/**
	 *
	 * @return array
	 */
	public function getData() {
		if ( substr( $this->root, -1 ) == '/' ) {
			$this->root = substr( $this->root, 0, -1 );
		}

		$title = \Title::newFromText( $this->root );

		if ( !( $title instanceof \Title ) || $title->exists() == false ) {
			return [];
		}

		$subpages = $title->getSubpages();
		$subpageRes = [];
		foreach ( $subpages as $subpage ) {
			$subpageRes[] = [
				'page_id' => $subpage->getArticleId(),
				'page_title' => $subpage->getText(),
				'page_namespace' => $subpage->getNamespace(),
				'prefixed_text' => $subpage->getPrefixedText()
			];
		}
		return $subpageRes;
	}

	/**
	 * Returns an instance of this handler
	 *
	 * @param string $root Page name
	 * @return \self
	 */
	public static function factory( $root ) {
		return new self( $root );
	}

	/**
	 *
	 * @param string $root
	 */
	protected function __construct( $root ) {
		$this->root = $root;
	}

}
