<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

class ForEachFileInstruction extends AbstractNode
{
    protected function getXmlTag(): string
    {
        return 'for-each-file';
    }
    protected function getXmlAttributes(): string
    {
        return '';
    }
    public function asXML(): string
    {
        return $this->getXmlContent();
    }

    public function loadChildren(EditorElement $strucElement)
    {
        $archive = $this->getParentNode();
        
        foreach ($archive->getFileNameList() as $name) {
            $strucData = [];
            $strucData['file-name'] = $name;
            
            $childElement = new EditorElement(EditorElement::NODE_TYPES['file'], $strucData, $strucElement->getChildren());
            
            $this->loadChild($childElement);
        }
    }

    protected function loadNode(EditorElement $strucElement)
    {
        
    }
}