<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class IntegerValue extends AbstractValueContent
{

    public function __construct()
    {
        parent::__construct();
        $this->strucData['min'] = 0;
        $this->strucData['max'] = 0;
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->strucData['min'] = $this->getParser()->evaluate($this->strucData['min'], $this->ownerFile);
        $this->strucData['max'] = $this->getParser()->evaluate($this->strucData['max'], $this->ownerFile);
        
        if (! $this->strucData['max']) {
            $this->strucData['max'] = $this->getConverter()->pow256($this->strucData['size']);
        }
    }

    protected function decodeValue()
    {
        return $this->getConverter()->decodeInteger($this->rawValue, $this->strucData['size']);
    }

    protected function encodeValue()
    {
        return $this->getConverter()->encodeInteger($this->strucData['value'], $this->strucData['size']);
    }
}
