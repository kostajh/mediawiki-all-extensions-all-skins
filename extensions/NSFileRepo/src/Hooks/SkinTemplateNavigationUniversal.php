<?php

namespace NSFileRepo\Hooks;

use NSFileRepo\NamespaceList;
use IContextSource;
use Config;
use SkinTemplate;
use Message;
use Title;
use FormatJson;

class SkinTemplateNavigationUniversal {

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @var IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @var SkinTemplate
	 */
	protected $sktemplate = null;

	/**
	 *
	 * @var array
	 */
	protected $links = [];

	/**
	 *
	 * @param SkinTemplate $sktemplate
	 * @param array $links
	 * @return boolean
	 */
	public static function handle( SkinTemplate $sktemplate, &$links ) {
		$instance = new self(
			\RequestContext::getMain(),
			new \NSFileRepo\Config(),
			$sktemplate,
			$links
		);

		return $instance->process();
	}


	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @param SkinTemplate $sktemplate
	 * @param array $links
	 */
	public function __construct( IContextSource $context, Config $config, SkinTemplate $sktemplate, &$links ) {
		$this->context = $context;
		$this->config = $config;
		$this->sktemplate = $sktemplate;
		$this->links =& $links;
	}

	/**
	 *
	 * @return boolean
	 */
	public function process() {
		if ( !$this->isFilePage() ) {
			return true;
		}

		$editableNamespaces = $this->getEditableNamespaces();
		if ( empty( $editableNamespaces ) ) {
			return true;
		}

		$excludeNS = $this->makeExcludeNS( $editableNamespaces );
		$filetitle = Title::newFromText( $this->sktemplate->getTitle()->getDBkey() );

		$this->links['actions']['move-file-namespace'] = [
			'class' => 'nsfr-move-file-namespace',
			'text' => Message::newFromKey( 'nsfilerepo-move-file-namespace-action-label' )->plain(),
			'href' => '#',
			'data' => [
				'currentNamespace' => $filetitle->getNamespace(),
				'unprefixedFilename' => $filetitle->getDBkey(),
				'excludeNS' => FormatJson::encode( $excludeNS )
			]
		];

		$this->sktemplate->getOutput()->addModules( 'ext.nsfilerepo.filepage.bootstrap' );

		return true;
	}

	private function isFilePage() {
		return $this->sktemplate->getTitle()->getNamespace() === NS_FILE;
	}

	private function makeExcludeNS( $editableNamespaces ) {
		$allNamespaces = array_keys( $this->sktemplate->getLanguage()->getNamespaces() );

		$nonEditableNamespaces = array_diff( $allNamespaces, $editableNamespaces );
		return array_values( $nonEditableNamespaces );
	}

	private function getEditableNamespaces() {
		$nsList = new NamespaceList(
			$this->sktemplate->getUser(),
			$this->config,
			$this->sktemplate->getLanguage()
		);

		$editableNamespaces = array_keys( $nsList->getEditable() );

		return $editableNamespaces;
	}
}
