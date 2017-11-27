<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;

declare(ticks = 1000);

class BitValue extends AbstractValueContent
{

    private $bit;
    
    protected function getXmlTag(): string
    {
        return 'bit';
    }
    protected function getXmlAttributes(): string
    {
        return parent::getXmlAttributes()
        . $this->createXmlIntegerAttribute('bit', $this->bit);
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->bit = (int) $strucElement->getAttribute('bit');
    }

    private function getBitValue()
    {
        return $this->getConverter()->pow2($this->bit);
    }

    public function setRawValue(string $rawValue)
    {
        $this->value = (bool) ($this->decodeValue($rawValue) & $this->getBitValue());
    }

    public function setValue($value)
    {
        $this->value = (bool) $value;
    }

    public function updateContent()
    {
        $this->ownerFile->insertContentBit(
            $this->contentOffset,
            $this->getBitValue(),
            $this->value
        );
    }

    protected function decodeValue(string $rawValue)
    {
        return $this->getConverter()->decodeInteger($rawValue, $this->size);
    }

    protected function encodeValue($value) : string
    {
        return $this->getConverter()->encodeInteger($value, $this->size);
    }
}