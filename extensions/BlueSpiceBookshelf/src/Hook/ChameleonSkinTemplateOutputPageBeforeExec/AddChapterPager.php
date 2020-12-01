<?php

namespace BlueSpice\Bookshelf\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Bookshelf\ChapterPager;
use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\Services;
use BlueSpice\SkinData;
use PageHierarchyProvider;

class AddChapterPager extends ChameleonSkinTemplateOutputPageBeforeExec {
	protected $tree;
	protected $bookTitle;
	protected $title;
	protected $previousTitle = null;
	protected $nextTitle = null;

	protected function skipProcessing() {
		try {
			$this->phProvider = PageHierarchyProvider::getInstanceForArticle(
				$this->template->getSkin()->getTitle()->getPrefixedText()
			);
		} catch ( \Exception $ex ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$config = Services::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		$pagerBeforeContent = $config->get( 'BookShelfShowChapterNavigationPagerBeforeContent' );
		$pagerAfterContent = $config->get( 'BookShelfShowChapterNavigationPagerAfterContent' );

		$chapterPager = new ChapterPager( $this->template );

		if ( ( $pagerBeforeContent === true ) || ( $pagerBeforeContent === 1 ) ) {
			$this->mergeSkinDataArray(
				SkinData::BEFORE_CONTENT,
				[
					'bookshelfui-chapterpager' => $chapterPager->getDefaultPagerHtml()
				]
			);
		}

		if ( ( $pagerAfterContent === true ) || ( $pagerAfterContent === 1 ) ) {
			$this->mergeSkinDataArray(
				SkinData::AFTER_CONTENT,
				[
					'bookshelfui-chapterpager' => $chapterPager->getDefaultPagerHtml()
				]
			);
		}

		return true;
	}
}
