<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class SelectValue extends IntegerValue
{
    protected $dictionaryRef;

    public function getXmlAttributes() : string
    {
        $ret = parent::getXmlAttributes();
        if ($this->dictionaryRef !== '') {
            $ret .= " dictionary-ref='$this->dictionaryRef'";
        }
        return $ret;
    }
    
    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->dictionaryRef = $this->loadStringAttribute('dictionary-ref');
    }
}

