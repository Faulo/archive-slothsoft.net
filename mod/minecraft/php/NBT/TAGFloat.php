<?php
namespace Slothsoft\Minecraft\NBT;

class TAGFloat extends TAGNumber
{

    const TYPE = 5;

    public function loadPayload()
    {
        parent::loadPayload(4, 1);
    }
} 

