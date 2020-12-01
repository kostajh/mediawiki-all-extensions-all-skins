<?php

class SynchroniseThreadArticleDataJob extends Job {
	public function __construct( $title, $params ) {
		parent::__construct( 'synchroniseThreadArticleData', $title, $params );
	}

	/**
	 * Execute the job
	 *
	 * @return bool
	 */
	public function run() {
		$article = new WikiPage( $this->title );
		$limit = $this->params['limit'];
		$cascade = $this->params['cascade'];

		Threads::synchroniseArticleData( $article, $limit, $cascade );

		return true;
	}
}
