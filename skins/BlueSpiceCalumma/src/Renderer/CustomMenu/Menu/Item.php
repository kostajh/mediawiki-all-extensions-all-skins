<?php

namespace BlueSpice\Calumma\Renderer\CustomMenu\Menu;

class Item extends \BlueSpice\CustomMenu\Renderer\Menu\Item {
	/**
	 *
	 * @return string
	 */
	protected function makeItemRendererKey() {
		return 'calummacustommenuitem';
	}

	/**
	 *
	 * @param int $level
	 * @return array
	 */
	protected function makeChildMenuOpeningTagAttribs( $level ) {
		$attribs = parent::makeChildMenuOpeningTagAttribs( $level );
		$attribs[static::PARAM_CLASS] .= ' dropdown-menu';

		return $attribs;
	}

	/**
	 *
	 * @return array
	 */
	protected function makeTagAttribs() {
		$attribs = parent::makeTagAttribs();

		if ( $this->isRootDropDown() ) {
			$attribs[static::PARAM_CLASS] .= ' dropdown';
		} elseif ( $this->hasChildren() ) {
			$attribs[static::PARAM_CLASS] .= ' dropdown-submenu';
		}

		return $attribs;
	}

	/**
	 *
	 * @return bool
	 */
	private function isRootDropDown() {
		return $this->hasChildren()	&& $this->args[static::PARAM_LEVEL] === 1;
	}

	/**
	 *
	 * @return string
	 */
	protected function makeItemAnchor() {
		$icon = \Html::element( 'i', [ 'class' => 'pull-right' ] );
		$label = \Html::element( 'span', [], $this->args[static::PARAM_TEXT] );

		$anchor = \Html::rawElement(
			'a',
			$this->makeItemAnchorAttribs(),
			$label . $icon
		);
		return $anchor;
	}

	/**
	 *
	 * @return array
	 */
	protected function makeItemAnchorAttribs() {
		$attribs = parent::makeItemAnchorAttribs();

		if ( $this->isRootDropDown() ) {
			$attribs['data-toggle'] = 'dropdown';
			$attribs[static::PARAM_CLASS] = ' dropdown-toggle';
		} elseif ( $this->hasChildren() ) {
			$attribs[static::PARAM_CLASS] = ' dropdown-submenu-toggle';
			$attribs[static::PARAM_HREF] = '';
		}

		return $attribs;
	}

}
