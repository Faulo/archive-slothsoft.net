<?php
namespace Slothsoft\Minecraft\NBT;

class TAGCompound extends TAGNode
{

    const TYPE = 10;

    public function loadPayload()
    {
        $offset = $this->getPayloadOffset();
        
        do {
            
            $NewChild = self::parseDocument($offset, true);
            
            $this->Payload['childs'][] = $NewChild;
            
            $offset += $NewChild->getLength();
        } while (! ($NewChild instanceof TAGEnd));
        
        $this->Payload['end'] = array_pop($this->Payload['childs']);
    }

    public function getLength()
    {
        $length = parent::getLength();
        
        $length += $this->Payload['end']->getLength();
        
        foreach ($this->Payload['childs'] as $Child) {
            
            $length += $Child->getLength();
        }
        
        return $length;
    }

    public function getValue()
    {}

    public $Payload = array(
        
        'childs' => array(),
        
        'end' => null
        
        // 'A sequential list of Named Tags. This array keeps going until a TAGEnd is found',
        
    // 'TAGEnd end'
    
    );
}
