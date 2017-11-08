<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\Converter;
use Slothsoft\Savegame\Editor;
use DOMElement;
use Exception;
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
     * @var \Slothsoft\Savegame\Node\ArchiveNode
     */
    protected $ownerArchive;

    /**
     *
     * @var \Slothsoft\Savegame\Node\FileContainer
     */
    protected $ownerFile;

    /**
     *
     * @var \Slothsoft\Savegame\Converter
     */
    protected $converter;

    /**
     *
     * @var \Slothsoft\Savegame\Node\AbstractNode
     */
    public $parentNode;

    /**
     *
     * @var \Slothsoft\Savegame\Node\AbstractNode[]
     */
    protected $childNodeList;

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
    protected $strucData;

    protected $tagName;

    protected $nodeId;

    public function __construct()
    {
        static $idCounter = 0;
        
        $this->strucData = [];
        $this->childNodeList = [];
        $this->converter = Converter::getInstance();
        $this->parser = TypeParser::getInstance();
        $this->nodeId = $idCounter;
        
        $idCounter ++;
    }

    public function init(Editor $ownerEditor, DOMElement $strucElement, AbstractNode $parentNode = null, string $tagName, array $overrideData)
    {
        $this->ownerEditor = $ownerEditor;
        $this->strucElement = $strucElement;
        if ($parentNode) {
            $this->parentNode = $parentNode;
            $this->ownerFile = $this->parentNode->getOwnerFile();
            $this->ownerArchive = $this->parentNode->getOwnerArchive();
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

    private $expressionCache = [];

    protected function parseExpression($expr)
    {
        preg_match_all('/\$([A-Za-z0-9\-\.]+)/', $expr, $matches);
        $translate = [];
        foreach ($matches[0] as $i => $key) {
            if ($node = $this->ownerFile->getValueByName($matches[1][$i])) {
                $val = $node->getValue();
            } else {
                $val = 0;
            }
            $translate[$key] = $val;
        }
        $expr = strtr($expr, $translate);
        echo $expr . PHP_EOL;
        return eval("return (int) ($expr);");
    }

    /**
     *
     * @param mixed $val
     * @return int
     */
    protected function parseInt($val)
    {
        if (is_int($val)) {
            return $val;
        }
        $val = trim($val);
        if (is_numeric($val)) {
            return (int) $val;
        }
        if (preg_match('/^0x(\w+)$/', $val, $match)) {
            return hexdec($match[1]);
        }
        if (preg_match('/^{(.+)}$/', $val, $match)) {
            $expr = $match[1];
            return (int) $this->ownerFile->parseExpression($expr);
        }
        throw new Exception(sprintf('unknown integer type "%s"', $val));
    }

    /**
     *
     * @param string $val
     * @return string
     */
    protected function parseTokenList(string $val)
    {
        return $val;
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
     * @return NULL|\Slothsoft\Savegame\Node\ArchiveNode
     */
    public function getOwnerArchive()
    {
        return $this->ownerArchive;
    }

    /**
     *
     * @return int
     */
    public function getNodeId()
    {
        return $this->nodeId;
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
                $ret .= ' ' . $key . '="' . htmlentities($val, ENT_COMPAT | ENT_XML1) . '"';
            }
        }
        
        $ret .= $content === '' ? '/>' : '>' . $content . '</' . $tagName . '>'; // sprintf('>%s</%s>', $content, $tagName);
        return $ret;
    }
}