<?php
namespace Slothsoft\Minecraft\NBT;

class TAGByte extends TAGNumber
{

    const TYPE = 1;

    public function loadPayload()
    {
        parent::loadPayload(1);
    }
} 

