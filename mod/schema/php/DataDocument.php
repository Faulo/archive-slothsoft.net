<?php
namespace Schema;

use DOMXPath;
use DOMElement;

class DataDocument
{

    protected $schema = [
        'name' => '',
        'attributes' => [],
        'elements' => []
    ];

    protected $dataXPath;

    protected $dataNode;

    protected $elementList = [];

    public function __construct(DOMXPath $dataXPath, array $schema, DOMDocument $dataNode)
    {
        foreach ($this->schema as $key => &$val) {
            if (isset($schema[$key])) {
                $val = $schema[$key];
            }
        }
        unset($val);
        
        $this->dataXPath = $dataXPath;
        $this->dataNode = $dataNode;
        
        foreach ($this->schema['elements'] as $childSchema) {
            $childNodeList = $this->dataXPath->evaluate($childSchema['name'], $this->dataNode);
            foreach ($childNodeList as $childNode) {
                $this->elementList[] = new DataElement($this->dataXPath, $childSchema, $childNode);
            }
            $childNode = $this->dataNode->ownerDocument->createElementNS($this->dataNode->namespaceURI, $childSchema['name']);
            $this->dataNode->appendChild($childNode);
            $this->elementList[] = new DataElement($this->dataXPath, $childSchema, $childNode);
        }
    }
}