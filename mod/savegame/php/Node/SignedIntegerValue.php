<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

class SignedIntegerValue extends AbstractValueContent
{

    const MIN_VALUES = [
        0,
        - 127,
        - 32767,
        - 8388607,
        - 2147483647
    ];

    const MAX_VALUES = [
        0,
        127,
        32767,
        8388607,
        2147483647
    ];

    private $min;

    private $max;

    public  function getXmlTag(): string
    {
        return 'signed-integer';
    }

    public function getXmlAttributes(): string
    {
        return parent::getXmlAttributes() . $this->createXmlIntegerAttribute('min', $this->min) . $this->createXmlIntegerAttribute('max', $this->max);
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->min = (int) $strucElement->getAttribute('min', self::MIN_VALUES[$this->size]);
        $this->max = (int) $strucElement->getAttribute('max', self::MAX_VALUES[$this->size]);
    }

    protected function decodeValue(string $rawValue)
    {
        return $this->getConverter()->decodeSignedInteger($rawValue, $this->size);
    }

    protected function encodeValue($value): string
    {
        return $this->getConverter()->encodeSignedInteger($value, $this->size);
    }
}