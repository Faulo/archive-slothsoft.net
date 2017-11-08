<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

abstract class AbstractContentNode extends AbstractNode
{

    protected $valueOffset = 0;

    abstract protected function loadContent();

    public function __construct()
    {
        parent::__construct();
        $this->strucData['name'] = '';
        $this->strucData['position'] = 0;
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->strucData['position'] = $this->parser->evaluate($this->strucData['position'], $this->ownerFile);
        
        $this->valueOffset = $this->strucData['position'];
        if ($this->parentNode instanceof AbstractContentNode) {
            $this->valueOffset += $this->parentNode->getOffset();
        }
    }

    protected function loadNode()
    {
        $this->loadContent();
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
        return $this->strucData['name'];
    }
}