<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class ArchiveNodeAMBR extends ArchiveNodeJH
{

    public function getArchive()
    {
        $header = [];
        $body = [];
        $maxId = 0;
        foreach ($this->childNodeList as $child) {
            if ($child instanceof FileContainer) {
                $id = (int) $child->getFileName();
                if ($id > $maxId) {
                    $maxId = $id;
                }
                $val = $child->getContent();
                $header[$id] = pack('N', strlen($val));
                $body[$id] = $val;
            }
        }
        for ($id = 1; $id < $maxId; $id++) {
            if (!isset($header[$id])) {
                $header[$id] = pack('N', 0);
                $body[$id] = '';
            }
        }
        ksort($header);
        ksort($body);
        
        array_unshift($header, 'AMBR' . pack('n', count($body)));
        
        $ret = implode('', $header) . implode('', $body);
        return $ret;
    }
}