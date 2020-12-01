<?php
	class SpecialGoogleAnalyticsTopPages extends SpecialPage {
		public function __construct() {
			parent::__construct( 'GoogleAnalyticsTopPages' );
		}

		public function execute( $par ) {
		}

		protected function getGroupName() {
			return 'other';
		}
	}
