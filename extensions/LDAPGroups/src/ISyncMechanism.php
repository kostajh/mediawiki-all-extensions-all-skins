<?php

namespace MediaWiki\Extension\LDAPGroups;

interface ISyncMechanism {

	/**
	 * @param \User $user
	 * @param \MediaWiki\Extension\LDAPProvider\GroupList $groupList
	 * @param \Config $config
	 * @return \Status
	 */
	public function sync( $user, $groupList, $config );
}
