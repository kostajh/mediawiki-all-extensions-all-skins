<?php

namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\TemplateComponent;

class PageContent extends TemplateComponent {

	/**
	 *
	 * @return string
	 */
	protected function getTemplatePathName() {
		return 'Calumma.Components.PageContent';
	}

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$args = parent::getTemplateArgs();
		$args += [
			'contentsub' => $this->getContentSub(),
			'sitesub' => $this->getSiteSub(),
			'contenttext' => $this->getContentText(),
			'printfooter' => $this->getPrintFooter()
		];
		return $args;
	}

	/**
	 *
	 * @return string
	 */
	protected function getSiteSub() {
		if ( $this->getSkin()->getTitle()->isContentPage() ) {
			$this->getSkinTemplate()->getMsg( 'tagline' );
		} else {
			return '';
		}
	}

	/**
	 *
	 * @return string
	 */
	protected function getContentSub() {
		return $this->getSkinTemplate()->get( 'subtitle' );
	}

	/**
	 *
	 * @return string
	 */
	protected function getContentText() {
		return $this->getSkinTemplate()->get( 'bodytext' );
	}

	/**
	 *
	 * @return string
	 */
	protected function getPrintFooter() {
		return $this->getSkinTemplate()->get( 'printfooter' );
	}

}
