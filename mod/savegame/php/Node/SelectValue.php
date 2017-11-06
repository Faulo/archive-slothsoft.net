<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class SelectValue extends IntegerValue
{
    public function __construct()
    {
        parent::__construct();
        $this->strucData['dictionary-ref'] = '';
    }
}

