<?php
namespace BlueSpice\PageAssignments\Renderer;

use BlueSpice\PageAssignments\IAssignment;
use BlueSpice\Renderer\Params;
use BlueSpice\Utility\CacheHelper;
use Config;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;

class Assignment extends \BlueSpice\TemplateRenderer {
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
	 *
	 * @param mixed $val
	 * @return mixed
	 */
	protected function render_image( $val ) {
		return \Html::element( 'span', [
			'class' => "bs-icon-" . $this->assignment->getType(),
		] );
	}

	/**
	 * Returns the template's name
	 * @return string
	 */
	public function getTemplateName() {
		return "BlueSpicePageAssignments.Assignment";
	}

}
