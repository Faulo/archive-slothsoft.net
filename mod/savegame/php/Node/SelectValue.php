<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

class SelectValue extends AbstractValueContent
{

    protected $dictionaryRef;

    protected function getXmlTag(): string
    {
        return 'select';
    }

    public function getXmlAttributes(): string
    {
        return parent::getXmlAttributes() . $this->createXmlIdAttribute('dictionary-ref', $this->dictionaryRef);
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->dictionaryRef = (string) $strucElement->getAttribute('dictionary-ref');
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

