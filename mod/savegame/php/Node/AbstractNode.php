<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\Converter;
use Slothsoft\Savegame\EditorElement;
use DS\Vector;

declare(ticks = 1000);

abstract class AbstractNode
{

    abstract protected function loadNode();

    /**
     *
     * @var \Slothsoft\Savegame\Node\AbstractNode
     */
    protected $parentNode;

    /**
     *
     * @var \Slothsoft\Savegame\EditorElement
     */
    private $strucElement;

    public function __construct()
    {
		
	}

    public function init(EditorElement $strucElement, AbstractNode $parentNode = null)
    {
        $this->strucElement = $strucElement;
        $this->parentNode = $parentNode;
        
        $this->loadStruc();
        $this->loadNode();
        $this->loadChildren();
        
        return true;
    }

    protected function loadStruc()
    {}
    
    
    protected function loadStringAttribute(string $key, string $default = '') : string {
        return $this->getStrucElement()->hasAttribute($key)
        ? $this->getStrucElement()->getAttribute($key)
        : $default;
    }

    protected function loadChildren()
    {
        foreach ($this->getStrucElementChildren() as $strucElement) {
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
        return $this->strucElement->getChildren();
    }
    
    /**
     *
     * @return NULL|\Slothsoft\Savegame\Editor
     */
    public function getOwnerEditor()
    {
        return $this->parentNode->getOwnerEditor();
    }

    public function asXML() : string
    {
        return $this->createXML(
            $this->getXmlTag(),
            $this->getXmlAttributes(),
            $this->getXmlContent()
        );
    }
    protected function getXmlTag() : string {
        return $this->strucElement->getTag();
    }
    protected function getXmlAttributes() : string
    {
        return '';
    }
    protected function getXmlContent() : string
    {
        $content = '';
        foreach ($this->getChildNodeList() as $child) {
            $content .= $child->asXML();
		}
        return $content;
    }

    protected function createXML(string $tagName, string $attributes, string $content) : string
    {
        return $content === ''
            ? "<$tagName $attributes />"
            : "<$tagName $attributes>$content</$tagName>";
        /*
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
        //*/
    }

    /**
     *
     * @return \Slothsoft\Savegame\Converter
     */
    protected function getConverter()
    {
        return Converter::getInstance();
    }
    public function getParentNode() {
        return $this->parentNode;
    }
    public function getChildNodeList() {
        return $this->getOwnerEditor()->getNodeListByParentNode($this);
    }
}