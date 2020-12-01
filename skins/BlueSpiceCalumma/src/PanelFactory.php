<?php

namespace BlueSpice\Calumma;

use BlueSpice\Calumma\Panel\Form;
use BlueSpice\Calumma\Panel\LinkList;
use BlueSpice\Calumma\Panel\PlainHTML;
use QuickTemplate;

class PanelFactory {

	/**
	 *
	 * @var array
	 */
	protected $panelDefinitions = [];

	/**
	 *
	 * @var QuickTemplate
	 */
	protected $sktemplate = null;

	/**
	 *
	 * @var string
	 */
	protected $currentPanelKey = '';

	/**
	 *
	 * @var array
	 */
	protected $currentPanelDef = [];

	/**
	 *
	 * @var BlueSpice\Calumma\Components\IPanel[]
	 */
	protected $panels = [];

	/**
	 *
	 * @param array $panelDefs
	 * @param QuickTemplate $sktemplate
	 */
	public function __construct( $panelDefs, QuickTemplate $sktemplate ) {
		$this->panelDefinitions = $panelDefs;
		$this->sktemplate = $sktemplate;
	}

	/**
	 * @return BlueSpice\Calumma\IPanel[]
	 */
	public function makePanels() {
		$this->panels = [];
		$this->sortPanels();
		foreach ( $this->panelDefinitions as $panelKey => $panelDef ) {
			$this->currentPanelKey = $panelKey;
			$this->currentPanelDef =& $panelDef;
			$this->makeCurrentPanel();
		}

		return $this->panels;
	}

	/**
	 *
	 * @throws \Exception
	 */
	protected function makeCurrentPanel() {
		if ( $this->isLinkList( $this->currentPanelDef ) ) {
			$this->addPanel( new LinkList(
				$this->currentPanelKey,
				$this->currentPanelDef
			) );
		} elseif ( $this->isPlainHTML( $this->currentPanelDef ) ) {
			$this->addPanel( new PlainHTML(
				$this->currentPanelKey,
				$this->currentPanelDef
			) );
		} elseif ( $this->isForm( $this->currentPanelDef ) ) {
			$this->addPanel( new Form(
				$this->currentPanelKey,
				$this->currentPanelDef
			) );
		} elseif ( $this->isCallback( $this->currentPanelDef ) ) {
			$panel = call_user_func_array(
				$this->currentPanelDef['callback'],
				[ $this->sktemplate ]
			);

			if ( $panel instanceof IPanel === false ) {
				throw new \Exception(
					"Callback of {$this->currentPanelKey}"
					. " did not return an IPanel!"
				);
			}

			$this->addPanel( $panel );
		}
	}

	/**
	 *
	 * @param \BlueSpice\Calumma\IPanel $instance
	 */
	protected function addPanel( $instance ) {
		$this->panels[$this->currentPanelKey] = $instance;
	}

	/**
	 * Sorts the panels
	 */
	protected function sortPanels() {
		uasort( $this->panelDefinitions, function ( $a, $b ) {
			$posA = isset( $a['position'] ) ? $a['position'] : 0;
			$posB = isset( $b['position'] ) ? $b['position'] : 0;
			return $posA > $posB;
		} );
	}

	/**
	 *
	 * @param array $panelDef
	 * @return bool
	 */
	protected function isLinkList( $panelDef ) {
		if ( !isset( $panelDef['content'] ) ) {
			return false;
		}
		return ( !isset( $panelDef['type'] ) && is_array( $panelDef['content'] ) )
			||
			( isset( $panelDef['type'] ) && $panelDef['type'] === 'linklist' );
	}

	/**
	 *
	 * @param array $panelDef
	 * @return bool
	 */
	protected function isPlainHTML( $panelDef ) {
		if ( !isset( $panelDef['content'] ) ) {
			return false;
		}
		return ( !isset( $panelDef['type'] ) && is_string( $panelDef['content'] ) )
			||
			( isset( $panelDef['type'] ) && $panelDef['type'] === 'html' );
	}

	/**
	 *
	 * @param array $panelDef
	 * @return bool
	 */
	protected function isForm( $panelDef ) {
		return ( isset( $panelDef['type'] ) && $panelDef['type'] === 'form' );
	}

	/**
	 *
	 * @param array $panelDef
	 * @return bool
	 */
	protected function isCallback( $panelDef ) {
		return (
			!isset( $panelDef['label'] )
			&& !isset( $panelDef['content'] )
			&& isset( $panelDef['callback'] )
		)
		||
		( isset( $panelDef['type'] ) && $panelDef['type'] === 'callback' );
	}
}
