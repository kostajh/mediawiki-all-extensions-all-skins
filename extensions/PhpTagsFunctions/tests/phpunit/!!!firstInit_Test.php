<?php

if ( PhpTags\Renderer::$needInitRuntime ) {
	wfDebug( 'PHPTags: run hook PhpTagsRuntimeFirstInit ' . __FILE__ );
	\Hooks::run( 'PhpTagsRuntimeFirstInit' );
	\PhpTags\Hooks::loadData();
	\PhpTags\Runtime::$loopsLimit = 1000;
	PhpTags\Renderer::$needInitRuntime = false;
}
