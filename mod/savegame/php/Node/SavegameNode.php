<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\Editor;
use DOMDocument;
use DOMElement;
declare(ticks = 1000);

class SavegameNode extends AbstractNode
{

    protected $archiveList = [];

    public function __construct()
    {
        parent::__construct();
        // $this->strucData['defaultDir'] = '';
        // $this->strucData['tempDir'] = '';
        $this->strucData['save-id'] = '';
        $this->strucData['save-mode'] = '';
    }

    public function init(Editor $ownerEditor, DOMElement $strucElement, AbstractNode $parentValue = null)
    {
        parent::init($ownerEditor, $strucElement, $this);
    }

    protected function loadNode()
    {}

    protected function loadChildren()
    {
        parent::loadChildren();
        $this->archiveList = [];
        foreach ($this->childNodeList as $child) {
            if ($child instanceof ArchiveNode) {
                $this->archiveList[$child->getFilename()] = $child;
            }
        }
    }

    public function asNode(DOMDocument $dataDoc)
    {
        $this->updateStrucNode();
        
        return $dataDoc->importNode($this->strucElement, true);
    }

    public function getArchiveByFilename($name)
    {
        $ret = null;
        foreach ($this->childNodeList as $child) {
            if ($child instanceof ArchiveNode) {
                if ($child->getFilename() === $name) {
                    $ret = $child;
                    break;
                }
            }
        }
        return $ret;
    }
}