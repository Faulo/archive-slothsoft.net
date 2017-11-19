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

    protected function initStrucChildren()
    {
        $elementList = parent::initStrucChildren();
        
        $archiveList = [];
        $globalList = [];
        $dictionaryList = [];
        
        foreach ($elementList as $element) {
            switch ($element->getType()) {
                case 'dictionary':
                    $dictionaryList[] = $element;
                    break;
                case 'archive':
                    $archiveList[] = $element;
                    break;
                case 'global':
                    $globalList[] = $element;
                    break;
            }
        }
        
        return array_merge($dictionaryList, $globalList, $archiveList);
    }

    protected function loadNode()
    {}

    public function asXML()
    {
        return $this->createXML($this->getStrucElement()->getType(), [
            'xmlns' => 'http://schema.slothsoft.net/savegame/editor'
        ] + $this->strucData, $this->getChildrenXML());
    }
}