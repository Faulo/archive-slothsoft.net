<?php
namespace Slothsoft\Amber;

use Slothsoft\CMS\HTTPFile;
use Slothsoft\CMS\HTTPRequest;
use Slothsoft\Core\DOMHelper;

class ResourceController {
	private $moduleDir;
	private $locator;
	
	public function __construct(string $moduleDir) {
		assert(strlen($moduleDir) and is_dir($moduleDir));
		
		$this->moduleDir = realpath($moduleDir);
	}
	
	public function resourceAction(HTTPRequest $req) {
		$this->locator = new ResourceLocator($this->moduleDir, $req->getInputValue('game'), $req->getInputValue('mod'));
		$file = null;
		
		if ($id = $req->getInputValue('id')) {
			$file = $this->locator->getResourceById($id);
		}
		if ($type = $req->getInputValue('type') and $name = $req->getInputValue('name')) {
			$file = $this->locator->getResource($type, $name);
		}
		
		return $file
			? DOMHelper::loadDocument($file)
			: null;
	}
	
	public function buildAction(HTTPRequest $req) {
	}
}