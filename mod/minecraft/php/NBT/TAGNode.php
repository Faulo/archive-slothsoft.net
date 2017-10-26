<?php
namespace Slothsoft\Minecraft\NBT;

use DOMDocument;

abstract class TAGNode {
	const TYPE_END 			= 0;
	const TYPE_BYTE 		= 1;
	const TYPE_SHORT 		= 2;
	const TYPE_INT 			= 3;
	const TYPE_LONG 		= 4;
	const TYPE_FLOAT 		= 5;
	const TYPE_DOUBLE 		= 6;
	const TYPE_BYTEARRAY 	= 7;
	const TYPE_STRING 		= 8;
	const TYPE_LIST 		= 9;
	const TYPE_COMPOUND 	= 10;
	
	public static $document = '';
	public static $Root;
	public static $tagNames = array(
		0 => 'end',
		1 => 'byte',
		2 => 'short',
		3 => 'int',
		4 => 'long',
		5 => 'float',
		6 => 'double',
		7 => 'byteArray',
		8 => 'string',
		9 => 'list',
		10 => 'compound'
	);
	public static function createDocument($string) {
		@$try = gzinflate(substr($string, 10, -8));
		if (is_string($try)) {
			$string = $try;
		}
		self::$document = $string;
		self::$Root = self::parseDocument();
		return self::$Root;
	}

	public static function parseDocument($offset = 0, $namedTag = true, $tagType = null) {
		$offset = (int) $offset;	
		if ($tagType === null) {
			$tagType = self::getInteger($offset, 1);
			$exlicitTagType = true;
		} else {
			$exlicitTagType = false;
		}
		if ($tagType === self::TYPE_END)
			$namedTag = false;
		$Node = self::createNode($tagType, $offset, $namedTag, $exlicitTagType);
		return $Node;
	}

	public static function createNode($tagType, $offset, $namedTag = false, $exlicitTagType = false) {
		if ($node = self::instantiateNode($tagType)) {
			$node->init($offset, $namedTag, $exlicitTagType);
			return $node;
		}		
	}
	protected static function instantiateNode($tagType) {
		switch ($tagType) {
			case self::TYPE_END:		return new TAGEnd($tagType);
			case self::TYPE_BYTE:		return new TAGByte($tagType);
			case self::TYPE_SHORT:		return new TAGShort($tagType);
			case self::TYPE_INT:		return new TAGInt($tagType);
			case self::TYPE_LONG:		return new TAGLong($tagType);
			case self::TYPE_FLOAT:		return new TAGFloat($tagType);
			case self::TYPE_DOUBLE:		return new TAGDouble($tagType);
			case self::TYPE_BYTEARRAY:	return new TAGByteArray($tagType);
			case self::TYPE_STRING:		return new TAGString($tagType);
			case self::TYPE_LIST:		return new TAGList($tagType);
			case self::TYPE_COMPOUND:	return new TAGCompound($tagType);
		}
		return null;
	}

	public static function getFloat($offset, $length) {

		$bin = self::getBinary($offset, $length);		

		$str = '';

		for ($i = 0; $i < $length; $i++) {

			$str .= bin2hex($bin[$i]);

		}

		

		$int = 0;

		switch ($length) {

			case 8:	//64b

				$b = '';

				for ($i = 0; $i < 16; $i++) {

					$j = hexdec($str[$i]);

					for ($k = 3; $k >= 0; $k--) {

						$b .= $j & pow(2, $k) ? '1' : '0';

					}

				}

				if ($str == '4052000000000000') {

					//die($b . '<br />' . strlen($b));

				}

				$sign = $b[0] ? -1 : 1;

				//var_dump(substr($b, 1, 11));

				$exp = bindec(substr($b, 1, 11)) - 1023;

				$man = 1.0;

				$m = substr($b, 12);

				for ($i = 0, $j = strlen($m); $i < $j; $i++) {

					if ($m[$i])

						$man += pow(2, - $i - 1);

				}

				//$man = floatval('1.' . ));

				//var_dump(substr($b, 12));

				$int = $sign * $man * pow(2, $exp);

				break;

			case 4:	//32b

				$v = hexdec($str);

				$x = ($v & ((1 << 23) - 1)) + (1 << 23) * ($v >> 31 | 1);

				$exp = ($v >> 23 & 0xFF) - 127;

				$int = $x * pow(2, $exp - 23);

				break;

		}

		return $int;

	}

	public static function getInteger($offset, $length) {

		return hexdec(bin2hex(self::getBinary($offset, $length)));

	}

	public static function getBinary($offset, $length) {

		return substr(self::$document, $offset, $length);

	}

	public static function TAG2DOM(DOMDocument $Doc, TAGNode $Tag) {

		$tag = self::$tagNames[$Tag->type];
		//my_dump($tag);
		$Node = $Doc->createElement($tag);

		if ($Tag->Name) {

			$Node->setAttribute('key', $Tag->Name->getValue());

		}

		if (isset($Tag->Payload['childs'])) {

			foreach ($Tag->Payload['childs'] as $val) {

				$Node->appendChild(self::TAG2DOM($Doc, $val));

			}

		}

		if (strlen($val = utf8_encode($Tag->getValue()))) {

			$Node->setAttribute('val', $val);

		}

		return $Node;

	}
	protected $type;
	public function __construct($tagType) {
		$this->type = $tagType;
	}
	public function init($offset = null, $namedTag = false, $exlicitTagType = false) {

		if ($offset !== null) {

			$this->offset = (int) $offset;

			$this->exlicitTagType = $exlicitTagType;

			if ((bool) $namedTag) {

				$this->loadName();

			}

			$this->loadPayload();

		}

	}

	public $offset = 0;

	public $exlicitTagType = false;

	public $Name = null;	

	public $Payload = array();

	public function loadName() {

		$this->Name = self::createNode(self::TYPE_STRING, $this->offset + 1);

	}

	public function getPayloadOffset() {

		$offset = $this->offset + (int) $this->exlicitTagType;

		if ($this->Name) {

			$offset += $this->Name->getLength();

		}

		return $offset;

	}

	public abstract function loadPayload();

	public function getLength() {

		$length = (int) $this->exlicitTagType;

		if ($this->Name) {

			$length += $this->Name->getLength();

		}

		return $length;

	}

	public abstract function getValue();
	
	public function getElementsByName($name) {
		$ret = array();
		if ($this->Name.'' === $name) {
			$ret[] = $this;
		}
		if (isset($this->Payload['childs'])) {
			foreach ($this->Payload['childs'] as $tagNode) {
				$ret = array_merge($ret, $tagNode->getElementsByName($name));
			}
		}
		return $ret;
	}

}


