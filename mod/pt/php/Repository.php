<?php
namespace Slothsoft\PT;

use Slothsoft\DBMS\Manager;
use Exception;

class Repository
{

    protected static $instanceList = [];

    public static function getInstance($repositoryName)
    {
        $repositoryName = trim(strtolower($repositoryName));
        if (! strlen($repositoryName)) {
            throw new Exception('Repository name may not be empty.');
        }
        if (! isset(self::$instanceList[$repositoryName])) {
            self::$instanceList[$repositoryName] = new Repository($repositoryName);
        }
        return self::$instanceList[$repositoryName];
    }

    const TABLE_INDEX_NAME = 'index';

    const TABLE_STRUC_NAME = 'struc: %s';

    const TABLE_STRUC_REGEX = '/^struc: (.+)$/';

    const TABLE_DATA_NAME = 'data: %s';

    const TABLE_DATA_REGEX = '/^data: (.+)$/';

    protected $dbmsDB;

    protected $indexTable = null;

    protected $strucTableList = [];

    protected $dataTableList = [];

    protected $documentList = [];

    protected $namespaceData = [];

    protected $namespaceListByURI = [];

    protected $namespaceListById = [];

    protected function __construct($dbName)
    {
        $this->dbmsDB = Manager::getDatabase($dbName);
        $indexTable = $this->getIndexTable();
        $indexTable->init();
        $this->namespaceData[null] = [
            'id' => null,
            'name' => 'null',
            'uri' => '',
            'prefix' => 'null',
            'spec' => null
        ];
        $dataList = $indexTable->getNamespaceList();
        foreach ($dataList as $data) {
            $this->namespaceData[$data['id']] = $data;
        }
    }

    public function asNode(\DOMDocument $dataDoc)
    {
        $retNode = $dataDoc->createElement('repository');
        if ($table = $this->getIndexTable()) {
            $node = $dataDoc->createElement('index');
            $node->setAttribute('name', $table->getTableName());
            $retNode->appendChild($node);
        }
        $tableList = $this->getStrucTableList();
        foreach ($tableList as $table) {
            $node = $dataDoc->createElement('struc');
            $node->setAttribute('name', $table->getTableName());
            $retNode->appendChild($node);
        }
        $tableList = $this->getDataTableList();
        foreach ($tableList as $table) {
            $node = $dataDoc->createElement('data');
            $node->setAttribute('name', $table->getTableName());
            $retNode->appendChild($node);
        }
        return $retNode;
    }

    public function hasDocument($documentName)
    {
        $documentName = trim(strtolower($documentName));
        $table = $this->getDataTable($documentName);
        return $table->exists();
    }

    public function getDocument($documentName)
    {
        $documentName = trim(strtolower($documentName));
        if (! strlen($documentName)) {
            throw new Exception('Document name may not be empty.');
        }
        if (! isset($this->documentList[$documentName])) {
            $this->documentList[$documentName] = $this->constructDocument($documentName);
        }
        
        return $this->documentList[$documentName];
    }

    protected function constructDocument($documentName)
    {
        return new Document($this, $documentName, $this->getDataTable($documentName));
    }

    public function getNamespaceById($namespaceId)
    {
        // $namespaceId = (int) $namespaceId;
        if (! isset($this->namespaceListById[$namespaceId])) {
            if (isset($this->namespaceData[$namespaceId])) {
                $this->constructNS($this->namespaceData[$namespaceId]);
            } else {
                throw new DOMException('NAMESPACE_ERR');
            }
        }
        return $this->namespaceListById[$namespaceId];
    }

    public function getNamespaceByURI($namespaceURI)
    {
        $namespaceURI = (string) $namespaceURI;
        
        if (! isset($this->namespaceListByURI[$namespaceURI])) {
            foreach ($this->namespaceData as $data) {
                if ($data['uri'] === $namespaceURI) {
                    $this->constructNS($data);
                    break;
                }
            }
            if (! isset($this->namespaceListByURI[$namespaceURI])) {
                throw new DOMException('NAMESPACE_ERR');
            }
        }
        return $this->namespaceListByURI[$namespaceURI];
    }

    protected function constructNS(array $data)
    {
        $ns = new NS($this, $data, $this->getStrucTable($data['prefix']));
        $this->namespaceListById[$ns->getId()] = $ns;
        $this->namespaceListByURI[$ns->getURI()] = $ns;
        return $ns;
    }

    public function getIndexTable()
    {
        if (! $this->indexTable) {
            $this->indexTable = new DB\IndexTable($this->dbmsDB->getName(), self::TABLE_INDEX_NAME);
        }
        return $this->indexTable;
    }

    public function getStrucTableList()
    {
        $retList = [];
        $tableList = $this->dbmsDB->getTableList();
        foreach ($tableList as $table) {
            if (preg_match(self::TABLE_STRUC_REGEX, $table, $match)) {
                $retList[] = $this->getStrucTable($match[1]);
            }
        }
        return $retList;
    }

    public function getStrucTable($namespacePrefix)
    {
        $tableName = sprintf(self::TABLE_STRUC_NAME, $namespacePrefix);
        if (! isset($this->strucTableList[$tableName])) {
            $this->strucTableList[$tableName] = new DB\StrucTable($this->dbmsDB->getName(), $tableName);
        }
        return $this->strucTableList[$tableName];
    }

    public function getDataTableList()
    {
        $retList = [];
        $tableList = $this->dbmsDB->getTableList();
        foreach ($tableList as $table) {
            if (preg_match(self::TABLE_DATA_REGEX, $table, $match)) {
                $retList[] = $this->getDataTable($match[1]);
            }
        }
        return $retList;
    }

    public function getDataTable($documentName)
    {
        $tableName = sprintf(self::TABLE_DATA_NAME, $documentName);
        if (! isset($this->dataTableList[$tableName])) {
            $this->dataTableList[$tableName] = new DB\DataTable($this->dbmsDB->getName(), $tableName);
        }
        return $this->dataTableList[$tableName];
    }
}