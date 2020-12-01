<?php

/**
 * GoogleAppEngine extension for MediaWiki
 *
 * Provides enhancements to MediaWiki instances
 * running on the GoogleAppEngine platform
 *
 * @license GPL v3+
 * @author Kunal Mehta <legoktm@gmail.com>
 */

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'GoogleAppEngine',
	'author' => 'Kunal Mehta',
	'url' => 'https://www.mediawiki.org/wiki/Extension:GoogleAppEngine',
	'descriptionmsg' => 'gae-desc',
	'version' => '0.1.0',
	'license-name' => 'GPL-3.0-or-later',
);

$wgMessagesDirs['GoogleAppEngine'] = __DIR__ . '/i18n';

$wgAutoloadClasses['ApiGAEJobQueue'] = __DIR__ . '/job/ApiGAEJobQueue.php';
$wgAutoloadClasses['JobQueueGAE'] = __DIR__ . '/job/JobQueueGAE.php';
$wgAutoloadClasses['GoogleAppEngineHooks'] = __DIR__ . '/GoogleAppEngineHooks.php';

$wgHooks['AlternateUserMailer'][] = 'GoogleAppEngineHooks::onAlternateUserMailer';

$wgAPIModules['gaejobqueue'] = 'ApiGAEJobQueue';

$wgJobTypeConf['default']['class'] = 'JobQueueGAE';
$wgJobRunRate = 0;

// PHP Memcached doesn't work, need to use the Pecl version.
$wgObjectCaches[CACHE_MEMCACHED] = array( 'class' => 'MemcachedPeclBagOStuff' );
