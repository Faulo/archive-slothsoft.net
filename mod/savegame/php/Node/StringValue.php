<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

class StringValue extends AbstractValueContent
{

    private $encoding;

    protected function getXmlTag(): string
    {
        return 'string';
    }

    public function getXmlAttributes(): string
    {
        return parent::getXmlAttributes() . $this->createXmlIdAttribute('encoding', $this->encoding);
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->encoding = (string) $strucElement->getAttribute('encoding');
    }

    protected function decodeValue(string $rawValue)
    {
        return $this->getConverter()->decodeString($rawValue, $this->size, $this->encoding);
    }

    protected function encodeValue($value): string
    {
        return $this->getConverter()->encodeString($value, $this->size, $this->encoding);
    }
}
