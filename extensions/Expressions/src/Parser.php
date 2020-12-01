<?php

namespace Expressions;

class Parser {
	const VALUE_HINT_TOKEN_TYPES = [ // phpcs:ignore
		"T_STRING",
		"T_NUMBER",
		"T_FALSE",
		"T_TRUE",
		"T_LEFTPAREN"
	];

	/**
	 * @var array
	 */
	private $tokens;

	/**
	 * @var int
	 */
	private $current = 0;

	/**
	 * Parser constructor.
	 * @param string $expression_string
	 * @throws ExpressionException
	 */
	public function __construct( $expression_string ) {
		$this->tokens = Lexer::lex( $expression_string );
	}

	/**
	 * @return Node
	 * @throws ExpressionException
	 */
	public function parse() {
		if ( $this->atEnd() ) {
			return new Node( null, null, null, null, null );
		}

		$ast = $this->expression();

		if ( $this->current < count( $this->tokens ) ) {
			$this->throwUnexpectedTokenError( $this->current() );
		}

		return $ast;
	}

	/**
	 * This is the parse() method without the initial empty-case check.
	 *
	 * @return Node
	 * @throws ExpressionException
	 */
	private function expression() {
		return $this->equality();
	}

	/**
	 * @return Node
	 * @throws ExpressionException
	 */
	private function equality() {
		$offset_start = $this->current()->getOffset();
		$expression = $this->implication();

		while ( $this->matchesToken( "T_NOT_EQUALS", "T_NOT_EQUALITY", "T_EQUALS", "T_EQUALITY" ) ) {
			$operator = $this->previous();
			$right = $this->implication();

			$expression = new Node(
				$operator->getTokenType(),
				[ $expression, $right ],
				$operator->getMatch(),
				$offset_start,
				$right->getOffsetEnd()
			);
		}

		return $expression;
	}

	/**
	 * @return Node
	 * @throws ExpressionException
	 */
	private function implication() {
		$offset_start = $this->current()->getOffset();
		$expression = $this->disjunction();

		while ( $this->matchesToken( "T_IMPLICATION" ) ) {
			$operator = $this->previous();
			$right = $this->disjunction();

			$expression = new Node(
				$operator->getTokenType(),
				[ $expression, $right ],
				$operator->getMatch(),
				$offset_start,
				$right->getOffsetEnd()
			);
		}

		return $expression;
	}

	/**
	 * @return Node
	 * @throws ExpressionException
	 */
	private function disjunction() {
		$offset_start = $this->current()->getOffset();
		$expression = $this->conjunction();

		while ( $this->matchesToken( "T_DISJUNCTION" ) ) {
			$operator = $this->previous();
			$right = $this->conjunction();

			$expression = new Node(
				$operator->getTokenType(),
				[ $expression, $right ],
				$operator->getMatch(),
				$offset_start,
				$right->getOffsetEnd()
			);
		}

		return $expression;
	}

	/**
	 * @return Node
	 * @throws ExpressionException
	 */
	private function conjunction() {
		$offset_start = $this->current()->getOffset();
		$expression = $this->logicalXor();

		while ( $this->matchesToken( "T_CONJUNCTION" ) ) {
			$operator = $this->previous();
			$right = $this->logicalXor();

			$expression = new Node(
				$operator->getTokenType(),
				[ $expression, $right ],
				$operator->getMatch(),
				$offset_start,
				$right->getOffsetEnd()
			);
		}

		return $expression;
	}

	/**
	 * @return Node
	 * @throws ExpressionException
	 */
	private function logicalXor() {
		$offset_start = $this->current()->getOffset();
		$expression = $this->comparison();

		while ( $this->matchesToken( "T_XOR" ) ) {
			$operator = $this->previous();
			$right = $this->comparison();

			$expression = new Node(
				$operator->getTokenType(),
				[ $expression, $right ],
				$operator->getMatch(),
				$offset_start,
				$right->getOffsetEnd()
			);
		}

		return $expression;
	}

	/**
	 * @return Node
	 * @throws ExpressionException
	 */
	private function comparison() {
		$offset_start = $this->current()->getOffset();
		$expression = $this->unary();

		while ( $this->matchesToken( "T_GREATER", "T_LESS", "T_GREATEREQUAL", "T_LESSEQUAL" ) ) {
			$operator = $this->previous();
			$right = $this->unary();

			$expression = new Node(
				$operator->getTokenType(),
				[ $expression, $right ],
				$operator->getMatch(),
				$offset_start,
				$right->getOffsetEnd()
			);
		}

		return $expression;
	}

	/**
	 * @return Node
	 * @throws ExpressionException
	 */
	private function unary() {
		if ( $this->matchesToken( "T_NOT", "T_MINUS" ) ) {
			$operator = $this->previous();
			$right = $this->unary();

			return new Node(
				$operator->getTokenType(),
				[ $right ],
				$operator->getMatch(),
				$operator->getOffset(),
				$right->getOffsetEnd()
			);
		}

		return $this->primary();
	}

