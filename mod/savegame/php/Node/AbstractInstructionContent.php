<?php
namespace Slothsoft\Savegame\Node;

use Traversable;
use DS\Vector;

declare(ticks = 1000);

abstract class AbstractInstructionContent extends AbstractContentNode
{

    abstract protected function loadInstruction();
    
    protected $dictionary;
    protected $dictionaryRef;
    
    public function getXmlTag()  : string {
        return 'group';
    }
    final protected function getXmlAttributes() : string
    {
        $ret = parent::getXmlAttributes();
        $ret .= " instruction='{$this->getStrucElement()->getTag()}'";
        if ($this->dictionaryRef !== '') {
            $ret .= " dictionary-ref='$this->dictionaryRef'";
        }
        return $ret;
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->dictionaryRef = $this->loadStringAttribute('dictionary-ref');
        
        if ($this->dictionaryRef !== '') {
            $this->dictionary = $this->getOwnerEditor()->getDictionaryById($this->dictionaryRef);
        }
    }

    protected function loadContent()
    {
    }

    protected function loadChildren()
    {
        if ($instructionList = $this->loadInstruction()) {
            foreach ($instructionList as $instruction) {
                $this->loadChild($instruction);
            }
        }
    }
}
