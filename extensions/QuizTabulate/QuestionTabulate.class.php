<?php

class QuestionTabulate extends Question {

	/**
	 * Constructor
	 *
	 * @global int $wgQuizPageLatestRevID
	 * @param boolean $beingCorrected
	 * @param boolean $caseSensitive
	 * @param int $questionId Identifier of the question used to generate input names.
	 * @param Parser $parser
	 */
	public function __construct( $beingCorrected, $caseSensitive, $questionId, Parser &$parser ) {
		global $wgQuizPageLatestRevID;

		parent::__construct( $beingCorrected, $caseSensitive, $questionId, $parser );

		#if $wgQuizPageLatestRevID is not set, this quiz page doesn't use the #quiztabulate: parser function, so it must have the
		#actual quiz stored on this page.
		if ( !$wgQuizPageLatestRevID ) {
			$wgQuizPageLatestRevID = $parser->getRevisionId();
		}
	}

	/**
	 * Convert a basic type object from quiz syntax to HTML.
	 *
	 * @global int $wgQuizPageLatestRevID
	 * @param string $input A question object in quiz syntax
	 * @param string $inputType
	 * @return string A question object in HTML.
	 */
	function basicTypeParseObject( $input, $inputType ) {
		global $wgQuizPageLatestRevID;

		$output = parent::basicTypeParseObject( $input, $inputType );
		$output .= "<input type=\"hidden\" name=\"quiz_page_id\" value=\"$wgQuizPageLatestRevID\" />";

		if ( !$this->mBeingCorrected ) {
			return $output;
		}

		#These lines were borrowed from Question::basicTypeParseObject()
		$raws = preg_split( '`\n`s', $input, -1, PREG_SPLIT_NO_EMPTY );

		$allAnswers = array();
		$rightAnswer = false;
		$answerChosen = '';
		foreach ( $raws as $proposalId => $raw ) {
			if ( preg_match( $this->mProposalPattern, $raw, $matches ) ) {
				array_shift( $matches );

				$sign = $matches[0];
				$answerText = $matches[1];
				#If the quiz was constructed having the same answer twice with two different signs, that's a user error anyway.
				#@todo use array syntax
				$allAnswers[$proposalId]['sign'] = $sign;
				$allAnswers[$proposalId]['answerText'] = $answerText;
				array_pop( $matches );
				# Determine a type ID, according to the questionType and the number of signes.
				$typeId = substr( $this->mType, 0, 1 );
				$typeId .= array_key_exists( 1, $matches ) ? 'c' : 'n';

				switch ( $typeId ) {
					#@todo extension only set up to track a linear right/wrong answer, which likely limits it to this type.
					case 'sn':
						$name = "q$this->mQuestionId";
						$value = "p$proposalId";
						break;
				}
				# Determine if the input had to be checked.
				$checked = ( $this->mRequest->getVal( $name ) == $value ) ? 'checked="checked"' : null;
				if ( $checked ) {
					$allAnswers[$proposalId]['checked'] = true;
				} else {
					$allAnswers[$proposalId]['checked'] = false;
				}
			}
		}
		$this->tabulate( $allAnswers );

		return $output;
	}

	/**
	 *  Save answers to the database.
	 *
	 * @global int $wgQuizPageLatestRevID
	 * @param array $allAnswers
	 */
	function tabulate( $allAnswers ) {
		global $wgQuizPageLatestRevID;
		$fullTableName = QUIZ_TABULATE_TABLE;

		foreach ( $allAnswers as $answerId => $proposal ) {
			#@todo extension does not enter answer into db until it is checked.
			#so not all answers may be shown when viewing quiz tabulation.
			if ( $proposal['checked'] == true ) {
				$dbr = wfGetDB( DB_REPLICA );
				if ( !$dbr->tableExists( $fullTableName ) ) {
					#@todo error message
					echo "$fullTableName table not found. Please run update.php!";
					return;
				}
				$existingProposalObject = $dbr->select(
					$fullTableName, array( 'count_attempt' ),
					"quiz_rev_id=$wgQuizPageLatestRevID AND question_id=$this->mQuestionId AND answer_id=$answerId",
					__METHOD__
				);
				$dbw = wfGetDB( DB_MASTER );
				if ( $existingProposalObject->numRows() !== 0 ) {
					$dbw->update(
						$fullTableName, array( 'count_attempt = count_attempt + 1' ),
						array( 'quiz_rev_id' => $wgQuizPageLatestRevID, 'question_id' => $this->mQuestionId, 'answer_id' => $answerId ),
						__METHOD__
					);
				} else {
					$dbw->insert(
						$fullTableName,
						array( 'quiz_rev_id' => $wgQuizPageLatestRevID, 'question_id' => $this->mQuestionId, 'answer_id' => $answerId,
						'answer_text' => $proposal['answerText'], 'count_attempt' => 1 ), __METHOD__
					);
				}
			}
		}
	}

	/**
	 * Convert the question's header into HTML.
	 * Store question in db
	 * @global int $wgQuizPageLatestRevID
	 * @param string $input The quiz header in quiz syntax.
	 * @return string
	 */
	function parseHeader( $input ) {
		global $wgQuizPageLatestRevID;

		$output = parent::parseHeader( $input );
		if ( $this->mBeingCorrected ) {
			$fullTableName = QUIZ_TABULATE_QUESTIONS_TABLE;
			$dbr = wfGetDB( DB_REPLICA );
			#check if question already exists
			$existingQuestion = $dbr->select(
				$fullTableName, array( 'question_id' ),
				"quiz_rev_id=$wgQuizPageLatestRevID AND question_id = $this->mQuestionId", __METHOD__
			);
			if ( $existingQuestion->numRows() == 0 ) {
				$dbw = wfGetDB( DB_MASTER );
				$dbw->insert(
					$fullTableName,
					array( 'quiz_rev_id' => $wgQuizPageLatestRevID, 'question_id' => $this->mQuestionId, 'question_text' => $output ),
					__METHOD__
				);
			}
		}

		return $output;
	}
}
