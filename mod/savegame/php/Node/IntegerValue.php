<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

class IntegerValue extends AbstractValueContent
{

    const MAX_VALUES = [
        0,
        256,
        65536,
        16777216,
        4294967296
    ];

    private $min;

    private $max;

    protected function getXmlTag(): string
    {
        return 'integer';
    }

    public function getXmlAttributes(): string
    {
        return parent::getXmlAttributes() . $this->createXmlIntegerAttribute('min', $this->min) . $this->createXmlIntegerAttribute('max', $this->max);
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->min = (int) $strucElement->getAttribute('min');
        $this->max = (int) $strucElement->getAttribute('max', self::MAX_VALUES[$this->size]);
    }

    protected function decodeValue(string $rawValue)
    {
        return $this->getConverter()->decodeInteger($rawValue, $this->size);
    }

    protected function encodeValue($value): string
    {
        return $this->getConverter()->encodeInteger($value, $this->size);
    }
}
