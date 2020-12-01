<?php

class SectionDisqus {

	static function addResources( &$out ) {
		$out->addModules( 'ext.SectionDisqus' );
		return true;
	}

	static function addDisqusButton( $skin, $title, $section, $tooltip, &$result ) {
		$result .= ' <span class="disqus_button">[ <a onclick="window.showDisqusDialog(\'' . $tooltip . '\');">disqus</a> ]</span>';
		return true;
	}

	static function addDisqusDialog( &$data ) {
		global $wgSectionDisqusShortname, $wgDisqusShortname;
		if ( $wgSectionDisqusShortname !== false ) {
			$shortName = $wgSectionDisqusShortname;
		} elseif ( isset( $wgDisqusShortname ) ) {
			$shortName = $wgDisqusShortname;
		} else {
			// placeholder?
			$shortName = 'your-disqus-shortname';
		}
		$data .= '<div id="disqus_dialog" title="Discuss this section"><div id="disqus_thread"></div></div>';
		$data .= '<script>

			var disqus_shortname = "' . $shortName . '";

			(function() {
				var dsq = document.createElement("script");
				dsq.type = "text/javascript";
				dsq.async = true;
				dsq.src = "//" + disqus_shortname + ".disqus.com/embed.js";
				document.getElementsByTagName("body")[0].appendChild(dsq);
			})();
			</script>';
		return true;
	}
}