<?php
namespace Slothsoft\Amber;

class ResourceLocator
{

    const TYPE_LIBRARY = 'lib';

    const TYPE_SOURCE = 'src';

    const TYPE_GRAPHIC = 'gfx';

    const TYPE_USERFILE = 'user';

    const TYPE_CLI = 'exe';

    const TYPE_GAMEFILE = 'gamefile';

    const TYPE_GAMEFOLDER = 'gamefolder';

    const TYPE_MODFILE = 'modfile';

    const TYPE_MODFOLDER = 'modfolder';

    const TYPE_STRUCTURE = 'structure';

    const TYPE_TEMPLATE = 'template';

    private static $repository = [
        'structure' => [
            'type' => self::TYPE_STRUCTURE,
            'name' => 'structure'
        ],
        'ambtool' => [
            'type' => self::TYPE_CLI,
            'name' => 'ambtool.exe'
        ],
        'ambgfx' => [
            'type' => self::TYPE_CLI,
            'name' => 'amgfx.exe'
        ],
        'amberdata' => [
            'type' => self::TYPE_MODFOLDER,
            'name' => 'amberdata'
        ],
        'amberfiles' => [
            'type' => self::TYPE_MODFOLDER,
            'name' => 'amberfiles'
        ],
        'items' => [
            'type' => self::TYPE_LIBRARY,
            'name' => 'items'
        ],
        'portraits' => [
            'type' => self::TYPE_LIBRARY,
            'name' => 'portraits'
        ],
        'graphics' => [
            'type' => self::TYPE_LIBRARY,
            'name' => 'graphics'
        ]
    ];

    private $moduleDir;

    private $cliDir;

    private $resDir;

    private $gameDir;

    private $modDir;

    public function __construct(string $moduleDir, string $game, string $mod)
    {
        assert(strlen($moduleDir) and is_dir($moduleDir));
        assert(strlen($game));
        assert(strlen($mod));
        
        $this->moduleDir = realpath($moduleDir);
        $this->cliDir = $this->moduleDir . DIRECTORY_SEPARATOR . 'cli';
        assert(is_dir($this->cliDir));
        
        $this->resDir = $this->moduleDir . DIRECTORY_SEPARATOR . 'res';
        assert(is_dir($this->resDir));
        
        $this->gameDir = $this->resDir . DIRECTORY_SEPARATOR . $game;
        assert(is_dir($this->gameDir));
        
        $this->modDir = $this->gameDir . DIRECTORY_SEPARATOR . $mod;
        assert(is_dir($this->modDir));
    }

    public function getResource(string $fileType, string $fileName)
    {
        switch ($fileType) {
            case self::TYPE_GAMEFOLDER:
                return $this->ensureFolder($this->gameDir . DIRECTORY_SEPARATOR . $fileName);
            case self::TYPE_MODFOLDER:
                return $this->ensureFolder($this->modDir . DIRECTORY_SEPARATOR . $fileName);
            case self::TYPE_GAMEFILE:
                return $this->ensureParentFolder($this->gameDir . DIRECTORY_SEPARATOR . $fileName);
            case self::TYPE_MODFILE:
                return $this->ensureParentFolder($this->modDir . DIRECTORY_SEPARATOR . $fileName);
            case self::TYPE_CLI:
                return $this->cliDir . DIRECTORY_SEPARATOR . $fileName;
            
            case self::TYPE_STRUCTURE:
                return $this->getResource(self::TYPE_GAMEFILE, $fileName . '.xml');
            case self::TYPE_TEMPLATE:
                return $this->getResource(self::TYPE_GAMEFILE, 'template.' . $fileName . '.xsl');
            
            case self::TYPE_LIBRARY:
                return $this->getResource(self::TYPE_MODFILE, 'lib' . DIRECTORY_SEPARATOR . $fileName . '.xml');
            case self::TYPE_SOURCE:
                return $this->getResource(self::TYPE_MODFILE, 'src' . DIRECTORY_SEPARATOR . $fileName);
            case self::TYPE_GRAPHIC:
                return $this->getResource(self::TYPE_MODFILE, 'gfx' . DIRECTORY_SEPARATOR . $fileName . '.png');
        }
    }

    public function getResourceById(string $id)
    {
        assert('isset(self::$repository[$id])');
        
        return $this->getResource(self::$repository[$id]['type'], self::$repository[$id]['name']);
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