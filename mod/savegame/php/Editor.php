<?php
namespace Slothsoft\Savegame;

use Slothsoft\Core\DOMHelper;
use Slothsoft\CMS\HTTPFile;
use Slothsoft\CMS\HTTPRequest;
use Exception;
use DOMElement;
use DOMDocument;
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

    protected $valueList = [];

    protected $dictionary = null;

    protected $strucDoc;

    protected $savegame;

    protected $globalList = [];

    protected $dictionaryList = [];

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

    public function setDictionary(Dictionary $dictionary)
    {
        $this->dictionary = $dictionary;
    }

    public function load($strucFile)
    {
        $this->strucDoc = DOMHelper::loadDocument($strucFile);
        
        if (! ($this->strucDoc and $this->strucDoc->documentElement)) {
            throw new Exception('Structure document is empty');
        }
        
        $strucElement = $this->strucDoc->documentElement;
        
        $strucElement->setAttribute('save-id', $this->config['id']);
        $strucElement->setAttribute('save-mode', $this->config['mode']);
        
        $this->globalList = [];
        $this->dictionaryList = [];
        foreach ($strucElement->childNodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                switch ($node->localName) {
                    case 'global':
                        $this->globalList[$node->getAttribute('global-id')] = $node;
                        break;
                    case 'dictionary':
                        $dictionary = [];
                        foreach ($node->childNodes as $optionNode) {
                            if ($optionNode->nodeType === XML_ELEMENT_NODE) {
                                if (! $optionNode->hasAttribute('key')) {
                                    $optionNode->setAttribute('key', count($dictionary));
                                }
                                $dictionary[$optionNode->getAttribute('key')] = $optionNode->getAttribute('val');
                            }
                        }
                        $this->dictionaryList[$node->getAttribute('dictionary-id')] = $dictionary;
                        break;
                }
            }
        }
        
        $this->savegame = new Node\SavegameNode();
        $this->savegame->init($this, $strucElement, null);
        $this->savegame->updateStrucNode();
    }

    public function getGlobalById($id)
    {
        return isset($this->globalList[$id]) ? $this->globalList[$id] : null;
    }

    public function getDictionaryById($id)
    {
        return isset($this->dictionaryList[$id]) ? $this->dictionaryList[$id] : [];
    }

    public function getArchiveById($name)
    {
        return $this->savegame->getArchiveByFilename($name);
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
        return $this->savegame->asNode($dataDoc);
    }

    public function registerValue(Node\AbstractValueContent $value)
    {
        $id = $value->getValueId();
        if (! $id) {
            $id = count($this->valueList);
        }
        $value->setValueId($id);
        $this->valueList[$id] = $value;
    }

    public function createNodes(Node\AbstractNode $parentNode, array $strucElementList)
    {
        $ret = [];
        foreach ($strucElementList as $strucElement) {
            if ($node = $this->createNode($parentNode, $strucElement)) {
                if ($node instanceof Node\AbstractInstructionContent) {
                    $ret = array_merge($ret, $this->createNodes($parentNode, $node->getInstructionElements()));
                } else {
                    $ret[] = $node;
                }
            }
        }
        return $ret;
    }

    protected function createNode(Node\AbstractNode $parentValue, DOMElement $strucElement)
    {
        $ret = null;
        if ($value = $this->constructValue($strucElement->localName, $strucElement->getAttribute('type'))) {
            if ($value->init($this, $strucElement, $parentValue)) {
                if ($value instanceof Node\AbstractValueContent) {
                    $this->registerValue($value);
                }
                $ret = $value;
            }
        }
        return $ret;
    }

    protected function constructValue($tagName, $type)
    {
        switch ($tagName) {
            case 'global':
                return null;
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
            case 'for-each-file':
                return new Node\ForEachFileInstruction();
            case 'repeat-group':
                return new Node\RepeatGroupInstruction();
            case 'use-global':
                return new Node\UseGlobalInstruction();
            
            // archive types
            case 'archive':
                switch ($type) {
                    case 'AMBR':
                        return new Node\ArchiveNodeAMBR();
                    case 'JH':
                        return new Node\ArchiveNodeJH();
                    case 'Raw':
                        return new Node\ArchiveNodeRaw();
                    case 'AM2':
                        return new Node\ArchiveNodeAM2();
                    default:
                        return new Node\ArchiveNode();
                }
            default:
                throw new Exception(sprintf('unknown tag: "%s"', $tagName));
        }
        return null;
    }
}