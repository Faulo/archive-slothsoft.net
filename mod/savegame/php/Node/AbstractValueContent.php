<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

abstract class AbstractValueContent extends AbstractContentNode
{

    abstract protected function decodeValue();

    abstract protected function encodeValue();

    protected $rawValue;

    public function __construct()
    {
        parent::__construct();
        $this->strucData['size'] = '1';
        $this->strucData['value'] = '';
        $this->strucData['value-id'] = '';
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        $this->strucData['size'] = $this->parseInt($this->strucData['size']);
    }

    protected function loadContent()
    {
        if ($this->strucData['size'] and $this->ownerFile) {
            $this->setRawValue($this->ownerFile->extractContent($this->valueOffset, $this->strucData['size']));
        }
    }

    public function setValueId($id)
    {
        $this->strucData['value-id'] = $id;
    }

    public function getValueId()
    {
        return $this->strucData['value-id'];
    }

    public function setValue($value)
    {
        $this->strucData['value'] = $value;
        $this->rawValue = $this->encodeValue();
    }

    public function getValue()
    {
        return $this->strucData['value'];
    }

    public function setRawValue($value)
    {
        $this->rawValue = $value;
        $this->strucData['value'] = $this->decodeValue();
    }

    public function getRawValue()
    {
        return $this->rawValue;
    }

    public function updateContent()
    {
        if ($this->strucData['size']) {
            $this->ownerFile->insertContent($this->valueOffset, $this->strucData['size'], $this->rawValue);
        }
        parent::updateContent();
    }
}
