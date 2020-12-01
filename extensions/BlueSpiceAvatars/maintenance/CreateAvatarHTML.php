<?php

require_once __DIR__ . '/Maintenance.php';

/**
 * Maintenance script to create HTML with links to Avatars
 *
 * @ingroup Maintenance
 */
class CreateAvatarHTML extends Maintenance {

	/**
	 *
	 */
	public function __construct() {
		parent::__construct();
		$this->addDescription( "Create HTML with links to Avatars" );

		$this->requireExtension( 'BlueSpiceFoundation' );
		$this->requireExtension( 'BlueSpiceAvatars' );
	}

	/**
	 *
	 */
	public function execute() {
		$this->output( "<html>\n" );

		// We list user by user_id from one of the slave database
		$dbr = wfGetDB( DB_REPLICA );
		$result = $dbr->select( 'user', [ 'user_id' ], [], __METHOD__
		);

		foreach ( $result as $id ) {

			$user = User::newFromId( $id->user_id );

			if ( !is_object( $user ) ) {
				$this->error( "invalid username.", true );
			}

			$user_id = $id->user_id;
			$user_name = $user->getName();
			$user_real_name = $user->getRealName();

			$this->output( "<img src=\"/SWPedia/index.php?action=ajax&title=-"
					. "&rs=SecureFileStore::getFile"
					. "&f=/bluespice/Avatars/BS_avatar_${user_id}.png\" />\n" );
			$this->output( " $user_id, $user_name, $user_real_name\n" );
			$this->output( "<br />\n" );
		}
		$this->output( "</html>" );
	}

}

$maintClass = "CreateAvatarHTML";
require_once RUN_MAINTENANCE_IF_MAIN;
