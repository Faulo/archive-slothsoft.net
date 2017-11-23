<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class DictionaryNode extends AbstractNode
{

    private $dictionaryId;
    private $optionList;
    
    protected function getXmlAttributes() : string
    {
        $ret = parent::getXmlAttributes();
        $ret .= " dictionary-id='$this->dictionaryId'";
        return $ret;
    }
    
    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->dictionaryId = $this->loadStringAttribute('dictionary-id');
    }

    protected function loadNode()
    {
        $this->optionList = [];
        foreach ($this->getStrucElementChildren() as $optionElement) {
            $key = $optionElement->hasAttribute('key') ? $optionElement->getAttribute('key') : (string) count($this->optionList);
            $val = $optionElement->getAttribute('val');
            
            $this->optionList[$key] = $val;
        }
    }

    protected function loadChildren()
    {}

    public function hasOption(string $key)
    {
        return isset($this->optionList[$key]);
    }

    public function getOption(string $key)
    {
        return $this->optionList[$key] ?? null;
    }

    public function getDictionaryId() : string
    {
        return $this->dictionaryId;
    }

    public function getXmlContent() : string
    {
        $ret = '';
        foreach ($this->optionList as $key => $val) {
            $ret .= sprintf(
                '<option key="%s" val="%s"/>',
                htmlspecialchars($key, ENT_COMPAT | ENT_XML1),
                htmlspecialchars($val, ENT_COMPAT | ENT_XML1)
            );
        }
        return $ret;
    }
}