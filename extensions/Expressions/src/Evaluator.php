<?php

namespace Expressions;

class Evaluator {
	/**
	 * @param Node $expression
	 * @return mixed
	 * @throws ExpressionException
	 */
	public static function evaluate( Node $expression ) {
		$operator = $expression->getOperator();
		$operands = $expression->getOperands();

		$value = $expression->getValue();

		if ( $operator === null ) {
			return $value;
		}

		switch ( $operator ) {
			case "T_CONJUNCTION":
				return self::evaluateConjunction( $operands[0], $operands[1] );
			case "T_DISJUNCTION":
				return self::evaluateDisjunction( $operands[0], $operands[1] );
			case "T_EQUALITY":
				return self::evaluateEquality( $operands[0], $operands[1] );
			case "T_NOT_EQUALITY":
				return self::evaluateNotEquality( $operands[0], $operands[1] );
			case "T_EQUALS":
				return self::evaluateEquals( $operands[0], $operands[1] );
			case "T_NOT_EQUALS":
				return self::evaluateNotEquals( $operands[0], $operands[1] );
			case "T_IMPLICATION":
				return self::evaluateImplication( $operands[0], $operands[1] );
			case "T_XOR":
				return self::evaluateXor( $operands[0], $operands[1] );
			case "T_GREATEREQUAL":
				return self::evaluateGreaterEqual( $operands[0], $operands[1], $value );
			case "T_LESSEQUAL":
				return self::evaluateLessEqual( $operands[0], $operands[1], $value );
			case "T_GREATER":
				return self::evaluateGreater( $operands[0], $operands[1], $value );
			case "T_LESS":
				return self::evaluateLess( $operands[0], $operands[1], $value );
			case "T_NOT":
				return self::evaluateNot( $operands[0], $value );
			case "T_MINUS":
				return self::evaluateMinus( $operands[0], $value );
		}

		// This will (in theory) never happen.
		throw new \MWException( "Invalid token type" );
	}

	/**
	 * @param Node $left
	 * @param Node $right
	 * @return bool
	 * @throws ExpressionException
	 */
	private static function evaluateConjunction( Node $left, Node $right ) {
		return self::evaluate( $left ) && self::evaluate( $right );
	}

	/**
	 * @param Node $left
	 * @param Node $right
	 * @return bool
	 * @throws ExpressionException
	 */
	private static function evaluateDisjunction( Node $left, Node $right ) {
		return self::evaluate( $left ) || self::evaluate( $right );
	}

	/**
	 * @param Node $left
	 * @param Node $right
	 * @return bool
	 * @throws ExpressionException
	 */
	private static function evaluateEquality( Node $left, Node $right ) {
		return self::evaluate( $left ) === self::evaluate( $right );
	}

	/**
	 * @param Node $left
	 * @param Node $right
	 * @return bool
	 * @throws ExpressionException
	 */
	private static function evaluateNotEquality( Node $left, Node $right ) {
		return self::evaluate( $left ) !== self::evaluate( $right );
	}

	/**
	 * @param Node $left
	 * @param Node $right
	 * @return bool
	 * @throws ExpressionException
	 */
	private static function evaluateEquals( Node $left, Node $right ) {
		return self::evaluate( $left ) == self::evaluate( $right );
	}

	/**
	 * @param Node $left
	 * @param Node $right
	 * @return bool
	 * @throws ExpressionException
	 */
	private static function evaluateNotEquals( Node $left, Node $right ) {
		return self::evaluate( $left ) != self::evaluate( $right );
	}

	/**
	 * @param Node $left
	 * @param Node $right
	 * @return bool
	 * @throws ExpressionException
	 */
	private static function evaluateImplication( Node $left, Node $right ) {
		return !self::evaluate( $left ) || self::evaluate( $right );
	}

	/**
	 * @param Node $left
	 * @param Node $right
	 * @return bool
	 * @throws ExpressionException
	 */
	private static function evaluateXor( Node $left, Node $right ) {
		return self::evaluate( $left ) xor self::evaluate( $right );
	}

	/**
	 * @param Node $left
	 * @param Node $right
	 * @param string $operator
	 * @return bool
	 * @throws ExpressionException
	 */
	private static function evaluateGreaterEqual( Node $left, Node $right, $operator ) {
		self::matchType( "double", "expressions-binary-left-type-exception", $operator, $left );
		self::matchType( "double", "expressions-binary-right-type-exception", $operator, $right );

		return self::evaluate( $left ) >= self::evaluate( $right );
	}

	/**
	 * @param Node $left
	 * @param Node $right
	 * @param string $operator
	 * @return bool
	 * @throws ExpressionException
	 */
	private static function evaluateLessEqual( Node $left, Node $right, $operator ) {
		self::matchType( "double", "expressions-binary-left-type-exception", $operator, $left );
		self::matchType( "double", "expressions-binary-right-type-exception", $operator, $right );

		return self::evaluate( $left ) <= self::evaluate( $right );
	}

	/**
	 * @param Node $left
	 * @param Node $right
	 * @param string $operator
	 * @return bool
	 * @throws ExpressionException
	 */
	private static function evaluateGreater( Node $left, Node $right, $operator ) {
		self::matchType( "double", "expressions-binary-left-type-exception", $operator, $left );
		self::matchType( "double", "expressions-binary-right-type-exception", $operator, $right );

		return self::evaluate( $left ) > self::evaluate( $right );
	}

	/**
	 * @param Node $left
	 * @param Node $right
	 * @param string $operator
	 * @return bool
	 * @throws ExpressionException
	 */
	private static function evaluateLess( Node $left, Node $right, $operator ) {
		self::matchType( "double", "expressions-binary-left-type-exception", $operator, $left );
		self::matchType( "double", "expressions-binary-right-type-exception", $operator, $right );

		return self::evaluate( $left ) < self::evaluate( $right );
	}

	/**
	 * @param Node $expression
	 * @param string $operator
	 * @return bool
	 * @throws ExpressionException
	 */
	private static function evaluateNot( Node $expression, $operator ) {
		self::matchType(
			"boolean",
			"expressions-unary-type-exception",
			$operator,
			$expression
		);

		return !self::evaluate( $expression );
	}

	/**
	 * @param Node $expression
	 * @param string $operator
	 * @return int
	 * @throws ExpressionException
	 */
	private static function evaluateMinus( Node $expression, $operator ) {
		self::matchType(
			"double",
			"expressions-unary-type-exception",
			$operator,
			$expression
		);

		return -self::evaluate( $expression );
	}

	/**
	 * @param string $expected_type
	 * @param string $errormsg
	 * @param string $operator
	 * @param Node $expression
	 * @throws ExpressionException
	 */
	private static function matchType( $expected_type, $errormsg, $operator, Node $expression ) {
		$actual_type = gettype( self::evaluate( $expression ) );

		if ( $actual_type !== $expected_type ) {
			throw new ExpressionException(
				"expressions-invalid-type-message",
				[],
				"expressions-invalid-type-submessage",
				[
					$expected_type,
					new ExceptionMessage( $errormsg, [ $operator, $expected_type ] ),
					$actual_type
				],
				"expressions-invalid-type-hint",
				[],
				$expression->getOffsetStart(),
				$expression->getOffsetEnd()
			);
		}
	}
}
