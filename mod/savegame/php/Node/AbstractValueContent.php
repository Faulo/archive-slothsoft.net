<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

abstract class AbstractValueContent extends AbstractContentNode
{

    abstract protected function decodeValue();

    abstract protected function encodeValue();
    
    private $valueId;
    protected $size;
    protected $value;
    protected $rawValue;

    protected function getXmlAttributes() : string
    {
        $ret = parent::getXmlAttributes();
        $ret .= " size='$this->size' value-id='$this->valueId'";
        $ret .= is_int($this->value)
            ? " value='$this->value'"
            : " value=\"" . htmlspecialchars($this->value, ENT_COMPAT | ENT_XML1) . "\"";
        return $ret;
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->size = $this->loadIntegerAttribute('size', 1);
    }

    protected function loadContent()
    {
        if ($this->size and $this->getOwnerFile()) {
            $this->setRawValue($this->getOwnerFile()->extractContent($this->getOffset(), $this->size));
        }
        //echo $this->getName() . ': ' . $this->getValue() . PHP_EOL;
    }

    public function setValueId(int $id)
    {
        $this->valueId = $id;
    }

    public function getValueId() : int
    {
        return $this->valueId;
    }

    public function setValue($value)
    {
        $this->value = $value;
        $this->rawValue = $this->encodeValue();
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setRawValue($value)
    {
        $this->rawValue = $value;
        $this->value = $this->decodeValue();
    }

    public function getRawValue()
    {
        return $this->rawValue;
    }

    public function updateContent()
    {
        if ($this->size) {
            $this->getOwnerFile()->insertContent($this->valueOffset, $this->size, $this->rawValue);
        }
    }
}
