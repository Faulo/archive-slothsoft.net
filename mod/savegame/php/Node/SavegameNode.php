<?php
namespace Slothsoft\Savegame\Node;

use Ds\Vector;
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
    
    private $dictionaryList;
    
    private $archiveList;
    
    private $globalElements;

    private $saveId;
    
    private $valueIdCounter = 0;

    public function __construct(Editor $ownerEditor = null)
    {
        $this->ownerEditor = $ownerEditor;
        
        parent::__construct();
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->saveId = (string) $strucElement->getAttribute('save-id');
        
        $this->dictionaryList = new Vector();
        $this->archiveList = new Vector();
        
        $this->globalElements = [];
    }

    public function getOwnerEditor() : Editor
    {
        return $this->ownerEditor;
    }

    public function loadChildren(EditorElement $strucElement)
    {
        log_execution_time(__FILE__, __LINE__);
        
        $archiveList = [];
        $globalList = [];
        $dictionaryList = [];
        
        foreach ($strucElement->getChildren() as $element) {
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
        
        foreach ($globalList as $element) {
            $this->globalElements[$element->getAttribute('global-id')] = $element->getChildren();
        }
        
        log_execution_time(__FILE__, __LINE__);
        
        foreach ($dictionaryList as $element) {
            $this->loadChild($element);
        }
        
        log_execution_time(__FILE__, __LINE__);
        
        foreach ($archiveList as $element) {
            $this->loadChild($element);
        }
        
        log_execution_time(__FILE__, __LINE__);
    }

    protected function loadNode(EditorElement $strucElement)
    {}
    
    public function appendChild(AbstractNode $node) {
        if ($node instanceof DictionaryNode) {
            $this->dictionaryList[] = $node;
        }
        if ($node instanceof ArchiveNode) {
            $this->archiveList[] = $node;
        }
    }
    
    protected function getXmlTag(): string
    {
        return 'savegame.editor';
    }
    protected function getXmlAttributes(): string
    {
        return $this->createXmlIdAttribute('xmlns', 'http://schema.slothsoft.net/savegame/editor')
        . $this->createXmlTextAttribute('save-id', $this->saveId);
    }
    protected function getXmlContent(): string
    {
        $content = '';
        log_execution_time(__FILE__, __LINE__);
        foreach ($this->dictionaryList as $child) {
            $content .= $child->asXML();
        }
        foreach ($this->archiveList as $child) {
            $content .= $child->asXML();
        }
        log_execution_time(__FILE__, __LINE__);
        return $content;
    }
    
    public function getDictionaryById(string $id)
    {
        foreach ($this->dictionaryList as $node) {
            if ($node->getDictionaryId() === $id) {
                return $node;
            }
        }
    }
    
    public function getArchiveById(string $id)
    {
        foreach ($this->archiveList as $node) {
            if ($node->getArchiveId() === $id) {
                return $node;
            }
        }
    }
    
    public function getGlobalElementsById(string $id) {
        return $this->globalElements[$id] ?? null;
    }
    
    public function nextValueId() : int {
        return ++$this->valueIdCounter;
    }
}