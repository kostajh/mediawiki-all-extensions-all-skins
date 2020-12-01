<?php

namespace BlueSpice\Avatars\Tag;

use BlueSpice\Avatars\DynamicFileDispatcher\UserProfileImage;
use BlueSpice\DynamicFileDispatcher\Params as DFDParams;
use BlueSpice\DynamicFileDispatcher\UrlBuilder;
use BlueSpice\Renderer\Params;
use BlueSpice\Renderer\UserImage;
use BlueSpice\RendererFactory;
use BlueSpice\Tag\Handler;
use Html;
use Message;
use MWException;
use Parser;
use PPFrame;
use User;

class ProfileImageHandler extends Handler {

	/**
	 *
	 * @var UrlBuilder
	 */
	protected $dfdUrlBuilder = null;

	/**
	 *
	 * @var RendererFactory
	 */
	protected $rendererFactory = null;

	/**
	 *
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @param UrlBuilder $dfdUrlBuilder
	 * @param RendererFactory $rendererFactory
	 */
	public function __construct( $processedInput, array $processedArgs, Parser $parser,
		PPFrame $frame, UrlBuilder $dfdUrlBuilder, RendererFactory $rendererFactory ) {
		parent::__construct( $processedInput, $processedArgs, $parser, $frame );
		$this->dfdUrlBuilder = $dfdUrlBuilder;
		$this->rendererFactory = $rendererFactory;
	}

	/**
	 *
	 * @return string
	 */
	public function handle() {
		$this->processedArgs['username'] = isset( $this->processedArgs['username'] )
			? $this->processedArgs['username']
			: '';
		$this->processedArgs['width'] = isset( $this->processedArgs['width'] )
			? (int)$this->processedArgs['width']
			: 32;
		$this->processedArgs['height'] = isset( $this->processedArgs['height'] )
			? (int)$this->processedArgs['height']
			: 32;
		$this->processedArgs['raw'] = isset( $this->processedArgs['raw'] )
			&& $this->processedArgs['raw']
			? true
			: false;

		if ( $this->processedArgs['raw'] === true ) {
			return $this->handleRaw();
		}
		$user = User::newFromName( $this->processedArgs['username'] );
		if ( !$user ) {
			$msg = Message::newFromKey(
				'bs-avatars-tag-profileimage-error-invalidusername'
			);
			throw new MWException( $msg );
		}
		$params = [
			UserImage::PARAM_HEIGHT => $this->processedArgs['height'],
			UserImage::PARAM_WIDTH => $this->processedArgs['width'],
			UserImage::PARAM_USER => $user,
			UserImage::PARAM_CLASS => 'bs-avatars-userimage-tag'
		];
		return $this->rendererFactory->get(
			'userimage',
			new Params( $params )
		)->render();
	}

	/**
	 *
	 * @return string
	 */
	protected function handleRaw() {
		$params = [
			DFDParams::MODULE => UserProfileImage::MODULE_NAME,
			UserProfileImage::USERNAME => $this->processedArgs['username'],
			UserProfileImage::WIDTH => $this->processedArgs['width'],
			UserProfileImage::HEIGHT => $this->processedArgs['height']
		];

		$url = $this->dfdUrlBuilder->build( new DFDParams( $params ) );

		$html = Html::element( 'img', [
			'src' => $url,
			'alt' => Message::newFromKey(
				'bs-avatars-tag-userimage-img-alt',
				$this->processedArgs['username']
			)->text(),
			'class' => 'bs-avatars-userimage-tag',
			'width' => $this->processedArgs['width'],
			'height' => $this->processedArgs['height']
		] );

		return $html;
	}
}
