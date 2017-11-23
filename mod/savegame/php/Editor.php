<?php
namespace Slothsoft\Savegame;

use Slothsoft\CMS\HTTPFile;
use Slothsoft\Core\DOMHelper;
use DOMDocument;
use DOMElement;
use DomainException;
use RuntimeException;
use UnexpectedValueException;
use DS\Vector;
use Slothsoft\Savegame\Node\FileContainer;
use Slothsoft\Savegame\Node\AbstractValueContent;
use Slothsoft\Savegame\Node\AbstractNode;

declare(ticks = 1000);

class Editor
{
    private $config = [
        'structureFile' => '',
        'defaultDir' => '',
        'tempDir' => '',
        'id' => '',
        'mode' => '',
        'ambtoolPath' => '',
        'ambgfxPath' => '',
        'loadAllArchives' => false,
        'selectedArchives' => [],
        'uploadedArchives' => []
    ];

    private $dom;

    private $savegame;
	
	private $nodeList;

    public function __construct(array $config = [])
    {
        foreach ($this->config as $key => &$val) {
            if (isset($config[$key])) {
                $val = $config[$key];
            }
        }
        unset($val);
        if (! $this->config['defaultDir']) {
            throw new RuntimeException('Missing directory for: default saves');
        }
        if (! $this->config['tempDir']) {
            throw new RuntimeException('Missing directory for: temp saves');
        }
        if (! $this->config['mode']) {
            throw new RuntimeException('Missing editor mode');
        }
        if (! $this->config['id']) {
            $this->config['id'] = md5(time());
        }
        
        $this->dom = new DOMHelper();
    }
    
