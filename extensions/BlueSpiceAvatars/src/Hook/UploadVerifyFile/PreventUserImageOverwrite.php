<?php

namespace BlueSpice\Avatars\Hook\UploadVerifyFile;

class PreventUserImageOverwrite extends \BlueSpice\Hook\UploadVerifyFile {

	protected function skipProcessing() {
		$file = $this->upload->getLocalFile();
		if ( $file === null ) {
			return true;
		}
		$fileName = $file->getName();
		$fileExt = strrpos( $fileName, '.' );

		if ( empty( $fileName ) || !$fileExt ) {
			return true;
		}

		$userName = substr( $fileName, 0, $fileExt );

		$user = \User::newFromName( $userName );
		if ( !$user instanceof \User || $user->isAnon() ) {
			return true;
		}

		if ( $user->getId() === $this->getContext()->getUser()->getId() ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		$this->error = 'bs-imageofotheruser';
		return false;
	}

}
