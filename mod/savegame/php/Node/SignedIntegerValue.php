<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class SignedIntegerValue extends AbstractValueContent
{
    private $min;
    private $max;
    
    public function getXmlAttributes() : string
    {
        $ret = parent::getXmlAttributes();
        $ret .= " min='$this->min' max='$this->max'";
        return $ret;
    }
    
    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->min = $this->loadStringAttribute('min', $this->getConverter()->pow256($this->size) / - 2 + 1);
        $this->max = $this->loadStringAttribute('max', $this->getConverter()->pow256($this->size) / 2 - 1);
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