<?php

class ImageCompare {
	public static function onExtensionLoad() {}
	
	public static function onParserInit(Parser $parser) {
		$parser->setHook('imgcomp', 'ImageCompare::parseTag');
	}
	
	# Whether the client is using mobile interface. Used to determine the width of images.
	public static function isOnMobile() {
		if (
			class_exists("MobileContext") # MobileFrontend is installed.
			&& MobileContext::singleton()->isMobileDevice() # Device is viewing the mobile version.
		) return true;
		else return false;
	}
	
	/* Mobile friendly <imgcomp> parser.
	   Parameters:
	   - img1; img2: Title of two images, to the left and to the right, without "File:".
	   - width: Width in pixels for images, for desktop. Defaults to their original width.
	   - mobilewidth: Width in pixels for images in mobile version. Defaults to 320px or width of it is lower.
	*/
	public static function parseTag($input, array $args, Parser $parser, PPFrame $frame) {
		try {
			$mobile = ImageCompare::isOnMobile();
			$mobilewidth = 320;
			$parser->getOutput()->addModules( 'ext.imageCompare'.($mobile ? '.mobile' : '') );
			$parser->getOutput()->addModuleStyles( 'ext.imageCompare.styles'.($mobile ? '.mobile' : '') );
			if (!isset($args['img1'], $args['img2'])) {
				throw new ImageCompareException("error-noimg");
			}
			$divheight = 0;
			$width = null;
			if (isset($args['width']))
				if (is_numeric($args['width']))
					$width = round($args['width']);
				else throw new ImageCompareException("error-numberinvalid");
			if (isset($args['mobilewidth']))
				if (is_numeric($args['mobilewidth']))
					$mobilewidth = round($args['mobilewidth']);
				else throw new ImageCompareException("error-numberinvalid");
			else if ($width < 320) $mobilewidth = $width;
			if ($mobile) $width = $mobilewidth;
			$return = ImageCompare::makeImage($parser, Title::newFromDBKey(str_replace(' ', '_', 'File:'.$args['img2'].'')), 'img-comp-img', $divheight, $width)
			.ImageCompare::makeImage($parser, Title::newFromDBKey(str_replace(' ', '_', 'File:'.$args['img1'].'')), 'img-comp-img img-comp-overlay', $divheight, $width)
			.'</div>';
			return "<div class='img-comp-container' style='height: {$divheight}px;'>".$return;
		} catch (ImageCompareException $e) {
			return wfMessage('ImageCompare-'.$e->getMessage())->parse();
		}
	}

	# Modified code from Parser.
	public static function makeImage($parser, $title, $classes, &$divheight, $width) {
		# Fetch and register the file (file title may be different via hooks)
		list( $file, $title ) = $parser->fetchFileAndTitle( $title, "" );

		$imgheight = 0;
		
		# Linker does the rest
		$time = isset( $options['time'] ) ? $options['time'] : false;
		$ret = ImageCompare::makeImageLink($parser, $title, $file, $classes, $imgheight, $width);
		
		if ($imgheight > $divheight) $divheight = $imgheight;

		return $ret;
	}
	
	# Modified code from Linker.
	public static function makeImageLink(Parser $parser, Title $title, $file, $classes, &$imgheight, $width) {
		$res = null;
		$dummy = new DummyLinker;
		if ( !Hooks::run( 'ImageBeforeProduceHTML', [ &$dummy, &$title,
			&$file, &$frameParams, &$handlerParams, &$time, &$res ] ) ) {
			return $res;
		}

		if (!$file) {
			throw new ImageCompareException("error-imginvalid");
		}
		
		$prefix = $postfix = '';

		$handlerparams['width'] = $file->getWidth( isset( $handlerParams['page'] ) ? $handlerParams['page'] : false );
		if (isset($width) && $width < $handlerparams['width']) $handlerparams['width'] = $width;
		$thumb = $file->transform( $handlerparams );
		if ( !$thumb ) {
			$s = Linker::makeBrokenImageLinkObj( $title, $frameParams['title'], '', '', '', $time == true );
		} else {
			$imgheight = $thumb->getHeight( isset( $handlerParams['page'] ) ? $handlerParams['page'] : false );
			Linker::processResponsiveImages( $file, $thumb, $handlerParams );
			$s = $thumb->toHtml( [] );
		}
		$s = "<div class=\"float {$classes}\">{$s}</div>";
		return str_replace( "\n", ' ', $prefix . $s . $postfix );
	}
}