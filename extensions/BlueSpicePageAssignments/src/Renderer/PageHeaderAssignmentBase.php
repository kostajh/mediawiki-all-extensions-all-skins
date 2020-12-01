<?php
namespace BlueSpice\PageAssignments\Renderer;

use BlueSpice\PageAssignments\IAssignment;
use BlueSpice\Renderer\Params;
use BlueSpice\Utility\CacheHelper;
use Config;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;

class PageHeaderAssignmentBase extends Assignment {
	const PARAM_ASSIGNMENT = 'assignment';

	/**
	 *
	 * @var IAssignment
	 */
	protected $assignment = null;

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

		$this->assignment = $params->get(
			static::PARAM_ASSIGNMENT,
			false
		);
		$this->args['image'] = '';
		$this->args = array_merge(
			(array)$this->assignment->toStdClass(),
			$this->args
		);
	}

	/**
	 * Returns the template's name
	 * @return string
	 */
	public function getTemplateName() {
		return "BlueSpicePageAssignments.PageHeaderAssignment";
	}

	/**
	 * Returns an array of arguments
	 * @return array
	 */
	public function getArgs() {
		$args = parent::getArgs();

		return $args;
	}
}
