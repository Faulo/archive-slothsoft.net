<?php
namespace Slothsoft\Minecraft\NBT;

class TAGByteArray extends TAGNode {

	const TYPE = 7;

	public function loadPayload() {

		$offset = $this->getPayloadOffset();

		$this->Payload['length'] = self::createNode(self::TYPE_INT, $offset);

		$offset += $this->Payload['length']->getLength();

		$this->Payload['bytes'] = self::getBinary($offset, $this->Payload['length']->getValue());

	}

	public function getLength() {

		$length = parent::getLength();

		$length += $this->Payload['length']->getLength();

		$length += $this->Payload['length']->getValue();

		return $length;

	}

	public function getValue() {

		return $this->Payload['bytes'];

	}

	public $Payload = array(

		'length' => null,

		'bytes' => ''

	);

} 

