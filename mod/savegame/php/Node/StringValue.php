<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class StringValue extends AbstractValueContent
{
    private $encoding;
    
    public function getXmlAttributes() : string
    {
        $ret = parent::getXmlAttributes();
        if ($this->encoding !== '') {
            $ret .= " encoding='$this->encoding'";
        }
        return $ret;
    }
    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->encoding = $this->loadStringAttribute('encoding');
    }

    protected function decodeValue()
    {
        return $this->getConverter()->decodeString($this->rawValue, $this->size, $this->encoding);
    }

    protected function encodeValue()
    {
        return $this->getConverter()->encodeString($this->value, $this->size, $this->encoding);
    }
}
