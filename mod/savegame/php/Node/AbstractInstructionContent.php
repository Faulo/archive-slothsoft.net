<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;

declare(ticks = 1000);

abstract class AbstractInstructionContent extends AbstractContentNode
{

    abstract protected function loadInstruction(EditorElement $strucElement);

//     protected $dictionary;

    protected $dictionaryRef;
    
    public function asXML(): string
    {
        return $this->createXmlElement('group', $this->getXmlAttributes(), $this->getXmlContent());
    }

    protected function getXmlAttributes(): string
    {
        return parent::getXmlAttributes()
            . $this->createXmlIdAttribute('instruction', $this->getXmlTag())
            . $this->createXmlIdAttribute('dictionary-ref', $this->dictionaryRef);
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->dictionaryRef = (string) $strucElement->getAttribute('dictionary-ref');
        
//         if ($this->dictionaryRef !== '') {
//             $this->dictionary = $this->getOwnerSavegame()->getDictionaryById($this->dictionaryRef);
//         }
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
