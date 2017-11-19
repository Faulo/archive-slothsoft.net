<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class EventInstruction extends AbstractInstructionContent
{

    public function __construct()
    {
        parent::__construct();
        $this->strucData['size'] = 0;
        $this->strucData['step-size'] = 0;
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->strucData['size'] = $this->getParser()->evaluate($this->strucData['size'], $this->ownerFile);
        $this->strucData['step-size'] = $this->getParser()->evaluate($this->strucData['step-size'], $this->ownerFile);
    }

    protected function loadInstruction()
    {
        $this->instructionList = [];
        
        for ($i = 0; $i < $this->strucData['size']; $i += $this->strucData['step-size']) {
            $strucData = [];
            $strucData['position'] = $i;
            $strucData['size'] = $this->strucData['step-size'];
            
            $this->instructionList[] = $this->getStrucElement()->clone('event-step', $strucData);
        }
    }
}
