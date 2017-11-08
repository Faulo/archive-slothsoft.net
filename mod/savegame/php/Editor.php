<?php
namespace Slothsoft\Savegame;

use Slothsoft\CMS\HTTPFile;
use Slothsoft\Core\DOMHelper;
use DOMDocument;
use DOMElement;
use Exception;
declare(ticks = 1000);

class Editor
{

    protected $config = [
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

    protected $strucDoc;

    protected $savegame;

    protected $dictionaryList = [];

    protected $globalList = [];

    protected $archiveList = [];

    /**
     *
     * @var \Slothsoft\Savegame\Node\AbstractValueContent[];
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
            throw new Exception('Missing directory for: default saves');
        }
        if (! $this->config['tempDir']) {
            throw new Exception('Missing directory for: temp saves');
        }
        if (! $this->config['mode']) {
            throw new Exception('Missing editor mode');
        }
        if (! $this->config['id']) {
            $this->config['id'] = md5(time());
        }
    }

    public function load($strucFile)
    {
        $this->strucDoc = DOMHelper::loadDocument($strucFile);
        
        if (! ($this->strucDoc and $this->strucDoc->documentElement)) {
            throw new Exception('Structure document is empty');
        }
        
        $strucElement = $this->strucDoc->documentElement;
        
        // $strucElement->setAttribute('save-id', $this->config['id']);
        // $strucElement->setAttribute('save-mode', $this->config['mode']);
        
        // $this->globalList = [];
        // $this->dictionaryList = [];
        
        $strucData = [];
        $strucData['save-id'] = $this->config['id'];
        $strucData['save-mode'] = $this->config['mode'];
        
        $this->savegame = $this->createNode(null, $strucElement, $strucElement->tagName, $strucData);
        
        /*
         * foreach ($strucElement->childNodes as $node) {
         * if ($node->nodeType === XML_ELEMENT_NODE) {
         * switch ($node->localName) {
         * case 'global':
         * $this->globalList[$node->getAttribute('global-id')] = $node;
         * break;
         * case 'dictionary':
         * $dictionary = [];
         * foreach ($node->childNodes as $optionNode) {
         * if ($optionNode->nodeType === XML_ELEMENT_NODE) {
         * if (! $optionNode->hasAttribute('key')) {
         * $optionNode->setAttribute('key', count($dictionary));
         * }
         * $dictionary[$optionNode->getAttribute('key')] = $optionNode->getAttribute('val');
         * }
         * }
         * $this->dictionaryList[$node->getAttribute('dictionary-id')] = $dictionary;
         * break;
         * }
         * }
         * }
         *
         * $this->savegame = new Node\SavegameNode();
         * $this->savegame->init($this, $strucElement, null);
         * //
         */
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
        $this->strucDoc->formatOutput = true;
        return HTTPFile::createFromDocument($this->strucDoc, sprintf('savegame.%s.xml', $this->config['id']));
    }

    public function getDocument()
    {
        return $this->strucDoc;
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
     *
     * @param \Slothsoft\Savegame\Node\AbstractNode $parentValue
     * @param \DOMElement $strucElement
     * @return NULL|\Slothsoft\Savegame\Node\AbstractNode
     */
    public function createNode(Node\AbstractNode $parentValue = null, DOMElement $strucElement, string $tagName, array $strucData)
    {
        $ret = null;
        if ($value = $this->constructValue($tagName)) {
            if ($value->init($this, $strucElement, $parentValue, $tagName, $strucData)) {
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
                throw new Exception(sprintf('unknown tag: "%s"', $tagName));
        }
        return null;
    }
}