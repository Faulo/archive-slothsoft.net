<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class SavegameNode extends AbstractNode
{

    public function __construct()
    {
        parent::__construct();
        $this->strucData['save-id'] = '';
        $this->strucData['save-mode'] = '';
    }
    
    protected function initStrucChildren() {
        $nodeList = parent::initStrucChildren();
        
        $archiveList = [];
        $globalList = [];
        $dictionaryList = [];
        
        foreach ($nodeList as $node) {
            switch ($node->localName) {
                case 'dictionary':
                    $dictionaryList[] = $node;
                    break;
                case 'archive':
                    $archiveList[] = $node;
                    break;
                case 'global':
                    $globalList[] = $node;
                    break;
            }
        }
        
        return array_merge($dictionaryList, $globalList, $archiveList);
    }
    protected function loadNode()
    {}
    
    public function asXML() {
        return $this->createXML($this->tagName, ['xmlns' => 'http://schema.slothsoft.net/savegame/editor'] + $this->strucData, $this->getChildrenXML());
    }
}