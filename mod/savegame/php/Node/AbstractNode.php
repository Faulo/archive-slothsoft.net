<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\Converter;
use Slothsoft\Savegame\Editor;
use DOMElement;
declare(ticks = 1000);

abstract class AbstractNode
{

    abstract protected function loadNode();

    /**
     * @var \Slothsoft\Savegame\Editor
     */
    protected $ownerEditor;

    /**
     * @var \Slothsoft\Savegame\Node\ArchiveNode
     */
    protected $ownerArchive;

    /**
     * @var \Slothsoft\Savegame\Node\FileContainer
     */
    protected $ownerFile;

    /**
     * @var \Slothsoft\Savegame\Converter
     */
    protected $converter;

    /**
     * @var \Slothsoft\Savegame\Node\AbstractNode
     */
    public $parentNode;

    /**
     * @var \Slothsoft\Savegame\Node\AbstractNode[]
     */
    protected $childNodeList;

    /**
     * @var \DOMElement
     */
    protected $strucElement;

    /**
     * @var mixed[string]
     */
    protected $strucData;

    public function __construct()
    {
        $this->strucData = [];
        $this->childNodeList = [];
        $this->converter = Converter::getInstance();
    }

    public function init(Editor $ownerEditor, DOMElement $strucElement, AbstractNode $parentNode)
    {
        $this->ownerEditor = $ownerEditor;
        $this->strucElement = $strucElement;
        $this->parentNode = $parentNode;
        $this->ownerFile = $this->parentNode->getOwnerFile();
        $this->ownerArchive = $this->parentNode->getOwnerArchive();
        
        $this->loadStruc();
        $this->loadNode();
        $this->loadChildren();
        
        return true;
    }

    protected function loadStruc()
    {
        foreach ($this->strucData as $key => &$val) {
            if ($this->strucElement->hasAttribute($key)) {
                $val = $this->strucElement->getAttribute($key);
            }
        }
        unset($val);
    }

    protected function loadChildren()
    {
        $nodeList = [];
        foreach ($this->strucElement->childNodes as $strucElement) {
            if ($strucElement->nodeType === XML_ELEMENT_NODE) {
                $nodeList[] = $strucElement;
            }
        }
        $this->ownerEditor->createNodes($this, $nodeList);
        
        while ($this->strucElement->hasChildNodes()) {
            $this->strucElement->removeChild($this->strucElement->lastChild);
        }
        foreach ($this->childNodeList as $childNode) {
            $this->strucElement->appendChild($childNode->getStrucElement());
        }
    }

    public function updateContent()
    {
        foreach ($this->childNodeList as $value) {
            $value->updateContent();
        }
    }

    public function updateStrucNode()
    {
        if ($this->strucElement) {
            // echo $this->strucData['contentId'] . PHP_EOL;
            foreach ($this->strucData as $key => $val) {
                switch ($key) {
                    default:
                        if (strlen($val)) {
                            $this->strucElement->setAttribute($key, $val);
                        } else {
                            $this->strucElement->removeAttribute($key);
                        }
                        break;
                }
            }
        }
        foreach ($this->childNodeList as $value) {
            $value->updateStrucNode();
        }
    }

    /**
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
     * @return DOMElement
     */
    public function getStrucElement()
    {
        return $this->strucElement;
    }

    /**
     * @param string $val
     * @return int
     */
    protected function parseInt(string $val)
    {
        $val = trim($val);
        if (preg_match('/^{(.+)}$/', $val, $match)) {
            $expr = $match[1];
            preg_match_all('/[\w-]+/', $expr, $matches);
            $translate = [];
            foreach ($matches[0] as $key) {
                if ($node = $this->ownerFile->getValueByName($key)) {
                    $val = $node->getValue();
                } else {
                    $val = 0;
                }
                $translate[$key] = $val;
            }
            $expr = strtr($expr, $translate);
            $val = eval("return ($expr);");
        }
        if (preg_match('/^0x(\w+)$/', $val, $match)) {
            $val = hexdec($match[1]);
            // echo $match[1] . '=' . $val . PHP_EOL;
        }
        return (int) $val;
    }

    /**
     * @param string $val
     * @return string
     */
    protected function parseTokenList(string $val)
    {
        return $val;
    }

    /**
     * @return NULL|\Slothsoft\Savegame\Node\FileContainer
     */
    public function getOwnerFile()
    {
        return $this->ownerFile;
    }

    /**
     * @return NULL|\Slothsoft\Savegame\Node\ArchiveNode
     */
    public function getOwnerArchive() {
        return $this->ownerArchive;
    }
    
    /**
     * @param string $name
     * @return NULL|\Slothsoft\Savegame\Node\AbstractValueContent
     */
    public function getValueByName(string $name) {
        $ret = null;
        foreach ($this->childNodeList as $node) {
            if ($node instanceof AbstractValueContent) {
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
    
    /**
     * @param \Slothsoft\Savegame\Node\AbstractNode $node
     */
    public function appendNode(AbstractNode $node) {
        $this->childNodeList[] = $node;
    }
}