<?php

namespace BlueSpice\Avatars;

class Generator {
	const FILE_PREFIX = "BS_avatar_";

	const PARAM_OVERWRITE = 'overwrite';
	const PARAM_HEIGHT = 'height';
	const PARAM_WIDTH = 'width';

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @param \Config $config
	 */
	public function __construct( $config ) {
		$this->config = $config;
	}

	/**
	 *
	 * @param \User $user
	 * @param array $params
	 * @return string
	 */
	public function generate( \User $user, array $params = [] ) {
		$defaultSize = 1024;

		$oFile = $this->getAvatarFile( $user );
		if ( !$oFile ) {
			return '';
		}

		if ( !$oFile->exists() || isset( $params[static::PARAM_OVERWRITE] ) ) {
			switch ( $this->config->get( 'AvatarsGenerator' ) ) {
				case 'Identicon':
					$rawPNGAvatar = $this->generateIdention(
						$user,
						$defaultSize
					);
					break;
				case 'InstantAvatar':
					$rawPNGAvatar = $this->generateInstantAvatar(
						$user,
						$defaultSize
					);
					break;
				default:
					throw new \MWException(
						'FATAL: Avatar generator not found!'
					);
			}

			$status = \BsFileSystemHelper::saveToDataDirectory(
				$oFile->getName(),
				$rawPNGAvatar,
				'Avatars'
			);
			if ( !$status->isGood() ) {
				throw new \MWException(
					'FATAL: Avatar could not be saved! ' . $status->getMessage()
				);
			}
			# Delete thumb folder if it exists
			$status = \BsFileSystemHelper::deleteFolder(
				"Avatars/thumb/{$oFile->getName()}",
				true
			);
			if ( !$status->isGood() ) {
				throw new \MWException(
					'FATAL: Avatar thumbs could no be deleted!'
				);
			}
			$oFile = \BsFileSystemHelper::getFileFromRepoName(
				$oFile->getName(),
				'Avatars'
			);

			$user->invalidateCache();
		}
	}

	/**
	 *
	 * @param \User $user
	 * @param int $size
	 * @return string
	 */
	protected function generateIdention( \User $user, $size ) {
		require_once dirname( __DIR__ ) . "/includes/lib/Identicon/identicon.php";
		return generateIdenticon( $user->getId(), $size );
	}

	/**
	 *
	 * @param \User $user
	 * @param int $size
	 * @return string
	 */
	protected function generateInstantAvatar( \User $user, $size ) {
		$dir = dirname( __DIR__ ) . "/includes/lib/InstantAvatar";
		require_once "$dir/instantavatar.php";

		$instantAvatar = new \InstantAvatar(
			"$dir/Comfortaa-Regular.ttf",
			round( 18 / 40 * $size ),
			$size,
			$size,
			2,
			"$dir/glass.png"
		);

		if ( !empty( $user->getRealName() ) ) {
			preg_match_all(
				'#(^| )(.)#u',
				$user->getRealName(),
				$matches
			);
			$chars = implode( '', $matches[2] );
			if ( mb_strlen( $chars ) < 2 ) {
				$chars = $user->getRealName();
			}
		} else {
			$chars = $user->getName();
		}

		$instantAvatar->generateRandom( $chars );
		return $instantAvatar->getRawPNG();
	}

	/**
	 * Gets Avatar file from user ID
	 * @param \User $user
	 * @return bool|\File
	 */
	public function getAvatarFile( \User $user ) {
		return \BsFileSystemHelper::getFileFromRepoName(
			static::FILE_PREFIX . $user->getId() . ".png",
			'Avatars'
		);
	}
}