    private function loadDocument($structureFile) {
        $strucDoc = $this->dom->load($structureFile);
        
        if (! ($strucDoc and $strucDoc->documentElement)) {
            throw new UnexpectedValueException('Structure document is empty');
        }
        
        return $this->loadDocumentElement($strucDoc->documentElement);
    }
    private function loadDocumentElement(DOMElement $node) {
        $type = EditorElement::getNodeType($node->localName);
        $attributes = [];
        foreach ($node->attributes as $attr) {
            $attributes[$attr->name] = $attr->value;
        }
        $children = [];
        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType === XML_ELEMENT_NODE) {
                $children[] = $this->loadDocumentElement($childNode);
            }
        }
        $children = new Vector($children);
        
        return new EditorElement($type, $attributes, $children);
    }
    public function load()
    {
        $rootElement = $this->loadDocument($this->config['structureFile']);
        
        $rootElement->setAttribute('save-id', $this->config['id']);
        $rootElement->setAttribute('save-mode', $this->config['mode']);
        
		$this->nodeList = new Vector();
        $this->savegame = $this->createNode(null, $rootElement);
    }
    
    public function getNodeListByParentNode(AbstractNode $parentNode) {
        $ret = [];
        foreach ($this->nodeList as $node) {
            if ($node->getParentNode() === $parentNode) {
                $ret[] = $node;
            }
        }
        return new Vector($ret);
    }
    public function getDictionaryById(string $id)
    {
		foreach ($this->nodeList as $node) {
			if ($node instanceof Node\DictionaryNode and $node->getDictionaryId() === $id) {
				return $node;
			}
		}
    }

    public function getGlobalById(string $id)
    {
		foreach ($this->nodeList as $node) {
			if ($node instanceof Node\GlobalNode and $node->getGlobalId() === $id) {
				return $node;
			}
		}
    }

    public function getArchiveById(string $id)
    {
       foreach ($this->nodeList as $node) {
			if ($node instanceof Node\ArchiveNode and $node->getArchiveId() === $id) {
				return $node;
			}
		}
    }
    
    public function getValueById(int $id)
    {
        foreach ($this->nodeList as $node) {
            if ($node instanceof Node\AbstractValueContent and $node->getValueId() === $id) {
                return $node;
            }
        }
    }
    public function getValueByName(string $name, FileContainer $ownerFile = null)
    {
        foreach ($this->nodeList as $node) {
            if ($node instanceof Node\AbstractValueContent and $node->getName() === $name and ($ownerFile === null or $node->getOwnerFile() === $ownerFile)) {
                return $node;
            }
        }
    }

    public function buildDefaultFile($name)
    {
        // return sprintf('%s%s%s.%s', $this->config['defaultDir'], DIRECTORY_SEPARATOR, $this->config['mode'], $name);
        return sprintf('%s%s%s', $this->config['defaultDir'], DIRECTORY_SEPARATOR, $name);
    }

    public function buildTempFile($name)
    {
        return sprintf('%s%s%s.%s', $this->config['tempDir'], DIRECTORY_SEPARATOR, $this->config['id'], $name);
    }

    public function updateContent()
    {
        foreach ($this->nodeList as $node) {
            if ($node instanceof AbstractValueContent) {
                $node->updateContent();
            }
        }
    }

    public function getConfigValue($key)
    {
        return isset($this->config[$key]) ? $this->config[$key] : null;
    }

    public function shouldLoadArchive($name)
    {
        return ($this->config['loadAllArchives'] or isset($this->config['selectedArchives'][$name]));
    }

    public function writeArchiveFile($name)
    {
        $ret = null;
        if ($archive = $this->getArchiveById($name)) {
            $ret = $archive->writeArchive();
        }
        return $ret;
    }

    public function getArchiveFile($name)
    {
        $ret = null;
        if ($archive = $this->getArchiveById($name)) {
            $ret = HTTPFile::createFromString($archive->getArchive(), $name);
        }
        return $ret;
    }

    public function parseRequest(array $req)
    {
        if (isset($req['data'])) {
            foreach ($req['data'] as $id => $val) {
                if ($val === '_checkbox') {
                    $val = isset($req['data'][$id . $val]);
                }
                if ($node = $this->getValueById((int) $id)) {
                    // printf('%s: %s => %s%s', $id, $node->getValue(), $val, PHP_EOL);
                    $node->setValue($val);
                }
            }
        }
    }

    /**
     *
     * @return \Slothsoft\CMS\HTTPFile
     */
    public function asFile()
    {
        
        return HTTPFile::createFromString($this->savegame->asXML(), sprintf('savegame.%s.xml', $this->config['id']));
    }

    public function asDocument()
    {
        $ret = new DOMDocument('1.0', 'UTF-8');
        $ret->appendChild($this->asNode($ret));
        return $ret;
    }

    public function asNode(DOMDocument $dataDoc)
    {
        $retFragment = $dataDoc->createDocumentFragment();
        $retFragment->appendXML($this->savegame->asXML());
        return $retFragment;
    }
    
	public function registerNode(Node\AbstractNode $node)
    {
		if ($node instanceof Node\AbstractValueContent) {
			$node->setValueId(count($this->nodeList));
		}
		$this->nodeList[] = $node;
    }

    /**
     *
     * @param \Slothsoft\Savegame\Node\AbstractNode $parentValue
     * @param \Slothsoft\Savegame\EditorElement $strucElement
     * @return NULL|\Slothsoft\Savegame\Node\AbstractNode
     */
    public function createNode(Node\AbstractNode $parentValue = null, EditorElement $strucElement)
    {
        if ($value = $this->constructValue($strucElement->getType())) {
            $this->registerNode($value);
            $value->init($strucElement, $parentValue);
			return $value;
        }
    }

    protected function constructValue(int $type)
    {
        switch ($type) {
            // root
            case EditorElement::NODE_TYPES['savegame.editor']:
                return new Node\SavegameNode($this);
            case EditorElement::NODE_TYPES['archive']:
                return new Node\ArchiveNode();
            case EditorElement::NODE_TYPES['global']:
                return new Node\GlobalNode();
            case EditorElement::NODE_TYPES['dictionary']:
                return new Node\DictionaryNode();
            
            // values
            case EditorElement::NODE_TYPES['integer']:
                return new Node\IntegerValue();
            case EditorElement::NODE_TYPES['signed-integer']:
                return new Node\SignedIntegerValue();
            case EditorElement::NODE_TYPES['string']:
                return new Node\StringValue();
            case EditorElement::NODE_TYPES['bit']:
                return new Node\BitValue();
            case EditorElement::NODE_TYPES['select']:
                return new Node\SelectValue();
            case EditorElement::NODE_TYPES['event-script']:
                return new Node\EventScriptValue();
            case EditorElement::NODE_TYPES['binary']:
                return new Node\BinaryValue();
            
            // containers
            case EditorElement::NODE_TYPES['group']:
                return new Node\GroupContainer();
            case EditorElement::NODE_TYPES['file']:
                return new Node\FileContainer();
            
            // instructions
            case EditorElement::NODE_TYPES['bit-field']:
                return new Node\BitFieldInstruction();
            case EditorElement::NODE_TYPES['string-dictionary']:
                return new Node\StringDictionaryInstruction();
            case EditorElement::NODE_TYPES['event-dictionary']:
                return new Node\EventDictionaryInstruction();
            case EditorElement::NODE_TYPES['event']:
                return new Node\EventInstruction();
            case EditorElement::NODE_TYPES['event-step']:
                return new Node\EventStepInstruction();
            case EditorElement::NODE_TYPES['repeat-group']:
                return new Node\RepeatGroupInstruction();
            case EditorElement::NODE_TYPES['use-global']:
                return new Node\UseGlobalInstruction();
            
            default:
                throw new DomainException(sprintf('unknown type: "%s"', $type));
        }
        return null;
    }
}