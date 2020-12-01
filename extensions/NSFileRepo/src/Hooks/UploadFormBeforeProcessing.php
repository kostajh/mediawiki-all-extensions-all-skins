<?php

namespace NSFileRepo\Hooks;

class UploadFormBeforeProcessing {

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @var \IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @var \SpecialUpload
	 */
	protected $uploadForm = null;

	/**
	 * Check for Namespace in Title line
	 * @param SpecialPage $uploadForm
	 * @return boolean
	 */
	public static function handle( &$uploadForm ) {
		$instance = new self(
			\RequestContext::getMain(),
			new \NSFileRepo\Config(),
			$uploadForm
		);

		return $instance->process();
	}

	/**
	 * See static method "handle"
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param \SpecialPage $uploadForm 'Extension:PageForms' fires this hook with \PFUploadWindow as parameter
	 */
	public function __construct( \IContextSource $context, \Config $config,  \SpecialPage $uploadForm ) {
		$this->context = $context;
		$this->config = $config;
		$this->uploadForm = $uploadForm;
	}

	public function process() {
		$title = \Title::newFromText( $this->uploadForm->mDesiredDestName );
		if( $title === null ) {
			return true;
		}
		if ( $title->getNamespace() < $this->config->get( 'NamespaceThreshold' ) ) {
			$this->uploadForm->mDesiredDestName = preg_replace( "/:/", '-', $this->uploadForm->mDesiredDestName );
		} else {
			$bits = explode( ':', $this->uploadForm->mDesiredDestName );
			$ns = array_shift( $bits );
			$this->uploadForm->mDesiredDestName = $ns.":" . implode( "-", $bits );
		}
		return true;
	}
}