	/**
	 * Returns true and advances one token if the current token is any of the given token types.
	 *
	 * @param string ...$tokens
	 * @return bool
	 * @throws ExpressionException
	 */
	private function matchesToken( ...$tokens ) {
		foreach ( $tokens as $token ) {
			if ( $this->check( $token ) ) {
				$this->advance();
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the given token type is equal to the type of the current token.
	 *
	 * @param string $token_type
	 * @return bool
	 * @throws ExpressionException
	 */
	private function check( $token_type ) {
		if ( $this->atEnd() ) {
			return false;
		}

		return $this->current()->getTokenType() === $token_type;
	}

	/**
	 * Returns true if we ran out of tokens.
	 *
	 * @return bool
	 */
	private function atEnd() {
		return $this->current >= count( $this->tokens );
	}

	/**
	 * Consumes the current token and returns it.
	 *
	 * @return Token
	 * @throws ExpressionException
	 */
	private function advance() {
		if ( !$this->atEnd() ) {
			$this->current++;
		}
		return $this->previous();
	}

	/**
	 * @return Token
	 * @throws ExpressionException
	 */
	private function current() {
		if ( $this->atEnd() ) {
			$this->throwUnexpectedTokenError( $this->previous(), "expressions-after" );
		}
		return $this->tokens[$this->current];
	}

	/**
	 * @return Token
	 * @throws ExpressionException
	 */
	private function previous() {
		if ( $this->current === 0 ) {
			$this->throwUnexpectedTokenError( $this->current(), "expressions-before" );
		}
		return $this->tokens[$this->current - 1];
	}

	/**
	 * @return Node
	 * @throws ExpressionException
	 */
	private function primary() {
		if ( $this->matchesToken( "T_FALSE" ) ) {
			return new Node(
				null,
				null,
				false,
				$this->previous()->getOffset(),
				$this->previous()->getOffset() + 5
			);
		}

		if ( $this->matchesToken( "T_TRUE" ) ) {
			return new Node(
				null,
				null,
				true,
				$this->previous()->getOffset(),
				$this->previous()->getOffset() + 4
			);
		}

		if ( $this->matchesToken( "T_NUMBER" ) ) {
			return new Node(
				null,
				null,
				floatval( $this->previous()->getMatch() ),
				$this->previous()->getOffset(),
				$this->previous()->getOffset() + strlen( (string)$this->previous()->getMatch() )
			);
		}

		if ( $this->matchesToken( "T_STRING" ) ) {
			return new Node(
				null,
				null,
				substr( $this->previous()->getMatch(), 1, -1 ),
				$this->previous()->getOffset(),
				$this->previous()->getOffset() + strlen( $this->previous()->getMatch() )
			);
		}

		if ( $this->matchesToken( "T_LEFTPAREN" ) ) {
			$start_offset = $this->previous()->getOffset();
			$expression = $this->expression();
			$this->consumeRightParenthesis();

			$end_offset = $this->previous()->getOffset() + 1;

			$expression->setOffsetStart( $start_offset );
			$expression->setOffsetEnd( $end_offset );

			return $expression;
		}

		$this->throwUnexpectedTokenError( $this->current(), "expressions-before" );

		return null;
	}

	/**
	 * @return Token
	 * @throws ExpressionException
	 */
	private function consumeRightParenthesis() {
		if ( $this->check( "T_RIGHTPAREN" ) ) {
			return $this->advance();
		}

		if ( !$this->atEnd() ) {
			$this->throwUnexpectedTokenError( $this->current() );
		}

		// Find the offset of the last unclosed parenthesis.
		$idx_current = strlen( Expressions::$expression_string );
		$open_parenthesis = 1;

		while ( $open_parenthesis > 0 && $idx_current > 0 ) {
			$character = Expressions::$expression_string[--$idx_current];

			if ( $character === '(' ) {
				$open_parenthesis--;
			}
			if ( $character === ')' ) {
				$open_parenthesis++;
			}
		}

		throw new ExpressionException(
			"expressions-unclosed-paren-message",
			[],
			"expressions-empty-string",
			[],
			"expressions-unclosed-paren-hint",
			[],
			$idx_current,
			$idx_current + 1
		);
	}

	/**
	 * @param Token $token
	 * @param string|null $location_hintmsg
	 * @throws ExpressionException
	 */
	private function throwUnexpectedTokenError( Token $token, $location_hintmsg = null ) {
		if ( $token->getTokenType() === "T_RIGHTPAREN" ) {
			$expected = new ExceptionMessage( "expressions-expected-value" );

			throw new ExpressionException(
				"expressions-unexpected-token-message",
				[],
				"expressions-unexpected-token-submessage",
				[ $expected, $token->getMatch() ],
				"expressions-unexpected-token-rightparen-hint",
				[],
				$token->getOffset(),
				$token->getOffset() + strlen( $token->getMatch() )
			);
		}

		if ( $location_hintmsg === "expressions-before" ) {
			$expected = "expressions-expected-value-before";
		} else {
			$expected = "expressions-expected-value-after";
		}

		if ( in_array( $token->getTokenType(), self::VALUE_HINT_TOKEN_TYPES ) ) {
			if ( $location_hintmsg !== null ) {
				$location_hint = new ExceptionMessage( $location_hintmsg );
			} else {
				$location_hint = new ExceptionMessage( "expressions-before" );
			}

			$hint = "expressions-unexpected-token-value-hint";
		} else {
			if ( $location_hintmsg !== null ) {
				$location_hint = new ExceptionMessage( $location_hintmsg );
			} else {
				$location_hint = new ExceptionMessage( "expressions-before" );
			}

			$hint = "expressions-unexpected-token-operator-hint";
		}

		throw new ExpressionException(
			"expressions-unexpected-token-message",
			[],
			"expressions-unexpected-token-submessage",
			[ $token->getMatch(), new ExceptionMessage( $expected ) ],
			$hint,
			[ $location_hint ],
			$token->getOffset(),
			$token->getOffset() + strlen( $token->getMatch() )
		);
	}
}
