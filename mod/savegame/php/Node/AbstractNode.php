<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\Converter;
use Slothsoft\Savegame\Editor;
use DOMElement;
declare(ticks = 1000);

abstract class AbstractNode
{

    abstract protected function loadNode();

    protected $ownerEditor;

    protected $ownerArchive;

    protected $ownerFile;

    protected $converter;

    public $parentNode;

    protected $childNodeList;

    protected $strucElement;

    protected $strucData;

    public function __construct()
    {
        $this->strucData = [];
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
        $this->childNodeList = $this->ownerEditor->createNodes($this, $nodeList);
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

    public function setStrucData(array $struc)
    {
        foreach ($struc as $key => $val) {
            if (isset($this->strucData[$key])) {
                $this->strucData[$key] = $val;
            }
        }
    }

    public function getStrucElement()
    {
        return $this->strucElement;
    }

    protected function parseInt($val)
    {
        $val = trim($val);
        if (preg_match('/^0x(\w+)$/', $val, $match)) {
            $val = hexdec($match[1]);
            // echo $match[1] . '=' . $val . PHP_EOL;
        }
        return (int) $val;
    }

    protected function parseTokenList($val)
    {
        return $val;
    }

    public function getOwnerFile()
    {
        return $this->ownerFile;
    }

    public function getOwnerArchive()
    {
        return $this->ownerArchive;
    }
}