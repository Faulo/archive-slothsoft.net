<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

abstract class AbstractInstructionContent extends AbstractContentNode
{

    abstract protected function loadInstruction(EditorElement $strucElement);

    abstract protected function getXmlInstructionType(): string;

    // protected $dictionary;
    protected $dictionaryRef;

    protected function getXmlTag(): string
    {
        return 'instruction';
    }

    protected function getXmlAttributes(): string
    {
        return parent::getXmlAttributes() . $this->createXmlIdAttribute('type', $this->getXmlInstructionType()) . $this->createXmlIdAttribute('dictionary-ref', $this->dictionaryRef);
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->dictionaryRef = (string) $strucElement->getAttribute('dictionary-ref');
    }

    protected function loadContent(EditorElement $strucElement)
    {}

    protected function loadChildren(EditorElement $strucElement)
    {
        if ($instructionList = $this->loadInstruction($strucElement)) {
            foreach ($instructionList as $instruction) {
                $this->loadChild($instruction);
            }
        }
    }
}
