<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\EditorElement;

declare(ticks = 1000);

class SavegameNode extends AbstractNode
{
    
    /**
     *
     * @var \Slothsoft\Savegame\Editor
     */
    private $ownerEditor;
    
    private $globalNodeList;
    private $dictionaryNodeList;
    private $archiveNodeList;
    
    private $saveId;

    public function __construct(Editor $ownerEditor = null)
    {
        $this->ownerEditor = $ownerEditor;
        
        parent::__construct();
    }
    
    protected function getXmlAttributes() : string
    {
        $ret = parent::getXmlAttributes();
        $ret .= sprintf(
            ' xmlns="http://schema.slothsoft.net/savegame/editor" save-id="%s"',
            htmlspecialchars($this->saveId, ENT_COMPAT | ENT_XML1)
        );
        return $ret;
    }
    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->saveId = $this->loadStringAttribute('save-id');
    }
    
    public function getOwnerEditor() {
        return $this->ownerEditor;
    }

    public function getStrucElementChildren()
    {
        $elementList = parent::getStrucElementChildren();
        
        $archiveList = [];
        $globalList = [];
        $dictionaryList = [];
        
        foreach ($elementList as $element) {
            switch ($element->getType()) {
                case EditorElement::NODE_TYPES['dictionary']:
                    $dictionaryList[] = $element;
                    break;
                case EditorElement::NODE_TYPES['archive']:
                    $archiveList[] = $element;
                    break;
                case EditorElement::NODE_TYPES['global']:
                    $globalList[] = $element;
                    break;
            }
        }
        
        return array_merge($dictionaryList, $globalList, $archiveList);
    }

    protected function loadNode()
    {}
}