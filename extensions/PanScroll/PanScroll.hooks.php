<?php

class PanScrollHooks {

	public static function init(Parser &$parser) {
		$parser->setHook('panscroll', 'PanScrollHooks::renderFromTag');
		return true;
	}

	public static function renderFromTag($input, array $args, Parser $parser, PPFrame $frame) {
		$parser->getOutput()->addModules('ext.panscroll.core');
		$parsedInput = $parser->internalParse($input);
		$blockSize = self::getConfigArgs($args);

		$output = <<< EOT
<div id="panscroll-element" class="panscroll-element" style="width: %s; height: %s">
<div class="panscroll-container">
<div class="panscroll-content">%s</div>
</div>
</div>
EOT;
		$output = sprintf($output, $blockSize['width'], $blockSize['height'], $parsedInput);
		return array($output, 'noparse' => false, 'isHTML' => true);

	}

	private static function getConfigArgs(array $args) {
		$size = array('width' => '100%', 'height' => '100%');
		if (array_key_exists('width', $args)) {
			$size['width'] = $args['width'];
		}
		if (array_key_exists('height', $args)) {
			$size['height'] = $args['height'];
		}
		return $size;
	}
}