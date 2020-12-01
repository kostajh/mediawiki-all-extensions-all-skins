<?php

namespace BlueSpice\TagCloud;

use BlueSpice\Renderer\Params;
use BlueSpice\TagCloud\Data\TagCloud\Record;
use BlueSpice\TagCloud\Data\TagCloud\ResultSet;
use BlueSpice\Utility\CacheHelper;
use Config;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;

abstract class Renderer extends \BlueSpice\TemplateRenderer {
	const PARAM_RENDERER = 'renderer';
	const PARAM_STORE = 'store';

	const PARAM_RESULT = 'result';
	const PARAM_CONTEXT = 'context';

	const PARAM_WIDTH = 'width';
	const PARAM_SHOW_COUNT = 'showcount';
	const PARAM_HEADLINE = 'title';

	const PARAM_MINSIZE = 'minsize';
	const PARAM_MAXSIZE = 'maxsize';

	/**
	 *
	 * @var ResultSet
	 */
	protected $result = null;

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 * @param CacheHelper|null $cacheHelper
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '', CacheHelper $cacheHelper = null ) {
		parent::__construct(
			$config,
			$params,
			$linkRenderer,
			$context,
			$name,
			$cacheHelper
		);

		$result = $params->get(
			static::PARAM_RESULT,
			null
		);
		if ( !$result instanceof \BlueSpice\Data\ResultSet ) {
			throw new \MWException(
				__CLASS__ . ':' . __METHOD__ . ' - invalid "' . static::PARAM_RESULT
			);
		}
		$this->result = new ResultSet( $result );

		$this->context = $params->get(
			static::PARAM_CONTEXT,
			null
		);
		if ( !$this->context instanceof Context ) {
			throw new \MWException(
				__CLASS__ . ':' . __METHOD__ . ' - invalid "' . static::PARAM_CONTEXT
			);
		}

		$this->args[static::PARAM_WIDTH] = $params->get(
			static::PARAM_WIDTH,
			"100%"
		);

		$showcountParam = $params->get(
			static::PARAM_SHOW_COUNT,
			true
		);
		if ( $showcountParam == false || $showcountParam === "false" ) {
			$this->args[static::PARAM_SHOW_COUNT] = false;
		} else {
			$this->args[static::PARAM_SHOW_COUNT] = true;
		}

		$this->args[static::PARAM_HEADLINE] = $params->get(
			static::PARAM_HEADLINE,
			''
		);

		$this->args[static::PARAM_RENDERER] = $params->get(
			static::PARAM_RENDERER,
			''
		);

		$this->args[static::PARAM_STORE] = $params->get(
			static::PARAM_STORE,
			''
		);

		$this->args[static::PARAM_MINSIZE] = $params->get(
			static::PARAM_MINSIZE,
			8
		);

		$this->args[static::PARAM_MAXSIZE] = $params->get(
			static::PARAM_MAXSIZE,
			24
		);

		$this->args[static::PARAM_TAG] = 'ul';
		$this->args[static::PARAM_CONTENT] = [];

		if ( !$this->args[static::PARAM_CLASS] ) {
			$this->args[static::PARAM_CLASS] = '';
		}
		$this->args[static::PARAM_CLASS] .=
			" bs-tagcloud"
			. " {$this->args[static::PARAM_STORE]}"
			. " {$this->args[static::PARAM_RENDERER]}";
	}

	/**
	 *
	 * @return string
	 */
	public function getTemplateName() {
		return "BlueSpiceTagCloud.List";
	}

	/**
	 *
	 * @param int $count
	 * @param int $mincount
	 * @param int $maxcount
	 * @param int $minsize
	 * @param int $maxsize
	 * @param int $tresholds
	 * @return int
	 */
	protected function getTagSizeLogarithmic( $count, $mincount, $maxcount, $minsize,
		$maxsize, $tresholds = 0 ) {
		if ( !is_int( $tresholds ) || $tresholds < 2 ) {
			$tresholds = $maxsize - $minsize;
			$treshold = 1;
		} else {
			$treshold = ( $maxsize - $minsize ) / ( $tresholds - 1 );
		}
		$log = $tresholds * log( $count - $mincount + 2 )
			/ log( $maxcount - $mincount + 2 ) - 1;

		return round( $minsize + round( $log ) * $treshold );
	}

	/**
	 *
	 * @return array
	 */
	protected function makeTagAttribs() {
		$attribs = parent::makeTagAttribs();
		$attribs['style'] = '';
		foreach ( $this->makeTagStyles() as $key => $style ) {
			$attribs['style'] .= " $key:$style;";
		}
		return $attribs;
	}

	/**
	 *
	 * @param array $styles
	 * @return array
	 */
	protected function makeTagStyles( $styles = [] ) {
		$styles[static::PARAM_WIDTH] = $this->args[static::PARAM_WIDTH];
		return $styles;
	}

	/**
	 *
	 * @param mixed $val
	 * @return mixed
	 */
	protected function render_content( $val ) {
		foreach ( $this->result->getRecords() as $record ) {
			$data = array_filter( (array)$record->getData(), function ( $e ) {
				return !empty( $e );
			} );
			$data['weight'] = $this->getTagSizeLogarithmic(
				$record->get( Record::COUNT, 0 ),
				$this->result->getLowestCount(),
				$this->result->getHighestCount(),
				$this->args[static::PARAM_MINSIZE],
				$this->args[static::PARAM_MAXSIZE]
			);
			$val[] = $data;
		}
		return $val;
	}

}
