<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class ArchiveNodeAMBR extends ArchiveNodeJH
{

    public function getArchive()
    {
        $header = [];
        $body = [];
        foreach ($this->childNodeList as $child) {
            if ($child instanceof FileContainer) {
                $val = $child->getContent();
                $header[] = pack('N', strlen($val));
                $body[] = $val;
            }
        }
        array_unshift($header, 'AMBR' . pack('n', count($body)));
        
        $ret = implode('', $header) . implode('', $body);
        return $ret;
    }
}