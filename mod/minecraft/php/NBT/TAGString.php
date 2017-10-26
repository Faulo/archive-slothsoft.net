<?php
namespace Slothsoft\Minecraft\NBT;
class TAGString extends TAGNode {

	const TYPE = 8;

	public function loadPayload() {

		$offset = $this->getPayloadOffset();

		$this->Payload['length'] = self::createNode(self::TYPE_SHORT, $offset);

		$offset += $this->Payload['length']->getLength();

		$this->Payload['string'] = self::getBinary($offset, $this->Payload['length']->getValue());

		//var_dump($this->Payload['string']);

	}

	public function getLength() {

		$length = parent::getLength();

		$length += $this->Payload['length']->getLength();

		$length += $this->Payload['length']->getValue();

		return $length;

	}

	public function getValue() {

		return $this->Payload['string'];

	}

	public $Payload = array(

		'length' => null,

		'string' => ''

	);
	
	public function __toString() {
		return $this->Payload['string'];
	}

} 

