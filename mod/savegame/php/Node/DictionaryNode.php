<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

class DictionaryNode extends AbstractNode
{

    private $dictionaryId;

    private $optionList;

    private $descriptionList;

    protected function getXmlTag(): string
    {
        return 'dictionary';
    }

    protected function getXmlAttributes(): string
    {
        return $this->createXmlIdAttribute('dictionary-id', $this->dictionaryId);
    }

    protected function getXmlContent(): string
    {
        $ret = '';
        foreach ($this->optionList as $key => $val) {
            $ret .= $this->createXmlElement('option', $this->createXmlIntegerAttribute('key', $key) . $this->createXmlTextAttribute('val', $val) . $this->createXmlTextAttribute('description', $this->descriptionList[$key]), '');
            
            // $ret .= sprintf('<option key="%s" val="%s"/>', htmlspecialchars($key, ENT_COMPAT | ENT_XML1), htmlspecialchars($val, ENT_COMPAT | ENT_XML1));
            $ret .= PHP_EOL;
        }
        return $ret;
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->dictionaryId = (string) $strucElement->getAttribute('dictionary-id');
    }

    protected function loadNode(EditorElement $strucElement)
    {
        $this->optionList = [];
        $this->descriptionList = [];
        foreach ($strucElement->getChildren() as $optionElement) {
            $key = (int) $optionElement->getAttribute('key', count($this->optionList));
            $val = (string) $optionElement->getAttribute('val');
            $title = (string) $optionElement->getAttribute('title');
            
            $this->optionList[$key] = $val;
            $this->descriptionList[$key] = $title;
        }
    }

    protected function loadChildren(EditorElement $strucElement)
    {}

    public function hasOption(string $key)
    {
        return isset($this->optionList[$key]);
    }

    public function getOption(string $key)
    {
        return $this->optionList[$key] ?? null;
    }

    public function getDictionaryId(): string
    {
        return $this->dictionaryId;
    }
}