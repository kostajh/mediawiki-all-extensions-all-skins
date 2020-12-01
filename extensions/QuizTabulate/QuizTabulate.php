<?php
/**
 * QuizTabulate is a quiz tabulation tool for MediaWiki. It requires the Quiz extension.
 *
 * To activate this extension, add the following to your LocalSettings.php :
 * require_once( "$IP/extensions/QuizTabulate/QuizTabulate.php" );
 *
 * @file
 * @ingroup Extensions
 * @author Ike Hecht for [http://www.wikiworks.com/ WikiWorks]
 * @link https://www.mediawiki.org/wiki/Extension:QuizTabulate Documentation
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This is not a valid entry point to MediaWiki.' );
}

/**
 * Extension credits that will show up on Special:Version
 */
$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'QuizTabulate',
	'descriptionmsg' => 'quiz-tabulate-description',
	'version' => '1.2.0.1', #corresponds to Quiz version it was based on and tested with
	'author' => 'Ike Hecht for [http://www.wikiworks.com/ WikiWorks]',
	'url' => 'https://www.mediawiki.org/wiki/Extension:QuizTabulate',
);

$wgAutoloadClasses['QuestionTabulate'] = __DIR__ . '/QuestionTabulate.class.php';
$wgAutoloadClasses['QuizTabulateHooks'] = __DIR__ . '/QuizTabulate.hooks.php';
$wgMessagesDirs['QuizTabulate'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['QuizTabulateMagic'] = __DIR__ . '/QuizTabulate.i18n.magic.php';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'QuizTabulateHooks::quizTabulateSchemaUpdate';
$wgHooks['ParserFirstCallInit'][] = 'QuizTabulateHooks::quizTabulateSetupParserFunction';
$wgHooks['QuizQuestionCreated'][] = 'QuizTabulateHooks::quizTabulateSetupTabulator';
$wgHooks['OutputPageBeforeHTML'][] = 'QuizTabulateHooks::outputQuizTabulate';

#Which users can see quiz tabulations?
$wgGroupPermissions['*']['viewquiztabulate'] = false;
$wgGroupPermissions['user']['viewquiztabulate'] = false;
$wgGroupPermissions['autoconfirmed']['viewquiztabulate'] = false;
$wgGroupPermissions['bot']['viewquiztabulate'] = true; // registered bots
$wgGroupPermissions['sysop']['viewquiztabulate'] = true;
$wgAvailableRights[] = 'viewquiztabulate';

define( 'QUIZ_TABULATE_TABLE', 'quiz_tabulate' );
define( 'QUIZ_TABULATE_QUESTIONS_TABLE', 'quiz_tabulate_questions' );

#set custom namespaces
#@todo Perhaps this should only be done if $wgQuizNamespace was not set.
define( 'QUIZ_TABULATE_NS_QUIZ', 430 );
define( 'QUIZ_TABULATE_NS_QUIZ_TALK', 431 );
$wgExtraNamespaces[QUIZ_TABULATE_NS_QUIZ] = "Quiz";
$wgExtraNamespaces[QUIZ_TABULATE_NS_QUIZ_TALK] = "Quiz_talk";
$wgQuizNamespace = QUIZ_TABULATE_NS_QUIZ;

#Global used to store which quiz page is being displayed. Is there a better way?
$wgQuizPageID = '';
$wgQuizPageLatestRevID = '';
