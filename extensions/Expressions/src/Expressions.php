<?php

namespace Expressions;

final class Expressions {
	public static $expression_string;

	/**
	 * @param \Parser $parser
	 */
	public static function onParserFirstCallInit( \Parser $parser ) {
		$parser->setFunctionHook( 'expression', [ self::class, 'evaluateExpression' ] );
	}

	/**
	 * @param \Parser $parser
	 * @param string $expression_string
	 * @param string $consequent
	 * @param string $alternate
	 * @return string
	 */
	public static function evaluateExpression(
		\Parser $parser,
		$expression_string = '',
		$consequent = '',
		$alternate = ''
	) {
		self::$expression_string = $expression_string;

		try {
			$parser = new Parser( self::$expression_string );

			$expression = $parser->parse();
			$expression = Evaluator::evaluate( $expression );

			return $expression ? $consequent : $alternate;
		} catch ( ExpressionException $exception ) {
			return self::parseException( $exception );
		}
	}

	/**
	 * @param ExpressionException $exception
	 * @return string
	 */
	private static function parseException( ExpressionException $exception ) {
		$message = wfMessage(
			$exception->getMessageName(),
			self::parseMessageParameters( $exception->getMessageParameters() )
		)->plain();

		$highlight_segment = self::highlightSegment(
			$exception->getOffsetStart(),
			$exception->getOffsetEnd() - $exception->getOffsetStart()
		);

		$submessage = wfMessage(
			$exception->getSubmessageName(),
			self::parseMessageParameters( $exception->getSubmessageParameters() )
		)->plain();

		$hint = wfMessage(
			$exception->getHintName(),
			self::parseMessageParameters( $exception->getHintParameters() )
		)->plain();

		return sprintf(
			"%s\n%s\n%s\n* %s",
			self::error( $message ),
			$highlight_segment,
			self::error( $submessage ),
			self::error( $hint )
		);
	}

	/**
	 * @param array $parameters
	 * @return array
	 */
	private static function parseMessageParameters( $parameters ) {
		return array_map( function ( $item ) {
			if ( $item instanceof ExceptionMessage ) {
				return wfMessage( $item->getErrorMessage(), $item->getParameters() );
			}

			return $item;
		}, $parameters );
	}

	/**
	 * @param string $message
	 * @param array $params
	 * @return string
	 */
	private static function error( $message, $params = [] ) {
		return \Html::rawElement(
				'span',
				[ 'class' => 'error' ],
				$message
			);
	}

	/**
	 * Highlights the given code segment at the given offset. Used for error reporting.
	 *
	 * @param int $offset
	 * @param int $token_length
	 * @return string
	 */
	private static function highlightSegment( $offset, $token_length ) {
		$max_expression_length = 60;

		$truncated = substr(
			self::$expression_string,
			max( 0, $offset - ( $max_expression_length / 2 ) ), $max_expression_length
		);

		if ( strlen( $truncated ) < strlen( self::$expression_string ) ) {
			$truncated .= "...";
		}

		if ( $offset > ( $max_expression_length / 2 ) ) {
			$truncated = "..." . $truncated;

			// +3 because we add three dots.
			$offset = $offset - ( $offset - ( $max_expression_length / 2 ) ) + 3;
		}

		if ( $offset + $token_length > strlen( $truncated ) ) {
			$token_length = strlen( $truncated ) - $offset;
		}

		return "<pre>1|&nbsp;" . str_replace( " ", "&nbsp;", htmlspecialchars( $truncated ) ) . "\n" .
			str_repeat( "&nbsp;", $offset + 3 ) .
			str_repeat( "^", $token_length ) .
			"</pre>";
	}
}
