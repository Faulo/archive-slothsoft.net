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
        
        $this->strucData['min'] = $this->parseInt($this->strucData['min']);
        $this->strucData['max'] = $this->parseInt($this->strucData['max']);
        
        if (! $this->strucData['min']) {
            $this->strucData['min'] = $this->converter->pow256($this->strucData['size']) / - 2 + 1;
        }
        if (! $this->strucData['max']) {
            $this->strucData['max'] = $this->converter->pow256($this->strucData['size']) / 2 - 1;
        }
    }

    protected function decodeValue()
    {
        return $this->converter->decodeSignedInteger($this->rawValue, $this->strucData['size']);
    }

    protected function encodeValue()
    {
        return $this->converter->encodeSignedInteger($this->strucData['value'], $this->strucData['size']);
    }
}
