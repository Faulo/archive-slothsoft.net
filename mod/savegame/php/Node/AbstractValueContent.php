<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

abstract class AbstractValueContent extends AbstractContentNode
{

    abstract protected function decodeValue(string $rawValue);

    abstract protected function encodeValue($value): string;

    private $valueId;

    protected $size;

    protected $value;

    protected function getXmlAttributes(): string
    {
        return parent::getXmlAttributes() . $this->createXmlIntegerAttribute('position', $this->getContentOffset()) . $this->createXmlTextAttribute('value', (string) $this->value) . $this->createXmlIntegerAttribute('size', $this->size) . $this->createXmlIntegerAttribute('value-id', $this->valueId);
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->size = (int) $strucElement->getAttribute('size', 1, $this->ownerFile);
        $this->valueId = (int) $this->ownerFile->registerValue($this);
    }

    protected function loadContent(EditorElement $strucElement)
    {
        if ($this->size and $this->ownerFile) {
            $this->setRawValue($this->ownerFile->extractContent($this->contentOffset, $this->size));
        }
        // echo $this->getName() . ': ' . $this->getValue() . PHP_EOL;
    }

    public function setValueId(int $id)
    {
        $this->valueId = $id;
    }

    public function getValueId(): int
    {
        return $this->valueId;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setRawValue(string $rawValue)
    {
        $this->value = $this->decodeValue($rawValue);
    }

    public function getRawValue()
    {
        return $this->encodeValue($this->value);
    }

    public function updateContent()
    {
        if ($this->size) {
            $this->ownerFile->insertContent($this->contentOffset, $this->size, $this->getRawValue());
        }
    }
}
