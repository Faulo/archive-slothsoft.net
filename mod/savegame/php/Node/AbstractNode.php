<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\Converter;
use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\EditorElement;
use Slothsoft\Savegame\TypeParser;
declare(ticks = 1000);

abstract class AbstractNode
{

    abstract protected function loadNode();

    /**
     *
     * @var \Slothsoft\Savegame\Editor
     */
    protected $ownerEditor;

    /**
     *
     * @var \Slothsoft\Savegame\Node\FileContainer
     */
    protected $ownerFile;

    /**
     *
     * @var \Slothsoft\Savegame\Node\AbstractNode
     */
    protected $parentNode;

    /**
     *
     * @var \Slothsoft\Savegame\Node\AbstractNode[]
     */
    protected $childNodeList = [];

    /**
     *
     * @var \Slothsoft\Savegame\EditorElement
     */
    private $strucElement;

    /**
     *
     * @var \Slothsoft\Savegame\EditorElement[]
     */
    private $strucElementChildren = [];

    /**
     *
     * @var mixed[string]
     */
    protected $strucData = [];

    public function __construct()
    {}

    public function init(Editor $ownerEditor, EditorElement $strucElement, AbstractNode $parentNode = null)
    {
        $this->ownerEditor = $ownerEditor;
        $this->strucElement = $strucElement;
        if ($parentNode) {
            $this->parentNode = $parentNode;
            $this->ownerFile = $this->parentNode->getOwnerFile();
            $this->parentNode->appendNode($this);
        }
        
        $this->strucElementChildren = $this->initStrucChildren();
        $this->initStrucAttributes();
        
        $this->loadStruc();
        $this->loadNode();
        $this->loadChildren();
        
        return true;
    }

    protected function initStrucChildren()
    {
        $elementList = [];
        foreach ($this->strucElement->getChildren() as $id) {
            $elementList[] = $this->ownerEditor->getElementById($id);
        }
        return $elementList;
    }

    protected function initStrucAttributes()
    {
        $this->setStrucData($this->strucElement->getAttributes());
    }

    protected function loadStruc()
    {}

    protected function loadChildren()
    {
        foreach ($this->getStrucElementChildren() as $strucElement) {
            $this->loadChild($strucElement);
        }
    }

    protected function loadChild(EditorElement $strucElement)
    {
        if ($node = $this->ownerEditor->createNode($this, $strucElement)) {
            // echo get_class($node) . PHP_EOL;
        }
    }

    public function updateContent()
    {
        foreach ($this->childNodeList as $value) {
            $value->updateContent();
        }
    }

    /**
     *
     * @param array $struc
     */
    public function setStrucData(array $struc)
    {
        foreach ($struc as $key => $val) {
            if (isset($this->strucData[$key])) {
                $this->strucData[$key] = $val;
            }
        }
    }

    /**
     *
     * @return EditorElement
     */
    public function getStrucElement()
    {
        return $this->strucElement;
    }

    /**
     *
     * @return EditorElement[]
     */
    public function getStrucElementChildren()
    {
        return $this->strucElementChildren;
    }

    /**
     *
     * @return NULL|\Slothsoft\Savegame\Node\FileContainer
     */
    public function getOwnerFile()
    {
        return $this->ownerFile;
    }

    /**
     *
     * @return int
     */
    public function getNodeId()
    {
        return spl_object_hash($this);
    }

    /**
     *
     * @param string $name
     * @return NULL|\Slothsoft\Savegame\Node\AbstractValueContent
     */
    public function getValueByName(string $name)
    {
        $ret = null;
        // echo count($this->childNodeList) . PHP_EOL;
        foreach ($this->childNodeList as $node) {
            if ($node instanceof AbstractValueContent) {
                // echo $node->getName() . PHP_EOL;
                if ($node->getName() === $name) {
                    $ret = $node;
                    break;
                }
            }
            if ($ret = $node->getValueByName($name)) {
                break;
            }
        }
        return $ret;
    }

    public function appendNode(AbstractNode $node)
    {
        $this->childNodeList[] = $node;
    }

    public function asXML()
    {
        return $this->createXML($this->strucElement->getType(), $this->strucData, $this->getChildrenXML());
    }

    protected function getChildrenXML()
    {
        $content = '';
        foreach ($this->childNodeList as $child) {
            $content .= $child->asXML();
        }
        return $content;
    }

    protected function createXML(string $tagName, array $attributes, string $content)
    {
        // $ret = sprintf('<%s', $tagName);
        $ret = '<' . $tagName;
        
        foreach ($attributes as $key => $val) {
            $val = (string) $val;
            if ($val !== '') {
                // $ret .= sprintf(' %s="%s"', $key, htmlentities($val, ENT_XML1));
                $ret .= ' ' . $key . '="' . htmlspecialchars($val, ENT_COMPAT | ENT_XML1) . '"';
            }
        }
        
        $ret .= $content === '' ? '/>' : '>' . $content . '</' . $tagName . '>'; // sprintf('>%s</%s>', $content, $tagName);
        return $ret;
    }

    /**
     *
     * @return \Slothsoft\Savegame\Converter
     */
    protected function getConverter()
    {
        return Converter::getInstance();
    }

    /**
     *
     * @return \Slothsoft\Savegame\TypeParser
     */
    protected function getParser()
    {
        return TypeParser::getInstance();
    }
}