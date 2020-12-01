<?php
namespace PhpTagsStorage;

class Field {

	const PREFIX = 'field_';

	private $name;
	private $type;
	private $templateID;
	private $number;

	public function __construct( $templateID, $name, $type, $number ) {
		$this->templateID = $templateID;
		$this->name = $name;
		$this->type = $type;
		$this->number = $number;
	}

	public static function newFromUserString( $templateID, $name, $typeString, $number ) {
		switch ( strtolower( $typeString ) ) {
			case 'int':
			case 'integer':
				$type = self::T_INTEGER;
				break;
			case 'text':
			case 'string':
				$type = self::T_TEXT;
				break;
			default:
				throw new \PhpTags\HookException( "unknown colum type: $typeString" );
		}
		return new self( $templateID, $name, $type, $number );
	}

	const T_INTEGER = 'INTEGER';
	const T_TEXT = 'TEXT';

	public static function newFromDB( $templateID, $name, $info ) {
		return new self( $templateID, $name, $info['type'], $info['number'] );
	}

	public function toCreateSQL() {
		//global $wgDBtype;
		$type = $this->type;
		$name = self::PREFIX . $this->number;
		return ", $name $type NULL DEFAULT NULL";
	}

	public function toSchema() {
		return array(
			'type' => $this->type,
			'number' => $this->number,
		);
	}

	public function getName() {
		return $this->name;
	}

	public function getNumber() {
		return $this->number;
	}

	public function getDBName() {
		return self::PREFIX . $this->number;
	}

}