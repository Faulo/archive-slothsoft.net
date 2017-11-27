<?php
namespace Slothsoft\Savegame\Node;

use Ds\Vector;
use Slothsoft\Savegame\Converter;
use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

abstract class AbstractNode
{

    abstract protected function loadNode(EditorElement $strucElement);
    
    abstract protected function getXmlTag() : string;
    abstract protected function getXmlAttributes(): string;

    /**
     *
     * @var \Slothsoft\Savegame\Node\AbstractNode
     */
    private $parentNode;
    
    private $childNodeList;

    public function __construct()
    {}

    public function init(EditorElement $strucElement, AbstractNode $parentNode = null)
    {
        $this->parentNode = $parentNode;
        
        if ($this->parentNode) {
            $this->parentNode->appendChild($this);
        }
        
        $this->loadStruc($strucElement);
        $this->loadNode($strucElement);
        $this->loadChildren($strucElement);
    }

    protected function loadStruc(EditorElement $strucElement)
    {
    }

    protected function loadChildren(EditorElement $strucElement)
    {
        foreach ($strucElement->getChildren() as $strucElement) {
            $this->loadChild($strucElement);
        }
    }

    protected function loadChild(EditorElement $strucElement)
    {
        if ($node = $this->getOwnerEditor()->createNode($this, $strucElement)) {
            // echo get_class($node) . PHP_EOL;
        }
    }

    /**
     *
     * @return \Slothsoft\Savegame\Editor
     */
    public function getOwnerEditor() : Editor
    {
        return $this->getOwnerSavegame()->getOwnerEditor();
    }
    /**
     *
     * @return \Slothsoft\Savegame\Node\SavegameNode
     */
    public function getOwnerSavegame() : SavegameNode
    {
        return $this->parentNode instanceof SavegameNode
            ? $this->parentNode
        : $this->parentNode->getOwnerSavegame();
    }

    public function asXML(): string
    {
        return $this->createXmlElement($this->getXmlTag(), $this->getXmlAttributes(), $this->getXmlContent());
    }
    
    protected function getXmlContent(): string
    {
        $content = '';
        foreach ($this->getChildNodeList() as $child) {
            $content .= $child->asXML();
        }
        return $content;
    }

    protected function createXmlElement(string $tagName, string $attributes, string $content): string
    {
        return $content === ''
            ? "<$tagName $attributes />"
            : "<$tagName $attributes>$content</$tagName>";
    }
    protected function createXmlIntegerAttribute(string $name, int $value) : string {
        return " $name=\"$value\"";
    }
    protected function createXmlIdAttribute(string $name, string $value) : string {
        return $value === ''
            ? ''
            : " $name=\"$value\"";
    }
    protected function createXmlTextAttribute(string $name, string $value) : string {
        $value = htmlspecialchars($value, ENT_COMPAT | ENT_XML1);
        return $value === ''
            ? ''
            : " $name=\"$value\"";
    }

    /**
     *
     * @return \Slothsoft\Savegame\Converter
     */
    protected function getConverter()
    {
        return Converter::getInstance();
    }

    public function getParentNode()
    {
        return $this->parentNode;
    }

    
    public function appendChild(AbstractNode $childNode) {
        if ($this->childNodeList === null) {
            $this->childNodeList = new Vector();
        }
        $this->childNodeList[] = $childNode;
    }
    
    public function getChildNodeList()
    {
        return $this->childNodeList ? $this->childNodeList : [];
    }
}