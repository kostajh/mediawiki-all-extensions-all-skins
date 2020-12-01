<?php

namespace BlueSpice\Social\Blog\EntityListContext;

use BlueSpice\Social\Blog\Entity\Blog;
use MediaWiki\MediaWikiServices;
use User;

class SpecialBlog extends \BlueSpice\Social\EntityListContext {
	const CONFIG_NAME_OUTPUT_TYPE = 'EntityListSpecialBlogOutputType';
	const CONFIG_NAME_TYPE_ALLOWED = 'EntityListSpecialBlogTypeAllowed';
	const CONFIG_NAME_TYPE_SELECTED = 'EntityListSpecialBlogTypeSelected';

	/**
	 * Owner of the user page
	 * @var User
	 */
	protected $owner = null;

	/**
	 *
	 * @return int
	 */
	public function getLimit() {
		return 5;
	}

	/**
	 *
	 * @return array
	 */
	public function getLockedFilterNames() {
		return array_merge(
			parent::getLockedFilterNames(),
			[ Blog::ATTR_TYPE ]
		);
	}

	/**
	 *
	 * @return string
	 */
	protected function getSortProperty() {
		return Blog::ATTR_TIMESTAMP_CREATED;
	}

	/**
	 *
	 * @return array
	 */
	public function getOutputTypes() {
		return array_merge( parent::getOutputTypes(), [
			'blog' => 'Page'
		] );
	}

	/**
	 *
	 * @return array
	 */
	public function getPreloadedEntities() {
		$preloaded = parent::getPreloadedEntities();
		$blog = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )->newFromObject(
			$this->getRawBlog()
		);
		if ( !$blog instanceof Blog ) {
			return $preloaded;
		}

		$status = $blog->userCan( 'create', $this->getUser() );
		if ( !$status->isOK() ) {
			return $preloaded;
		}

		$preloaded[] = $this->getRawBlog();
		return $preloaded;
	}

	/**
	 *
	 * @return \stdClass
	 */
	protected function getRawBlog() {
		return (object)[
			Blog::ATTR_TYPE => Blog::TYPE,
		];
	}

	/**
	 *
	 * @return bool
	 */
	public function showEntitySpawner() {
		return false;
	}

}
