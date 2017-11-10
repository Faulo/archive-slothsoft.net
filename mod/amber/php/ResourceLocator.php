<?php
namespace Slothsoft\Amber;

use Slothsoft\CMS\HTTPRequest;

class ResourceLocator {
	const TYPE_DATA 			= 'amberdata';
	const TYPE_AMBERFILE 		= 'amberfile';
	const TYPE_GFX 				= 'gfx';
	const TYPE_SAVE 			= 'save';
	const TYPE_CLI				= 'exe';
	const TYPE_GAMEFILE			= 'gamefile';
	const TYPE_MODFOLDER		= 'modfolder';
	
	private static $repository = [
		'structure' => [
			'type' => self::TYPE_GAMEFILE,
			'name' => 'structure.xml',
		],
		'ambtool' => [
			'type' => self::TYPE_CLI,
			'name' => 'ambtool.exe',
		],
		'amgfx' => [
			'type' => self::TYPE_CLI,
			'name' => 'amgfx.exe',
		],
		'amberdata' => [
			'type' => self::TYPE_MODFOLDER,
			'name' => 'amberdata',
		],
		'amberfiles' => [
			'type' => self::TYPE_MODFOLDER,
			'name' => 'amberfiles',
		],
	];
	
	private $moduleDir;
	private $cliDir;
	private $resDir;
	private $gameDir;
	private $modDir;
	
	public function __construct(string $moduleDir, string $game, string $mod) {
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
	
	public function getResource(string $fileType, string $fileName) {
		switch ($fileType) {
			case self::TYPE_DATA:			return $this->modDir . DIRECTORY_SEPARATOR . 'amberdata' . DIRECTORY_SEPARATOR . $fileName;
			case self::TYPE_AMBERFILE:		return $this->modDir . DIRECTORY_SEPARATOR . 'amberfiles' . DIRECTORY_SEPARATOR . $fileName;
			case self::TYPE_GFX:			return $this->modDir . DIRECTORY_SEPARATOR . 'ambergraphics' . DIRECTORY_SEPARATOR . $fileName;
			case self::TYPE_SAVE:			return $this->modDir . DIRECTORY_SEPARATOR . 'savegames' . DIRECTORY_SEPARATOR . $fileName;
			
			case self::TYPE_CLI:			return $this->cliDir . DIRECTORY_SEPARATOR . $fileName;
			
			case self::TYPE_MODFOLDER:		return $this->modDir . DIRECTORY_SEPARATOR . $fileName;
			case self::TYPE_GAMEFILE:		return $this->gameDir . DIRECTORY_SEPARATOR . $fileName;
		}
	}
	
	public function getResourceById(string $id) {
		assert('isset(self::$repository[$id])');
		
		return $this->getResource(self::$repository[$id]['type'], self::$repository[$id]['name']);
	}
	
}