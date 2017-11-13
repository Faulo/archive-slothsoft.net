<?php
namespace Slothsoft\Amber;

use Slothsoft\Core\FileSystem;
use Slothsoft\CMS\HTTPFile;
use Slothsoft\CMS\HTTPRequest;
use Slothsoft\CMS\HTTPDocument;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Savegame\Editor;
use Exception;
use Error;

class ModController {
	private $moduleDir;
	private $locator;
	private $dom;
	private $cms;
	
	public function __construct(string $moduleDir) {
		assert(strlen($moduleDir) and is_dir($moduleDir));
		
		$this->moduleDir = realpath($moduleDir);
	}
	
	public function defaultAction(HTTPRequest $req) {
		$ret = null;
		
		assert($req->hasInputValue('game'));
		assert($req->hasInputValue('mod'));
		
		$this->locator = new ResourceLocator($this->moduleDir, $req->getInputValue('game'), $req->getInputValue('mod'));
		$this->dom = new DOMHelper();
		$this->cms = HTTPDocument::instance();
		
		return $ret;
	}
	
	public function resourceAction(HTTPRequest $req) {
		$ret = $this->defaultAction($req);
		
		$file = null;
		if ($id = $req->getInputValue('id')) {
			$file = $this->locator->getResourceById($id);
		}
		if ($type = $req->getInputValue('type') and $name = $req->getInputValue('name')) {
			$file = $this->locator->getResource($type, $name);
		}
		if ($file) {
			assert(file_exists($file));
			$ret = $this->dom->load($file);
		}
		
		return $ret;
	}
	public function editorAction(HTTPRequest $req) {
		$ret = $this->defaultAction($req);
        
		assert($req->hasInputValue('struc'));
		
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
		
        $editorConfig = [];
		$editorConfig['structureFile'] = $this->locator->getResource(ResourceLocator::TYPE_STRUCTURE, $req->getInputValue('struc'));
        $editorConfig['defaultDir'] = $this->locator->getResource(ResourceLocator::TYPE_MODFOLDER, 'src');
        $editorConfig['tempDir'] = $this->locator->getResource(ResourceLocator::TYPE_MODFOLDER, 'user');
        $editorConfig['ambtoolPath'] = $this->locator->getResourceById('ambtool');
        $editorConfig['ambgfxPath'] = $this->locator->getResourceById('ambgfx');
		
        $editorConfig['mode'] = $mode;
        $editorConfig['id'] = $name;
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
	
	private $extractionConfig = [
		'graphics' => [
			'archives' => [
				'Monster_char_data.amb',
			],
		],
		/*
		'items' => [
			'archives' => [
				'AM2_BLIT',
			],
		],
		'maps' => [
			'archives' => [
				'2Map_data.amb',
				'2Map_texts.amb',
			],
		],
		'classes' => [
			'archives' => [
				'AM2_BLIT',
				'CONFIG_THALION',
			],
		],
		'tileset.icon' => [
			'archives' => [
				'Icon_data.amb',
			],
		],
		'monsters' => [
			'archives' => [
				'Monster_char_data.amb',
			],
		],
		//*/
	];
	public function extractAction(HTTPRequest $req) {
		$ret = false;
		
		$this->defaultAction($req);
		
		assert($req->hasInputValue('lib'));
		$lib = $req->getInputValue('lib');
		
		assert(isset($this->extractionConfig[$lib]));
		$config = $this->extractionConfig[$lib];
		
		$resourcePath = $this->locator->getResource(ResourceLocator::TYPE_LIBRARY, $lib);
		
		$req->setInputValue('struc', 'structure');
		$req->setInputValue('save', ['editor' => [ 'archives' => $config['archives']]]);
		
		$editor = $this->editorAction($req);
		
		$dataDoc = $editor->asDocument();
		$templateDoc = $this->locator->getResource(ResourceLocator::TYPE_TEMPLATE, 'extract');
		
		if ($dataDoc and $templateDoc) {
			if ($extractDoc = $this->dom->transform($dataDoc, $templateDoc, ['lib' => $lib])) {
				$ret = $extractDoc->save($resourcePath);
			}
		}
		
		return $ret
			? sprintf('Created resource "%s"!', $resourcePath)
			: sprintf('Failed to create resource "%s"!', $resourcePath);
	}
	public function cronAction(HTTPRequest $req) {
		$gameList = [];
		$gameList[] = 'ambermoon';
		
		$modList = [];
		$modList[] = 'Thalion-v1.05-DE';
		//$modList[] = 'Thalion-v1.06-DE';
		//$modList[] = 'Thalion-v1.07-DE';
		//$modList[] = 'Slothsoft-v1.00-DE';
		
		$libList = array_keys($this->extractionConfig);
		
		foreach ($gameList as $game) {
			$req->setInputValue('game', $game);
			echo $game . PHP_EOL;
			foreach ($modList as $mod) {
				$req->setInputValue('mod', $mod);
				
				$this->defaultAction($req);
				
				$archiveManager = new ArchiveManager($this->locator->getResourceById('ambtool'));
				$graphicsManager = new GraphicsManager($this->locator->getResourceById('ambgfx'));
				
				echo "\t" . $mod . PHP_EOL;
				//*
				echo "\t\tlib" . PHP_EOL;
				foreach ($libList as $lib) {
					$req->setInputValue('lib', $lib);
					
					echo "\t\t\t" . $lib . PHP_EOL;
					try {
						$res = $this->extractAction($req);
					} catch(Exception $e) {
						$res = 'EXCEPTION: ' . $e->getMessage();
					} catch(Error $e) {
						$res = 'ERROR: ' . $e->getMessage();
					}
					
					echo "\t\t\t\t" . $res . PHP_EOL;
				}
				echo PHP_EOL;
				//*/
				echo "\t\tgfx" . PHP_EOL;
				
				$graphicsDoc = $this->dom->load($this->locator->getResourceById('graphics'));
				foreach ($graphicsDoc->getElementsByTagNameNS('http://schema.slothsoft.net/amber/amberdata', 'gfx-archive') as $archiveNode) {
					$archiveName = $archiveNode->getAttribute('file-name');
					$archivePath = $this->locator->getResource(
						ResourceLocator::TYPE_SOURCE,
						$archiveNode->getAttribute('file-path')
					);
					echo "\t\t\t" . basename($archivePath) . PHP_EOL;
					$archiveDir = temp_dir(__CLASS__);
					$archiveManager->extractArchive($archivePath, $archiveDir);
					
					$filePathList = [];
					foreach (FileSystem::scanDir($archiveDir) as $filePath) {
						$filePathList[(int) $filePath] = $archiveDir . DIRECTORY_SEPARATOR . $filePath;
					}
					
					foreach ($archiveNode->childNodes as $fileNode) {
						$options = [];
						foreach ($fileNode->attributes as $attr) {
							$options[$attr->name] = $attr->value;
						}
						
						switch ($fileNode->localName) {
							case 'for-each-file':
								$fileIdList = array_keys($filePathList);
								break;
							case 'file':
								$fileIdList = [$options['id']];
								break;
						}
								
						foreach ($fileIdList as $fileId) {
							if (isset($filePathList[$fileId])) {
								$sourceFile = $filePathList[$fileId];
								$targetFile = $this->locator->getResource(
									ResourceLocator::TYPE_GRAPHIC,
									$archiveName . DIRECTORY_SEPARATOR . sprintf('%03d-%02d', $fileId, $options['palette'])
								);
								
								$res = $graphicsManager->convertGraphic($sourceFile, $targetFile, $options);
								echo "\t\t\t\t" . ($res ? 'OK: ' : 'ERROR: ') . $targetFile . PHP_EOL;
							}
						}
					}
				}
				echo PHP_EOL;
			}
			echo PHP_EOL;
		}
	}
}