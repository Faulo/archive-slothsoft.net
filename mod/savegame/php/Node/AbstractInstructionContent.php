<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

abstract class AbstractInstructionContent extends AbstractContentNode
{

    abstract protected function loadInstruction();
    
    protected $instructionList;

    protected $dictionary;

    public function __construct()
    {
        parent::__construct();
        $this->strucData['dictionary-ref'] = '';
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        
        if ($this->strucData['dictionary-ref'] !== '') {
            $this->dictionary = $this->ownerEditor->getDictionaryById($this->strucData['dictionary-ref']);
        }
    }

    protected function loadContent()
    {
        $this->loadInstruction();
    }

    protected function loadChildren()
    {
        foreach ($this->instructionList as $instruction) {
            $this->loadChild($instruction['element'], $instruction['tagName'], $instruction['strucData']);
        }
    }
    public function asXML() {
        $attributes = [];
        $attributes['instruction'] = $this->tagName;
        $attributes['name'] = $this->strucData['name'];
        $attributes['position'] = $this->strucData['position'];
        return $this->createXML('group', $attributes, $this->getChildrenXML());
    }
}
