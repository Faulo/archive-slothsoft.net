<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class SignedIntegerValue extends AbstractValueContent
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
        
        if (! $this->strucData['min']) {
            $this->strucData['min'] = $this->getConverter()->pow256($this->strucData['size']) / - 2 + 1;
        }
        if (! $this->strucData['max']) {
            $this->strucData['max'] = $this->getConverter()->pow256($this->strucData['size']) / 2 - 1;
        }
    }

    protected function decodeValue()
    {
        return $this->getConverter()->decodeSignedInteger($this->rawValue, $this->strucData['size']);
    }

    protected function encodeValue()
    {
        return $this->getConverter()->encodeSignedInteger($this->strucData['value'], $this->strucData['size']);
    }
}
