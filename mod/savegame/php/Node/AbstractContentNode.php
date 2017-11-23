<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

abstract class AbstractContentNode extends AbstractNode
{

    private $name;
    private $position;
    protected $valueOffset;

    abstract protected function loadContent();

    protected function getXmlAttributes() : string
    {
        $ret = parent::getXmlAttributes();
        if ($this->name) {
            $ret .= " name='{$this->getName()}'";
        }
        return $ret;
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->name = $this->loadStringAttribute('name');
        $this->position = $this->loadIntegerAttribute('position');
        
        $this->valueOffset = $this->position;
        if ($this->parentNode instanceof AbstractContentNode) {
            $this->valueOffset += $this->parentNode->getOffset();
        }
    }
    protected function loadIntegerAttribute(string $key, int $default = 0) : int {
        return $this->getStrucElement()->hasAttribute($key)
            ? $this->getOwnerFile()->evaluate($this->getStrucElement()->getAttribute($key))
            : $default;
    }

    protected function loadNode()
    {
        $this->loadContent();
    }
    
    /**
     *
     * @return NULL|\Slothsoft\Savegame\Node\FileContainer
     */
    public function getOwnerFile()
    {
        return $this->parentNode->getOwnerFile();
    }

    public function getOffset()
    {
        return $this->valueOffset;
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}