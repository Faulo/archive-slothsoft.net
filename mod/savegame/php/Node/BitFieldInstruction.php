<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class BitFieldInstruction extends AbstractInstructionContent
{

    public function __construct()
    {
        parent::__construct();
        $this->strucData['first-bit'] = 0;
        $this->strucData['last-bit'] = 0;
        $this->strucData['size'] = 1;
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->strucData['first-bit'] = $this->parser->evaluate($this->strucData['first-bit'], $this->ownerFile);
        $this->strucData['last-bit'] = $this->parser->evaluate($this->strucData['last-bit'], $this->ownerFile);
        
        if (! $this->strucData['last-bit']) {
            $this->strucData['last-bit'] = $this->strucData['size'] * 8 - 1;
        }
    }

    protected function loadInstruction()
    {
        $this->instructionList = [];
        
        $max = $this->strucData['size'] - 1;
        for ($i = $this->strucData['first-bit']; $i <= $this->strucData['last-bit']; $i ++) {
            $offset = (int) ($i / 8);
            $pos = $max - $offset;
            $bit = $i - 8 * $offset;
            
            $strucData = [];
            $strucData['position'] = $pos;
            $strucData['bit'] = $bit;
            $strucData['size'] = 1;
            $strucData['name'] = $this->dictionary
                ? (string) $this->dictionary->getOption($i)
                : '';
            
            $this->instructionList[] = [
                'tagName' => 'bit',
                'element' => $this->getStrucElement(),
                'strucData' => $strucData,
            ];
        }
    }
}