<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class BitValue extends AbstractValueContent
{

    public function __construct()
    {
        parent::__construct();
        $this->strucData['size'] = 1;
        $this->strucData['bit'] = 0;
        $this->strucData['bit-value'] = 0;
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->strucData['bit'] = $this->parseInt($this->strucData['bit']);
        $this->strucData['size'] = $this->parseInt($this->strucData['size']);
        $this->strucData['bit-value'] = $this->converter->pow2($this->strucData['bit']);
    }

    public function setRawValue($value)
    {
        $this->rawValue = $value;
        $this->strucData['value'] = (bool) ($this->decodeValue() & $this->strucData['bit-value']);
    }

    public function setValue($value)
    {
        $this->strucData['value'] = (bool) $value;
        if ($this->strucData['value']) {
            $this->rawValue |= $this->strucData['bit-value'];
        } else {
            $this->rawValue &= ~ $this->strucData['bit-value'];
        }
    }

    public function updateContent()
    {
        $this->ownerFile->insertContentBit($this->valueOffset, $this->strucData['bit-value'], $this->strucData['value']);
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