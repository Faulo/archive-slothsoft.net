<?php
namespace Slothsoft\Amber;

use Slothsoft\CMS\HTTPDocument;
use Slothsoft\CMS\HTTPRequest;
use Slothsoft\Savegame\Editor;
use Exception;

class SavegameController
{

    protected $baseDir;

    public function __construct($baseDir)
    {
        $this->baseDir = $baseDir;
    }

    public function loadEditor(HTTPRequest $req, HTTPDocument $document)
    {
        $editorFilePath = $req->getInputValue('EditorConfig', '/amber/AmbermoonAmberfiles');
        $editorFile = $document->getResourcePath($editorFilePath);
        if (! $editorFilePath) {
            throw new Exception(sprintf('Failed to load EditorConfig "%s"!', $editorFilePath));
        }
        
        $mode = $req->getInputValue('SaveDefault', 'thalion');
        $mode = preg_replace('~[^\w]~', '', $mode);
        $name = $req->getInputValue('SaveName', null);
        $name = preg_replace('~[^\w]~', '', $name);
        
        $loadAll = $req->hasInputValue('LoadAll');
        $saveAll = $req->hasInputValue('SaveAll');
        $downloadAll = $req->hasInputValue('DownloadAll');
        
        $loadFile = $req->getInputValue('LoadFile', null);
        $saveFile = $req->getInputValue('SaveFile', null);
        $downloadFile = $req->getInputValue('DownloadFile', null);
        
        $request = (array) $req->getInputValue('save', []);
        
        $defaultDir = realpath($this->baseDir . '/../res/save/amberfiles');
        $tempDir = realpath($this->baseDir . '/../res/save/temp');
        
        $editorConfig = [];
		$editorConfig['structureFile'] = $editorFile;
        $editorConfig['defaultDir'] = $defaultDir;
        $editorConfig['tempDir'] = $tempDir;
        $editorConfig['mode'] = $mode;
        $editorConfig['id'] = $name;
        $editorConfig['ambtoolPath'] = 'mod\\amber\\cli\\ambtool.exe';
        $editorConfig['ambgfxPath'] = 'mod\\amber\\cli\\amgfx.exe';
        $editorConfig['loadAllArchives'] = ($loadAll or $saveAll or $downloadAll);
        $editorConfig['selectedArchives'] = [];
        $editorConfig['uploadedArchives'] = [];
        
        if (isset($request['editor'])) {
            if (isset($request['editor']['archives'])) {
                foreach ($request['editor']['archives'] as $val) {
                    $editorConfig['selectedArchives'][$val] = true;
                }
            }
        }
        if ($loadFile) {
            $editorConfig['selectedArchives'][$loadFile] = true;
        }
        if ($saveFile) {
            $editorConfig['selectedArchives'][$saveFile] = true;
        }
        if ($downloadFile) {
            $editorConfig['selectedArchives'][$downloadFile] = true;
        }
        
        if (isset($_FILES['save'])) {
            foreach ($_FILES['save']['tmp_name'] as $file => $filepath) {
                if (strlen($filepath) and file_exists($filepath)) {
                    $editorConfig['uploadedArchives'][$file] = $filepath;
                }
            }
        }
        
        $editor = new Editor($editorConfig);
        
        $editor->load();
        
        $editor->parseRequest($request);
        
        return $editor;
    }
}