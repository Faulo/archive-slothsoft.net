<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class BitValue extends AbstractValueContent
{
    private $bit;
    
    protected function getXmlAttributes() : string
    {
        $ret = parent::getXmlAttributes();
        $ret .= " bit='$this->bit'";
        return $ret;
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->bit = $this->loadIntegerAttribute('bit');
    }
    
    private function getBitValue() {
        return $this->getConverter()->pow2($this->bit);
    }

    public function setRawValue($value)
    {
        $this->rawValue = $value;
        $this->value = (bool) ($this->decodeValue() & $this->getBitValue());
    }

    public function setValue($value)
    {
        $this->value = (bool) $value;
        if ($this->value) {
            $this->rawValue |= $this->getBitValue();
        } else {
            $this->rawValue &= ~ $this->getBitValue();
        }
    }

    public function updateContent()
    {
        $this->getOwnerFile()->insertContentBit($this->valueOffset, $this->getBitValue(), $this->value);
    }

    protected function decodeValue()
    {
        return $this->getConverter()->decodeInteger($this->rawValue, $this->size);
    }

    protected function encodeValue()
    {
        return $this->getConverter()->encodeInteger($this->value, $this->size);
    }
}