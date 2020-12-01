<?php
namespace BlueSpice\Social\Job;

class Update extends \BlueSpice\Social\Job {
	const JOBCOMMAND = 'socialentityupdate';

	public function run() {
		$oEntity = $this->getEntity();
		$oEntity->setValuesByObject( (object)$this->getParams() );
		$oStatus = $oEntity->save();
	}
}
