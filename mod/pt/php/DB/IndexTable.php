<?php
namespace Slothsoft\PT\DB;

class IndexTable extends Table
{

    protected function install()
    {
        $sqlCols = [
            'id' => 'int(11) NOT NULL AUTO_INCREMENT',
            'name' => 'tinytext NOT NULL',
            'uri' => 'text NOT NULL',
            'prefix' => 'tinytext NOT NULL',
            'spec' => 'text NULL'
        ];
        $sqlKeys = [
            'id'
        ];
        $this->dbmsTable->createTable($sqlCols, $sqlKeys);
    }

    protected function castList(array &$dataList)
    {
        foreach ($dataList as &$data) {
            $data['id'] = (int) $data['id'];
        }
        unset($data);
        return $dataList;
    }

    protected function cast(array &$data)
    {
        $data['id'] = (int) $data['id'];
        return $data;
    }

    public function getNamespaceList()
    {
        $dataList = $this->dbmsTable->select();
        return $this->castList($dataList);
    }
}