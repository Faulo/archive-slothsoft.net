<?php
namespace Slothsoft\Minecraft\NBT;

class TAGEnd extends TAGNode
{

    const TYPE = 0;

    public function loadPayload()
    {}

    public function getLength()
    {
        return parent::getLength();
    }

    public function getValue()
    {
        return null;
    }

    public $Payload = array(
    );
}

