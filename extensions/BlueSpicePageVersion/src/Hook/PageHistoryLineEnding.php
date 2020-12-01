<?php

namespace BS\PageVersion\Hook;

use BS\PageVersion\VersionFormatter;

class PageHistoryLineEnding {

	/**
	 *
	 * @var VersionFormatter
	 */
	protected $versionFormatter = null;

	/**
	 *
	 * @param \HistoryPager $history
	 * @param \stdClass &$row
	 * @param string &$s
	 * @param array &$classes
	 * @return bool
	 */
	public function handle( $history, &$row, &$s, &$classes ) {
		if ( $this->versionFormatter === null ) {
			$this->versionFormatter = new VersionFormatter(
				\Title::newFromID( (int)$row->rev_page )
			);
		}

		$versionTag = \Html::element(
			'span',
			[
				'class' => 'bs-pv-hist'
			],
			$this->versionFormatter->format( (int)$row->rev_id )
		);

		$s = "$versionTag&nbsp;" . $s;
		return true;
	}
}
