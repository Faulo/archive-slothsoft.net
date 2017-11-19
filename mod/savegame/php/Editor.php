<?php
namespace Slothsoft\Savegame;

use Slothsoft\CMS\HTTPFile;
use Slothsoft\Core\DOMHelper;
use DOMDocument;
use DOMElement;
use DomainException;
use RuntimeException;
use UnexpectedValueException;
declare(ticks = 1000);

class Editor
{

    protected $config = [
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

    protected $dom;

    protected $savegame;

    protected $dictionaryList = [];

    protected $globalList = [];

    protected $archiveList = [];
    
    /**
     * @var \Slothsoft\Savegame\EditorElement[]
     */
    protected $elementRepository = [];

    /**
     *
     * @var \Slothsoft\Savegame\Node\AbstractValueContent[]
     */
    protected $valueList = [];

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
    
    private function loadRepository($structureFile) {
        $this->elementRepository = [];
        
        $strucDoc = $this->dom->load($structureFile);
        
        if (! ($strucDoc and $strucDoc->documentElement)) {
            throw new UnexpectedValueException('Structure document is empty');
        }
        
        $structureIds = [];
        
        $nodeList = $strucDoc->getElementsByTagName('*');
        foreach ($nodeList as $node) {
            $id = $this->loadRepositoryId($node, $structureIds);
            $type = $node->localName;
            $attributes = [];
            foreach ($node->attributes as $attr) {
                $attributes[$attr->name] = $attr->value;
            }
            $children = [];
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType === XML_ELEMENT_NODE) {
                    $children[] = $this->loadRepositoryId($childNode, $structureIds);
                }
            }
            
            $this->elementRepository[$id] = new EditorElement($type, $attributes, $children);
        }
    }
    private function loadRepositoryId(DOMElement $node, array &$repositoryIds) {
        $id = array_search($node, $repositoryIds, true);
        if ($id === false) {
            $id = count($repositoryIds);
            $repositoryIds[$id] = $node;
        }
        return $id;
    }
    public function load()
    {
        $this->loadRepository($this->config['structureFile']);
        
        $rootElement = $this->elementRepository[0]->clone(
            null, [
                'save-id' => $this->config['id'],
                'save-mode' => $this->config['mode'],
            ]
        );
        
        $this->savegame = $this->createNode(null, $rootElement);
    }

    public function getDictionaryById($id)
    {
        return isset($this->dictionaryList[$id]) ? $this->dictionaryList[$id] : null;
    }

    public function getGlobalById($id)
    {
        return isset($this->globalList[$id]) ? $this->globalList[$id] : null;
    }

    public function getArchiveById($id)
    {
        return isset($this->archiveList[$id]) ? $this->archiveList[$id] : null;
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
        return $this->savegame->updateContent();
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
                if (isset($this->valueList[$id])) {
                    $value = $this->valueList[$id];
                    // printf('%s: %s => %s%s', $id, $value->getValue(), $val, PHP_EOL);
                    $value->setValue($val);
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
		$editorDoc = $this->asDocument();
        $editorDoc->formatOutput = true;
        return HTTPFile::createFromDocument($editorDoc, sprintf('savegame.%s.xml', $this->config['id']));
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

    public function registerDictionary(Node\DictionaryNode $node)
    {
        $id = $node->getDictionaryId();
        $this->dictionaryList[$id] = $node;
    }

    public function registerGlobal(Node\GlobalNode $node)
    {
        $id = $node->getGlobalId();
        $this->globalList[$id] = $node;
    }

    public function registerArchive(Node\ArchiveNode $node)
    {
        $id = $node->getArchiveId();
        $this->archiveList[$id] = $node;
    }

    public function registerValue(Node\AbstractValueContent $node)
    {
        $id = count($this->valueList);
        $node->setValueId($id);
        $this->valueList[$id] = $node;
    }

    /**
     *
     * @param string $name
     * @return NULL|\Slothsoft\Savegame\Node\AbstractValueContent
     */
    public function getValueByName(string $name)
    {
        foreach ($this->valueList as $value) {
            if ($value->getName() === $name) {
                return $value;
            }
        }
    }
    
    /**
     * @param string $id
     * @return \Slothsoft\Savegame\EditorElement
     */
    public function getElementById(string $id) : EditorElement {
        assert(isset($this->elementRepository[$id]));
        return $this->elementRepository[$id];
    }

    /**
     *
     * @param \Slothsoft\Savegame\Node\AbstractNode $parentValue
     * @param \Slothsoft\Savegame\EditorElement $strucElement
     * @return NULL|\Slothsoft\Savegame\Node\AbstractNode
     */
    public function createNode(Node\AbstractNode $parentValue = null, EditorElement $strucElement)
    {
        $ret = null;
        if ($value = $this->constructValue($strucElement->getType())) {
            if ($value->init($this, $strucElement, $parentValue)) {
                if ($value instanceof Node\DictionaryNode) {
                    $this->registerDictionary($value);
                }
                if ($value instanceof Node\GlobalNode) {
                    $this->registerGlobal($value);
                }
                if ($value instanceof Node\ArchiveNode) {
                    $this->registerArchive($value);
                }
                if ($value instanceof Node\AbstractValueContent) {
                    $this->registerValue($value);
                }
                $ret = $value;
            }
        }
        return $ret;
    }

    protected function constructValue($tagName)
    {
        switch ($tagName) {
            // root
            case 'savegame.editor':
                return new Node\SavegameNode();
            case 'archive':
                return new Node\ArchiveNode();
            case 'global':
                return new Node\GlobalNode();
            case 'dictionary':
                return new Node\DictionaryNode();
            
            // values
            case 'integer':
                return new Node\IntegerValue();
            case 'signed-integer':
                return new Node\SignedIntegerValue();
            case 'string':
                return new Node\StringValue();
            case 'bit':
                return new Node\BitValue();
            case 'select':
                return new Node\SelectValue();
            case 'event-script':
                return new Node\EventScriptValue();
            case 'binary':
                return new Node\BinaryValue();
            
            // containers
            case 'group':
                return new Node\GroupContainer();
            case 'file':
                return new Node\FileContainer();
            
            // instructions
            case 'bit-field':
                return new Node\BitFieldInstruction();
            case 'string-dictionary':
                return new Node\StringDictionaryInstruction();
            case 'event-dictionary':
                return new Node\EventDictionaryInstruction();
            case 'event':
                return new Node\EventInstruction();
            case 'event-step':
                return new Node\EventStepInstruction();
            case 'repeat-group':
                return new Node\RepeatGroupInstruction();
            case 'use-global':
                return new Node\UseGlobalInstruction();
            
            default:
                throw new DomainException(sprintf('unknown tag: "%s"', $tagName));
        }
        return null;
    }
}