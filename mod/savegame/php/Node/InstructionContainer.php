<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

class InstructionContainer extends AbstractContainerContent 
{

    private $type;

    private $dictionaryRef;

    public  function getXmlTag(): string
    {
        return 'instruction';
    }

    public function getXmlAttributes(): string
    {
        return parent::getXmlAttributes() . $this->createXmlIdAttribute('type', $this->type) . $this->createXmlIdAttribute('dictionary-ref', $this->dictionaryRef);
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->type = (string) $strucElement->getAttribute('type');
        $this->dictionaryRef = (string) $strucElement->getAttribute('dictionary-ref');
    }
}
