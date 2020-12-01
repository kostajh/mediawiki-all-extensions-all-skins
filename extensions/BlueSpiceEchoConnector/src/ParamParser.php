<?php

namespace BlueSpice\EchoConnector;

/**
 * This class deals with how params are displayed in the
 * final notification/email displayed to the user
 */
class ParamParser implements IParamParser {

	protected $event;
	protected $message;
	protected $distributionType;

	protected $paramParserRegistry;
	protected $foreignParsers = [];

	/**
	 *
	 * @param \EchoEvent $event
	 * @param string $distributionType
	 */
	public function __construct( \EchoEvent $event, $distributionType = 'web' ) {
		$this->event = $event;

		// Probably unnecessary, but maybe some parsers would use it
		$this->distributionType = $distributionType;

		$this->getParamParserRegistry();
	}

	/**
	 * Sets the type of output for this message (email/web)
	 * @param string $type
	 */
	public function setDistributionType( $type ) {
		$this->distributionType = $type;
	}

	/**
	 * Parses and sets value for given param to given \Message object
	 * First, tries to parse the param with foreign parser (registered by another
	 * extension), if that fails parses params supported by this class, if no-one is
	 * responsible for parsing the param, it sets it as-it, without parsing
	 *
	 * @param \Message $message
	 * @param string $param
	 */
	public function parseParam( \Message $message, $param ) {
		$this->message = $message;

		if ( $this->parseWithForeignParser( $param ) ) {
			return;
		}

		switch ( $param ) {
			case 'title':
				$this->parseTitle();
				break;
			case 'agent':
				$this->parseAgent();
				break;
			case 'oldtitle':
				$this->parseOldTitle();
				break;
			case 'user':
			case 'username':
				$this->parseUserName();
				break;
			default:
				// Just display the param value as-is
				$extra = $this->event->getExtra();
				if ( isset( $extra[$param] ) ) {
					$value = $extra[$param];
				} else {
					$value = '';
				}

				$this->message->params( $value );
		}
	}

	protected function parseTitle() {
		$title = $this->event->getTitle();
		if ( $title instanceof \Title ) {
			return $this->message->params( $title->getPrefixedText() );
		}

		// Check if there is title in extra params
		$extra = $this->event->getExtra();
		if ( isset( $extra['title'] ) ) {
			$title = $extra['title'];
			if ( $title instanceof \Title ) {
				$this->message->params( $title->getPrefixedText() );
			}
		}
	}

	protected function parseAgent() {
		$agent = $this->event->getAgent();
		if ( $agent instanceof \User ) {
			$this->message->params( $agent->getName() );
		}
	}

	protected function parseOldTitle() {
		if ( isset( $this->event->getExtra()['oldtitle'] ) ) {
			$oldTitle = $this->event->getExtra()['oldtitle'];
			if ( $oldTitle instanceof \Title ) {
				$this->message->params( $oldTitle->getPrefixedText() );
			}
		}
	}

	protected function parseUserName() {
		if ( isset( $this->event->getExtra()['user'] ) ) {
			$user = $this->event->getExtra()['user'];
			if ( $user instanceof \User ) {
				$this->message->params( $user->getName() );
			}
		}
	}

	/**
	 * This attribute exists so that extensions could add
	 * their params, and ways to parse them without implementing
	 * full-blown PresentationModel
	 */
	protected function getParamParserRegistry() {
		$this->paramParserRegistry = new ParamParserRegistry();
	}

	/**
	 * Tries to parse given param with another parser
	 *
	 * @param string $param
	 * @return bool if param cannot be parsed
	 */
	protected function parseWithForeignParser( $param ) {
		// If param is registered with another extension
		// let it do the parsing
		$parser = $this->getForeignParserForParam( $param );
		if ( $parser == null ) {
			return false;
		}

		$parser = $this;
		$parser->parseParam( $this->message, $param, $parser );
		return true;
	}

	/**
	 * If not already instantiated, instantiates \IParamParser for given
	 * param and returns it
	 *
	 * @param string $param
	 * @return null|\IParamParser
	 */
	protected function getForeignParserForParam( $param ) {
		if ( !isset( $this->foreignParsers[$param] ) ) {
			$parserClass = $this->paramParserRegistry->getValue( $param );
			if ( !$parserClass || !class_exists( $parserClass ) ) {
				return null;
			}

			if ( in_array( IParamParser::class, class_implements( $parserClass ) ) ) {
				$this->foreignParsers[$param] = new $parserClass( $this->event, $this->distributionType );
			} else {
				return null;
			}
		}

		return $this->foreignParsers[$param];
	}

}
