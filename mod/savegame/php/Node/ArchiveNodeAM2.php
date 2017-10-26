<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class ArchiveNodeAM2 extends ArchiveNode
{

    protected function loadArchive()
    {
        copy($this->filePath, $this->tempDir . DIRECTORY_SEPARATOR . '1');
    }
}