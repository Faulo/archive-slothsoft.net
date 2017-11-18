<?php
namespace Slothsoft\Amber;

class ModResourceLocator
{

    private static $repository = [
        'structure' => [
            'type' => ModResource::TYPE_STRUCTURE,
            'name' => 'structure'
        ],
        'ambtool' => [
            'type' => ModResource::TYPE_CLI,
            'name' => 'ambtool.exe'
        ],
        'ambgfx' => [
            'type' => ModResource::TYPE_CLI,
            'name' => 'amgfx.exe'
        ],
        'amberfiles' => [
            'type' => ModResource::TYPE_MODFOLDER,
            'name' => 'amberfiles'
        ],
        'items' => [
            'type' => ModResource::TYPE_LIBRARY,
            'name' => 'items'
        ],
        'portraits' => [
            'type' => ModResource::TYPE_LIBRARY,
            'name' => 'portraits'
        ],
        'classes' => [
            'type' => ModResource::TYPE_LIBRARY,
            'name' => 'classes'
        ],
        'graphics' => [
            'type' => ModResource::TYPE_LIBRARY,
            'name' => 'graphics'
        ],
        'monsters' => [
            'type' => ModResource::TYPE_LIBRARY,
            'name' => 'monsters'
        ],
        'npcs' => [
            'type' => ModResource::TYPE_LIBRARY,
            'name' => 'npcs'
        ],
        'pcs' => [
            'type' => ModResource::TYPE_LIBRARY,
            'name' => 'pcs'
        ],
        'amberdata' => [
            'type' => ModResource::TYPE_LIBRARY,
            'name' => 'items'
        ],
        'libs' => [
            'type' => ModResource::TYPE_MODFILE,
            'name' => 'libs.xml'
        ],
        'styles' => [
            'type' => ModResource::TYPE_MODFILE,
            'name' => 'styles.css'
        ]
    ];

    private $moduleDir;

    private $game;

    private $mod;

    public function __construct(string $moduleDir, string $game, string $mod)
    {
        assert(strlen($moduleDir) and is_dir(realpath($moduleDir)));
        assert(strlen($game));
        assert(strlen($mod));
        
        $this->moduleDir = realpath($moduleDir);
        $this->game = $game;
        $this->mod = $mod;
    }

    /**
     *
     * @param string $fileType
     * @param string $fileName
     * @return \Slothsoft\Amber\ModResource
     */
    public function getResource(string $fileType, string $fileName)
    {
        return ModResource::createFromMod($this->moduleDir, $this->game, $this->mod, $fileType, $fileName);
    }

    public function getResourceById(string $id)
    {
        assert('isset(self::$repository[$id])');
        
        return $this->getResource(self::$repository[$id]['type'], self::$repository[$id]['name']);
    }

    public function setResourceContentsById(string $id, string $contents)
    {
        if ($resource = $this->getResourceById($id)) {
            $resource->setContents($contents);
        }
        return $resource;
    }

    private function ensureFolder(string $folder)
    {
        if (! file_exists($folder)) {
            mkdir($folder, 0777, true);
        }
        return $folder;
    }

    private function ensureParentFolder(string $file)
    {
        $this->ensureFolder(dirname($file));
        return $file;
    }
}