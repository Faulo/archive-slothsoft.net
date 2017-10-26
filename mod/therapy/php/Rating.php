<?php
namespace Slothsoft\Therapy;

use DOMElement;
use DOMXPath;

class Rating
{

    protected $node;

    protected $document;

    protected $xpath;

    protected $goalList;

    public function __construct(DOMXPath $xpath, DOMElement $node)
    {
        $this->xpath = $xpath;
        $this->node = $node;
        $this->document = $this->node->ownerDocument;
    }

    public function clearEmpty()
    {
        if ($this->isEmpty()) {
            $this->node->parentNode->removeChild($this->node);
        }
    }

    public function isEmpty()
    {
        return ! (bool) strlen($this->getValue());
    }

    public function getNode()
    {
        return $this->node;
    }

    public function getValue()
    {
        return $this->node->getAttribute('value');
    }

    public function setValue($value)
    {
        $this->node->setAttribute('value', $value);
    }

    public function setData(array $data)
    {
        if (isset($data['value'])) {
            $this->setValue($data['value']);
        }
    }
}