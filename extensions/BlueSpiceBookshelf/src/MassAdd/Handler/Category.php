<?php
namespace BlueSpice\Bookshelf\MassAdd\Handler;

class Category implements \BlueSpice\Bookshelf\MassAdd\IHandler {
	/**
	 * Name of the category containing pages
	 * to be retrieved
	 *
	 * @var string
	 */
	protected $root;

	/**
	 *
	 * @return array
	 */
	public function getData() {
		$categoryPage = \Category::newFromName( $this->root );
		$titles = $categoryPage->getMembers();

		$categoryRes = [];
		$titles->rewind();
		while ( $titles->key() < $titles->count() ) {
			$title = $titles->current();
			$categoryRes[] = [
				'page_id' => $title->getArticleId(),
				'page_title' => $title->getText(),
				'page_namespace' => $title->getNamespace(),
				'prefixed_text' => $title->getPrefixedText()
			];
			$titles->next();
		}

		return $categoryRes;
	}

	/**
	 * Returns an instance of this handler
	 *
	 * @param string $root Category name to retrieve pages in
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
