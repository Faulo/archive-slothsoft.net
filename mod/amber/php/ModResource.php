<?php
namespace Slothsoft\Amber;

use Slothsoft\CMS\HTTPFile;
use DomainException;

class ModResource extends HTTPFile
{

    const TYPE_SOURCE = 1;

    const TYPE_LIBRARY = 2;

    const TYPE_GRAPHIC = 3;

    const TYPE_STYLESHEET = 4;

    const TYPE_USERFILE = 5;
    
    const TYPE_EDITOR = 6;

    const TYPE_MODFILE = 8;

    const TYPE_MODFOLDER = 9;

    const TYPE_STRUCTURE = 11;

    const TYPE_TEMPLATE = 12;

    const TYPE_GAMEFILE = 18;

    const TYPE_GAMEFOLDER = 19;
    
    const TYPE_CLI = 21;

    const DIR_CLI = 'cli';

    const DIR_RESOURCES = 'res';

    const DIR_LIBRARY = 'lib';

    const DIR_SOURCE = 'src';

    const DIR_GRAPHIC = 'gfx';
    
    const DIR_STYLESHEET = 'style';
    
    const DIR_EDITOR = 'editor';

    protected $url;

    /**
     *
     * @param string $baseDir
     * @param string $game
     * @param string $mod
     * @param string $type
     * @param string $name
     * @return \Slothsoft\Amber\ModResource
     */
    public static function createFromMod(string $baseDir, string $game, string $mod, string $type, string $name)
    {
        switch ($type) {
            case self::TYPE_CLI:
                $fileArgs = [
                    $baseDir,
                    self::DIR_CLI,
                    $name
                ];
                break;
            case self::TYPE_GAMEFOLDER:
            case self::TYPE_GAMEFILE:
                $fileArgs = [
                    $baseDir,
                    self::DIR_RESOURCES,
                    $game,
                    $name
                ];
                break;
            case self::TYPE_STRUCTURE:
                $fileArgs = [
                    $baseDir,
                    self::DIR_RESOURCES,
                    $game,
                    $name . '.xml'
                ];
                break;
            case self::TYPE_TEMPLATE:
                $fileArgs = [
                    $baseDir,
                    self::DIR_RESOURCES,
                    $game,
                    'template.' . $name . '.xsl'
                ];
                break;
            case self::TYPE_MODFOLDER:
            case self::TYPE_MODFILE:
                $fileArgs = [
                    $baseDir,
                    self::DIR_RESOURCES,
                    $game,
                    $mod,
                    $name
                ];
                break;
            case self::TYPE_EDITOR:
                $fileArgs = [
                $baseDir,
                self::DIR_RESOURCES,
                $game,
                $mod,
                self::DIR_EDITOR,
                $name . '.xml'
                    ];
                break;
            case self::TYPE_LIBRARY:
                $fileArgs = [
                $baseDir,
                self::DIR_RESOURCES,
                $game,
                $mod,
                self::DIR_LIBRARY,
                $name . '.xml'
                    ];
                break;
            case self::TYPE_SOURCE:
                $fileArgs = [
                    $baseDir,
                    self::DIR_RESOURCES,
                    $game,
                    $mod,
                    self::DIR_SOURCE,
                    $name
                ];
                break;
            case self::TYPE_GRAPHIC:
                $fileArgs = [
                    $baseDir,
                    self::DIR_RESOURCES,
                    $game,
                    $mod,
                    self::DIR_GRAPHIC,
                    $name . '.png'
                ];
                break;
            case self::TYPE_STYLESHEET:
                $fileArgs = [
                    $baseDir,
                    self::DIR_RESOURCES,
                    $game,
                    $mod,
                    self::DIR_STYLESHEET,
                    $name . '.css'
                ];
                break;
            default:
                throw new DomainException('unknown ModResource type: ' . $type);
        }
        $filePath = implode(DIRECTORY_SEPARATOR, $fileArgs);
        $fileName = basename($filePath);
        $url = '/getData.php/amber/mod.resource?' . http_build_query([
            'game' => $game,
            'mod' => $mod,
            'type' => $type,
            'name' => $name
        ]);
        return new ModResource($filePath, $fileName, $url);
    }

    protected function __construct($filePath, $fileName = null, $url = null)
    {
        $this->url = $url;
        parent::__construct($filePath, $fileName);
    }

    public function getUrl()
    {
        return $this->url;
    }
	
	public function exists() {
		return file_exists($this->getPath());
	}
	
	public function getChangeTime() {
		return filemtime($this->getPath());
	}
    
    public function setContents(string $content)
    {
        $folder = dirname($this->getPath());
        if (! file_exists($folder)) {
            mkdir($folder, 0777, true);
        }
        return parent::setContents($content);
    }
}