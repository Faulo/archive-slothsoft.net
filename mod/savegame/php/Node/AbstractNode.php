<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\Converter;
use Slothsoft\Savegame\Editor;
use Slothsoft\Savegame\TypeParser;
use DOMElement;
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
     * @var \DOMElement
     */
    private $strucElement;

    /**
     *
     * @var \DOMElement[]
     */
    private $strucElementChildren = [];

    /**
     *
     * @var mixed[string]
     */
    protected $strucData = [];

    protected $tagName;

    public function __construct()
    {}

    public function init(Editor $ownerEditor, DOMElement $strucElement, AbstractNode $parentNode = null, string $tagName, array $overrideData)
    {
        $this->ownerEditor = $ownerEditor;
        $this->strucElement = $strucElement;
        if ($parentNode) {
            $this->parentNode = $parentNode;
            $this->ownerFile = $this->parentNode->getOwnerFile();
            $this->parentNode->appendNode($this);
        }
        $this->tagName = $tagName;
        
        $this->strucElementChildren = $this->initStrucChildren();
        $this->initStrucAttributes($overrideData);
        
        $this->loadStruc();
        $this->loadNode();
        $this->loadChildren();
        
        return true;
    }

    protected function initStrucChildren()
    {
        $nodeList = [];
        foreach ($this->strucElement->childNodes as $node) {
            if ($node instanceof DOMElement) {
                $nodeList[] = $node;
            }
        }
        return $nodeList;
    }

    protected function initStrucAttributes(array $overrideData)
    {
        foreach ($this->strucData as $key => &$val) {
            if (isset($overrideData[$key])) {
                $val = $overrideData[$key];
            } elseif ($this->strucElement->hasAttribute($key)) {
                $val = $this->strucElement->getAttribute($key);
            }
        }
        unset($val);
    }

    protected function loadStruc()
    {}

    protected function loadChildren()
    {
        foreach ($this->getStrucElementChildren() as $strucElement) {
            $this->loadChild($strucElement, $strucElement->localName, []);
        }
    }

    protected function loadChild(DOMElement $strucElement, string $tagName, array $strucData)
    {
        if ($node = $this->ownerEditor->createNode($this, $strucElement, $tagName, $strucData)) {
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
     * @return \DOMElement
     */
    public function getStrucElement()
    {
        return $this->strucElement;
    }

    /**
     *
     * @return \DOMElement[]
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
        return $this->createXML($this->tagName, $this->strucData, $this->getChildrenXML());
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