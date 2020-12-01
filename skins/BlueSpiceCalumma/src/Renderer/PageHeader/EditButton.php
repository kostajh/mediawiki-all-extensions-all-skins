<?php

namespace BlueSpice\Calumma\Renderer\PageHeader;

use BlueSpice\Calumma\Renderer\PageHeader;
use BlueSpice\Renderer\Params;
use BlueSpice\SkinData;
use Config;
use Html;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use QuickTemplate;
use Title;

class EditButton extends PageHeader {
	public const FEATURED_ACTIONS = SkinData::FEATURED_ACTIONS;

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 * @param QuickTemplate|null $skinTemplate
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '', QuickTemplate $skinTemplate = null ) {
		parent::__construct( $config, $params, $linkRenderer, $context, $name, $skinTemplate );

		$this->args[static::FEATURED_ACTIONS] = $params->get(
			static::FEATURED_ACTIONS,
			[]
		);
	}

	/**
	 *
	 * @return string
	 */
	public function render() {
		$html = '';
		$title = $this->getContext()->getTitle();
		if ( !$title ) {
			return $html;
		}
		if ( !\MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'edit', $this->getContext()->getUser(), $title )
		) {
			return $html;
		}
		$editActions = $this->getActions( $title );
		$actions = $this->ensureAttribs( $editActions );

		return $this->makeButton( $actions );
	}

	/**
	 *
	 * @param Title $title
	 * @return array
	 */
	private function getActions( Title $title ) {
		$actions = $this->args[static::FEATURED_ACTIONS]['edit'];
		if ( isset( $actions['view'] ) ) {
			unset( $actions['view'] );
		}

		return $actions;
	}

	/**
	 *
	 * @param array $editActions
	 * @return array
	 */
	private function ensureAttribs( $editActions ) {
		$items = [];

		foreach ( $editActions as $item ) {
			if ( !isset( $item['id'] ) ) {
				$item['id'] = '';

			}
			if ( !isset( $item['class'] ) ) {
				$item['class'] = '';

			}
			if ( !isset( $item['text'] ) ) {
				$item['text'] = '';

			}
			if ( !isset( $item['title'] ) ) {
				$item['title'] = '';

			}
			if ( !isset( $item['href'] ) ) {
				$item['href'] = '';

			}

			$items[] = $item;
		}

		return $items;
	}

	/**
	 *
	 * @param array $actions
	 * @return string
	 */
	private function makeButton( $actions ) {
		if ( count( $actions ) === 0 ) {
			$editButton = '';
		} elseif ( count( $actions ) === 1 ) {
			$editButton = $this->makeSingleButton( $actions );
		} else {
			$editButton = $this->makeDropdownButton( $actions );
		}

		return $editButton;
	}

	/**
	 *
	 * @param array $actions
	 * @return string
	 */
	private function makeSingleButton( $actions ) {
		$editButton = Html::openElement(
				'span',
				[
					'class' => 'bs-page-edit-button-group pull-right'
				]
			);

		$editButton .= Html::element(
				'a',
				[
					'id' => $actions[0]['id'],
					'class' => 'btn' . $actions[0]['class'],
					'href' => $actions[0]['href'],
					'title' => $actions[0]['text'],
				],
				$actions[0]['text']
			);

		$editButton .= Html::closeElement( 'span' );

		return $editButton;
	}

	/**
	 *
	 * @param array $actions
	 * @return string
	 */
	private function makeDropdownButton( $actions ) {
		$editButton = Html::openElement(
				'span',
				[
					'class' => 'bs-page-edit-button-group pull-right dropdown'
				]
			);

		$editButton .= Html::element(
				'a',
				[
					'class' => 'btn' . $actions[0]['class'],
					'href' => $actions[0]['href'],
					'title' => $actions[0]['text'],
				],
				$actions[0]['text']
			);

		$editButton .= Html::openElement(
				'a',
				[
					'class' => 'btn dropdown-toggle',
					'data-toggle' => 'dropdown',
					'href' => '#',
				]
			);
		$editButton .= Html::element( 'i' );
		$editButton .= Html::closeElement( 'a' );

		$editButton .= Html::openElement(
				'div',
				[
					'class' => 'dropdown-menu'
				]
			);
		$editButton .= Html::openElement( 'ul' );
		$editButton .= Html::openElement( 'li' );

		foreach ( $actions as $item ) {
			$editButton .= Html::element(
				'a',
				[
					'id' => $item['id'],
					'class' => $item['class'],
					'href' => $item['href'],
					'title' => $item['text'],
				],
				$item['text']
			);
		}

		$editButton .= Html::closeElement( 'li' );
		$editButton .= Html::closeElement( 'ul' );
		$editButton .= Html::closeElement( 'div' );

		$editButton .= Html::closeElement( 'span' );

		return $editButton;
	}
}
