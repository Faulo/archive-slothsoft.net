<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class IntegerValue extends AbstractValueContent
{

    public function __construct()
    {
        parent::__construct();
        $this->strucData['min'] = '0';
        $this->strucData['max'] = '0';
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        $this->strucData['min'] = $this->parseInt($this->strucData['min']);
        $this->strucData['max'] = $this->parseInt($this->strucData['max']);
        
        if (! $this->strucData['max']) {
            $this->strucData['max'] = pow(256, $this->strucData['size']);
        }
    }

    protected function decodeValue()
    {
        return $this->converter->decodeInteger($this->rawValue, $this->strucData['size']);
    }

    protected function encodeValue()
    {
        return $this->converter->encodeInteger($this->strucData['value'], $this->strucData['size']);
    }
}
