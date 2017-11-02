<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

abstract class AbstractInstructionContent extends AbstractContentNode
{

    abstract protected function loadInstruction();

    /**
     * @var \DOMElement[]
     */
    protected $instructionElements;

    protected $dictionaryOptions;

    public function __construct()
    {
        parent::__construct();
        $this->strucData['dictionary-ref'] = '';
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        $this->dictionaryOptions = $this->ownerEditor->getDictionaryById($this->strucData['dictionary-ref']);
    }

    protected function loadContent()
    {
        $this->loadInstruction();
    }

    protected function loadChildren()
    {}

    protected function getInstructionContent()
    {
        $ret = null;
        if ($this->strucElement->hasChildNodes()) {
            $doc = $this->strucElement->ownerDocument;
            $ret = $doc->createDocumentFragment();
            foreach ($this->strucElement->childNodes as $childNode) {
                $ret->appendChild($childNode->cloneNode(true));
            }
        }
        return $ret;
    }

    protected function createInstructionContainer()
    {
        return $this->createInstructionElement('group', [
            'name' => $this->strucData['name'],
            'position' => $this->strucData['position'],
            'type' => $this->strucElement->localName
        ]);
    }

    protected function createInstructionElement($tagName, array $attributes)
    {
        $doc = $this->strucElement->ownerDocument;
        $instructionElement = $doc->createElementNS($this->strucElement->namespaceURI, $tagName);
        foreach ($attributes as $key => $val) {
            $instructionElement->setAttribute($key, $val);
        }
        return $instructionElement;
    }

    /**
     * @return \DOMElement[]
     */
    public function getInstructionElements()
    {
        return $this->instructionElements;
    }
}
