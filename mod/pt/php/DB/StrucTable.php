<?php
namespace Slothsoft\PT\DB;

class StrucTable extends Table
{

    protected function install()
    {
        $sqlCols = [
            'id' => 'int NOT NULL AUTO_INCREMENT',
            // 'parent_id' => 'int NOT NULL',
            'type' => 'int NOT NULL',
            'name' => 'VARCHAR(256) NULL',
            'options' => 'text NULL'
        ];
        $sqlKeys = [
            'id',
            // 'parent_id',
            'type',
            'name'
        ];
        $this->dbmsTable->createTable($sqlCols, $sqlKeys);
    }

    protected function castList(array &$dataList)
    {
        foreach ($dataList as &$data) {
            $data['id'] = (int) $data['id'];
            // $data['parent_id'] = (int) $data['parent_id'];
            $data['type'] = (int) $data['type'];
            if ($data['options'] !== null) {
                $data['options'] = json_decode($data['options'], true);
            }
        }
        unset($data);
        return $dataList;
    }

    protected function cast(array &$data)
    {
        $data['id'] = (int) $data['id'];
        // $data['parent_id'] = (int) $data['parent_id'];
        $data['type'] = (int) $data['type'];
        if ($data['options'] !== null) {
            $data['options'] = json_decode($data['options'], true);
        }
        return $data;
    }

    public function createStruc($type, $name, $options)
    {
        $data = [];
        $data['type'] = $type;
        $data['name'] = $name;
        $data['options'] = $options;
        // $data['parent_id'] = $parentId;
        $id = $this->dbmsTable->insert($data);
        $data['id'] = $id;
        return $this->cast($data);
    }

    public function updateStrucOptions($id, $options)
    {
        $this->dbmsTable->update([
            'options' => $options
        ], $id);
    }

    public function getStrucList()
    {
        $dataList = $this->dbmsTable->select();
        return $this->castList($dataList);
    }
}