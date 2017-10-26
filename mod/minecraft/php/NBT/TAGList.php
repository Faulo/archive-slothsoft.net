<?php
namespace Slothsoft\Minecraft\NBT;

class TAGList extends TAGNode {

	const TYPE = 9;

	public function loadPayload() {

		$offset = $this->getPayloadOffset();

		$this->Payload['tag_id'] = self::createNode(self::TYPE_BYTE, $offset);

		$tag_id = $this->Payload['tag_id']->getValue();

		$offset += $this->Payload['tag_id']->getLength();

		$this->Payload['length'] = self::createNode(self::TYPE_INT, $offset);

		$offset += $this->Payload['length']->getLength();

		for ($i = 0, $j = $this->Payload['length']->getValue(); $i < $j; $i++) {

			$NewChild = self::parseDocument($offset, false, $tag_id);

			$this->Payload['childs'][] = $NewChild;

			$offset += $NewChild->getLength();

		}

	}

	public function getLength() {

		$length = parent::getLength();

		$length += $this->Payload['tag_id']->getLength();

		$length += $this->Payload['length']->getLength();

		foreach ($this->Payload['childs'] as $Child) {

			$length += $Child->getLength();

		}

		return $length;

	}

	public function getValue() {

	}

	public $Payload = array(

		'tag_id' => null,

		'length' => null,

		'childs' => array()

		//'A sequential list of Tags (not Named Tags), of type <typeId>. The length of this array is <length> Tags'

	);

}
