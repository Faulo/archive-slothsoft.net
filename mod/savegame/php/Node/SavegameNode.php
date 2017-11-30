<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

class SavegameNode extends AbstractNode implements XmlBuildableInterface
{

    /**
     *
     * @var \Slothsoft\Savegame\Editor
     */
    private $ownerEditor;

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
        
        $this->globalElements = [];
    }

    public function getOwnerEditor(): Editor
    {
        return $this->ownerEditor;
    }

    public function loadChildren(EditorElement $strucElement)
    {
        log_execution_time(__FILE__, __LINE__);
        
        $archiveList = [];
        $globalList = [];
        
        foreach ($strucElement->getChildren() as $element) {
            switch ($element->getType()) {
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
        
        foreach ($archiveList as $element) {
            $this->loadChild($element);
        }
        
        log_execution_time(__FILE__, __LINE__);
    }

    protected function loadNode(EditorElement $strucElement)
    {}

    public function appendChild(XmlBuildableInterface $node)
    {
        assert($node instanceof ArchiveNode);
        
        parent::appendChild($node);
    }

    public  function getXmlTag(): string
    {
        return 'savegame.editor';
    }

    public function getXmlAttributes(): string
    {
        return $this->createXmlIdAttribute('xmlns', 'http://schema.slothsoft.net/savegame/editor') . $this->createXmlTextAttribute('save-id', $this->saveId) . $this->createXmlIdAttribute('schemaVersion', '0.3');
    }

    public function getArchiveById(string $id) : ArchiveNode
    {
        foreach ($this->getChildNodeList() as $node) {
            if ($node->getArchiveId() === $id) {
                return $node;
            }
        }
    }

    public function getGlobalElementsById(string $id)
    {
        return $this->globalElements[$id] ?? null;
    }

    public function nextValueId(): int
    {
        return ++ $this->valueIdCounter;
    }
}