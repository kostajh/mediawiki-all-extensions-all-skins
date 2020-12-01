<?php

final class QuizTabulateHooks {

	/**
	 * Schema update to set up the needed database tables.
	 *
	 * @global string $wgDBtype
	 * @param DatabaseUpdater $updater
	 * @return boolean
	 */
	public static function quizTabulateSchemaUpdate( DatabaseUpdater $updater = null ) {
		global $wgDBtype;

		// Set up the current schema.
		$updater->addExtensionTable(
			QUIZ_TABULATE_TABLE, __DIR__ . '/QuizTabulate.sql', true
		);
		$updater->addExtensionTable(
			QUIZ_TABULATE_QUESTIONS_TABLE, __DIR__ . '/QuizTabulate.sql', true
		);

		return true;
	}

	/**
	 * Add quiz tabulation to output
	 *
	 * @global int $wgQuizNamespace
	 * @param OutputPage $outputpage
	 * @param string $text
	 * @return boolean
	 */
	public static function outputQuizTabulate( OutputPage $outputpage, &$text ) {
		global $wgQuizNamespace;

		if ( !$outputpage->getUser()->isAllowed( 'viewquiztabulate' ) ) {
			return true;
		}

		$title = $outputpage->getTitle();
		$currentAction = Action::getActionName( $outputpage );
		if ( !$title->getNamespace() == $wgQuizNamespace || $currentAction != 'view' ) {
			return true;
		}

		$revisionId = $outputpage->getRevisionId();
		# if latest revision, getRevisionId is 0 so find latest revision id. is there a better way?
		if ( !$revisionId ) {
			$revisionId = $title->getLatestRevID();
		}

		#get this revision's data from db
		$dbr = wfGetDB( DB_REPLICA );
		$fullTableName = QUIZ_TABULATE_TABLE;
		$proposals = $dbr->select(
			$fullTableName, array( 'question_id', 'answer_id', 'count_attempt', 'answer_text' ),
			"quiz_rev_id=$revisionId", __METHOD__
		);
		if ( $proposals->numRows() == 0 ) {
			return true; #no quizzes submitted
		}

		#first put all data for this revision into an array
		$allResults = array();
		while ( $row = $proposals->fetchObject() ) {
			$allResults[$row->question_id][$row->answer_id] = array(
				'countAttempt' => $row->count_attempt,
				'answerText' => $row->answer_text
			);
		}

		#then spit it out nice-like
		$text .= XML::openElement( 'div', array( 'id' => 'quiz_tabulate', 'style' => 'clear:both;' ) );
		$text .= Xml::element( 'h2', array( 'id' => 'quiz_tabulate_title' ),
				wfMessage( 'quiz-tabulate-results' )->text() );

		$fullTableName = QUIZ_TABULATE_QUESTIONS_TABLE;
		foreach ( $allResults as $question_id => $results ) {
			$totalAnswers = 0;
			foreach ( $results as $answer ) {
				$totalAnswers += $answer['countAttempt'];
			}

			$questionTextObject = $dbr->select(
				$fullTableName, array( 'question_text' ),
				"quiz_rev_id=$revisionId AND question_id = $question_id", __METHOD__
			);

			$headingText = 'Question #' . ( $question_id + 1 ) . ': ' . $questionTextObject->fetchObject()->question_text;
			$text .= Xml::element( 'h3', array( 'class' => 'quiz_tabulate_question' ), $headingText );
			$text .= Xml::element( 'p', array( 'class' => 'quiz_tabulate_total' ), "Answers: $totalAnswers" );
			$tableRows = array();
			foreach ( $results as $answer ) {
				$tableRows[] = array( $answer['answerText'], $answer['countAttempt'], number_format( $answer['countAttempt']
						/ $totalAnswers * 100, 0 ) . '%' );
			}
			$text .= Xml::buildTable( $tableRows, array( 'class' => 'wikitable quiz_tabulate_answers' ),
					array( 'Answer', 'Count', 'Percentage' ) );
		}
		$text .= XML::closeElement( 'div' );
		return true;
	}

	/**
	 * quiztabulate parser function
	 *
	 * @param Parser $parser
	 * @return boolean
	 */
	public static function quizTabulateSetupParserFunction( Parser $parser ) {
		$parser->setFunctionHook( 'quiztabulate', __CLASS__ . '::quizTabulateRenderParserFunction' );
		return true;
	}

	/**
	 *
	 * @global int $wgQuizPageLatestRevID
	 * @global int $wgQuizNamespace
	 * @param Parser $parser
	 * @param string $quizName
	 * @param boolean $includeQuizTags
	 * @return array
	 */
	public static function quizTabulateRenderParserFunction( Parser $parser, $quizName = '',
		$includeQuizTags = false ) {
		global $wgQuizPageLatestRevID, $wgQuizNamespace;

		if ( $quizName == '' ) {
			return '';
		}
		$quizPage = Title::newFromText( $quizName, $wgQuizNamespace );
		if ( !$quizPage->isKnown() ) {
			return '';
		}

		$wgQuizPageLatestRevID = $quizPage->getLatestRevID();

		#@todo Perhaps return page contents instead of transclude.
		#@todo Only works for one quiz per page, which is not so bad since Quiz is buggy when including more than once anyway.
		if ( $includeQuizTags ) {
			$output = "{{#tag:quiz|\n{{" . $quizPage->getFullText() . '}}}}';
		} else {
			$output = "{{" . $quizPage->getFullText() . '}}';
		}

		return array( $output, 'noparse' => false );
	}

	/**
	 * Override Question object with our own Question child
	 *
	 * @global int $wgQuizPageLatestRevID
	 * @global int $wgQuizNamespace
	 * @param Quiz $quiz
	 * @param Question &$question
	 * @return boolean
	 */
	public static function quizTabulateSetupTabulator( Quiz $quiz, Question &$question ) {
		global $wgQuizPageLatestRevID, $wgQuizNamespace;

		#if this isn't in the Quiz namespace and wgQuizPageLatestRevID wasn't set (in other words, the #quiztabulate: function wasn't called)
		if ( $quiz->mParser->getTitle()->getNamespace() != $wgQuizNamespace && !$wgQuizPageLatestRevID ) {
			return true;
		}
		$question = new QuestionTabulate(
			$quiz->mBeingCorrected, $quiz->mCaseSensitive, $quiz->mQuestionId, $quiz->mParser
		);

		return true;
	}
}
