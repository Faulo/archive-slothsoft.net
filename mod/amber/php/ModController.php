<?php
namespace Slothsoft\Amber;

use Slothsoft\CMS\HTTPRequest;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\FileSystem;
use Slothsoft\Savegame\Editor;
use Error;
use Exception;

class ModController
{

    private $moduleDir;

    private $locator;

    private $dom;

    public function __construct(string $moduleDir)
    {
        assert(strlen($moduleDir) and is_dir($moduleDir));
        
        $this->moduleDir = realpath($moduleDir);
    }

    public function defaultAction(HTTPRequest $req)
    {
        $ret = null;
        
        //assert($req->hasInputValue('game'));
        //assert($req->hasInputValue('mod'));
        
        $this->locator = new ModResourceLocator(
			$this->moduleDir,
			$req->getInputValue('game', 'ambermoon'),
			$req->getInputValue('mod', 'Thalion-v1.05-DE')
		);
        $this->dom = new DOMHelper();
        
        return $ret;
    }

    public function resourceAction(HTTPRequest $req)
    {
        $ret = $this->defaultAction($req);
        
        $file = null;
        if ($id = $req->getInputValue('id')) {
            $file = $this->locator->getResourceById($id);
        } elseif ($type = $req->getInputValue('type') and $name = $req->getInputValue('name')) {
            $file = $this->locator->getResource($type, $name);
        } elseif ($name = $req->getInputValue('lib')) {
            $file = $this->locator->getResource(ModResource::TYPE_LIBRARY, $name);
        }
        
        return $file;
    }

    public function editorAction(HTTPRequest $req)
    {
        $ret = $this->defaultAction($req);
        
        //assert($req->hasInputValue('struc'));
        
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
        $editorConfig['structureFile'] = $this->locator->getResource(ModResource::TYPE_STRUCTURE, $req->getInputValue('struc', 'structure'))
            ->getPath();
        $editorConfig['defaultDir'] = $this->locator->getResource(ModResource::TYPE_MODFOLDER, 'src')->getPath();
        $editorConfig['tempDir'] = $this->locator->getResource(ModResource::TYPE_MODFOLDER, 'user')->getPath();
        $editorConfig['ambtoolPath'] = $this->locator->getResourceById('ambtool')->getPath();
        $editorConfig['ambgfxPath'] = $this->locator->getResourceById('ambgfx')->getPath();
        
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
                'Monster_char_data.amb'
            ]
        ],
        'items' => [
            'archives' => [
                'AM2_BLIT',
                'Object_texts.amb'
            ]
        ],
        'portraits' => [
            'archives' => []
        ],
        'maps' => [
            'archives' => [
                '2Map_data.amb',
                '2Map_texts.amb'
            ]
        ],
        'classes' => [
            'archives' => [
                'AM2_BLIT',
                'CONFIG_THALION'
            ]
        ],
        'tileset.icons' => [
            'archives' => [
                'Icon_data.amb'
            ]
        ],
        'pcs' => [
            'archives' => [
                'Party_char.amb'
            ]
        ],
        'npcs' => [
            'archives' => [
                'NPC_char.amb'
            ]
        ],
        'monsters' => [
            'archives' => [
                'Monster_char_data.amb'
            ]
        ]
    
    ];

    public function extractAction(HTTPRequest $req)
    {
        $ret = false;
        
        $this->defaultAction($req);
        
        assert($req->hasInputValue('lib'));
        $lib = $req->getInputValue('lib');
        
        assert(isset($this->extractionConfig[$lib]));
        $config = $this->extractionConfig[$lib];
        
        $libResource = $this->locator->getResource(ModResource::TYPE_LIBRARY, $lib);
        
        $req->setInputValue('struc', 'structure');
        $req->setInputValue('save', [
            'editor' => [
                'archives' => $config['archives']
            ]
        ]);
        
        $editor = $this->editorAction($req);
        
        $dataDoc = $editor->asDocument();
        $templateResource = $this->locator->getResource(ModResource::TYPE_TEMPLATE, 'extract');
        
        if ($dataDoc and $templateResource) {
            if ($extractDoc = $this->dom->transform($dataDoc, $templateResource->getPath(), [
                'lib' => $lib
            ])) {
                $ret = $libResource->setContents($extractDoc->saveXML());
            }
        }
        
        return $ret ? $libResource : null;
    }

    public function styleAction(HTTPRequest $req)
    {
        $ret = false;
        
        $this->defaultAction($req);
        
        assert($req->hasInputValue('lib'));
        $lib = $req->getInputValue('lib');
        
        $libResource = $this->locator->getResource(ModResource::TYPE_LIBRARY, $lib);
        $libDoc = $libResource->getDocument();
        
        $styling = [];
        
        switch ($lib) {
            case 'portraits':
                $fileId = 0;
                $palettes = [];
                foreach ([
                    49
                ] as $paletteId) {
                    $graphicResouce = $this->locator->getResource(ModResource::TYPE_GRAPHIC, sprintf('%s/%03d-%02d', $lib, $fileId, $paletteId));
                    $palettes[$paletteId] = $graphicResouce->getUrl();
                }
                $list = [];
                foreach ($libDoc->getElementsByTagName('portrait') as $itemNode) {
                    $list[$itemNode->getAttribute('id')] = $itemNode->getAttribute('id');
                }
                
                $styling[] = [
                    'name' => 'portrait',
                    'palettes' => $palettes,
                    'values' => $list
                ];
                break;
            case 'items':
                $fileId = 1;
                $palettes = [];
                foreach ([
                    49
                ] as $paletteId) {
                    $graphicResouce = $this->locator->getResource(ModResource::TYPE_GRAPHIC, sprintf('%s/%03d-%02d', $lib, $fileId, $paletteId));
                    $palettes[$paletteId] = $graphicResouce->getUrl();
                }
                $list = [];
                $labels = [];
                foreach ($libDoc->getElementsByTagName('item') as $itemNode) {
                    $list[$itemNode->getAttribute('id')] = $itemNode->getAttribute('image-id');
                    $labels[$itemNode->getAttribute('id')] = $itemNode->getAttribute('name');
                }
                
                $styling[] = [
                    'name' => 'item-id',
                    'palettes' => $palettes,
                    'values' => $list,
                    'labels' => $labels
                ];
                break;
            case 'tileset.icons':
                foreach ($libDoc->getElementsByTagName('tileset.icon') as $tilesetNode) {
                    $fileId = $tilesetNode->getAttribute('id');
                    $palettes = [];
                    foreach (range(1, 49) as $paletteId) {
                        $graphicResouce = $this->locator->getResource(ModResource::TYPE_GRAPHIC, sprintf('%s/%03d-%02d', $lib, $fileId, $paletteId - 1));
                        $palettes[$paletteId] = $graphicResouce->getUrl();
                    }
                    $list = [];
                    foreach ($tilesetNode->getElementsByTagName('tile') as $tileNode) {
                        $imageCount = (int) $tileNode->getAttribute('image-count');
                        if ($imageCount > 0) {
                            $list[$tileNode->getAttribute('id')] = (int) $tileNode->getAttribute('image-id');
                            $list[$tileNode->getAttribute('id')] --;
                        }
                    }
                    $styling[] = [
                        'selector' => sprintf('*[data-tileset-icon="%d"]', $fileId),
                        'name' => 'tile-id',
                        'palettes' => $palettes,
                        'values' => $list
                    ];
                }
                break;
            case 'pcs':
                break;
            case 'npcs':
                break;
            case 'monsters':
                
                // <xsl:variable name="id"
                // select="concat('monster-gfx-', generate-id(.))" />
                // <xsl:variable name="gfx-id" select=".//*[@name = 'gfx-id']/@value" />
                // <xsl:variable name="width"
                // select=".//*[@name = 'width']/*[@name = 'target']/@value" />
                // <xsl:variable name="height"
                // select=".//*[@name = 'height']/*[@name = 'target']/@value" />
                // <xsl:variable name="animation"
                // select="str:tokenize(string(.//*[@name = 'cycle']/@value))" />
                // <style scoped="scoped"><![CDATA[
                // .]]><xsl:value-of select="$id" /><![CDATA[ > *[data-picker-name="gfx-id"]::after {
                // width: ]]><xsl:value-of select="2 * $width" /><![CDATA[px;
                // height: ]]><xsl:value-of select="2 * $height" /><![CDATA[px;
                // font-size: ]]><xsl:value-of select="2 * $height" /><![CDATA[px;
                // background-image: url("/getResource.php/amber/monster-gfx/]]><xsl:value-of
                // select="$gfx-id" /><![CDATA[");
                // /*
                // animation-name: ]]><xsl:value-of select="$id" /><![CDATA[;
                // animation-iteration-count: infinite;
                // animation-timing-function: steps(1, end);
                // animation-duration: 4s;
                // */
                // }
                // @keyframes ]]><xsl:value-of select="$id" /><![CDATA[ {
                // ]]>
                // <xsl:for-each select="$animation">
                // <xsl:variable name="step" select="100 * position() div last()" />
                // <xsl:value-of
                // select="concat($step, '% { background-position-y: -', php:functionString('hexdec', .), 'em; } ')" />
                // </xsl:for-each>
                // <![CDATA[
                // }
                // ]]></style>
                break;
        }
        
        $css = [];
        
        foreach ($styling as $style) {
            if (! isset($style['selector'])) {
                $style['selector'] = '';
            }
            if (! isset($style['values'])) {
                $style['values'] = [];
            }
            if (! isset($style['labels'])) {
                $style['labels'] = [];
            }
            if (count($style['palettes']) > 1) {
                $css[] = sprintf('amber-%s::after {
background-clip: content-box;
background-size: cover;
background-repeat: no-repeat;
display: block;
content: " ";
}', $style['name']);
                foreach ($style['palettes'] as $paletteId => $url) {
                    $css[] = sprintf('%s[data-palette="%d"] amber-%s::after {
background-image: url("%s");
}', $style['selector'], $paletteId, $style['name'], $url);
                }
            } else {
                foreach ($style['palettes'] as $paletteId => $url) {
                    $css[] = sprintf('%s amber-%s::after {
background-image: url("%s");
background-clip: content-box;
background-size: cover;
background-repeat: no-repeat;
display: block;
content: " ";
}', $style['selector'], $style['name'], $url);
                }
            }
            foreach ($style['values'] as $id => $position) {
                $css[] = sprintf('%s amber-%s[value="%d"]::after { background-position-y: -%dem; }', $style['selector'], $style['name'], $id, $position);
            }
            foreach ($style['labels'] as $id => $label) {
                $css[] = sprintf('%s amber-%s[value="%d"]:hover::before { content: "%s"; }', $style['selector'], $style['name'], $id, $label);
            }
        }
        
        $css = implode(PHP_EOL, $css);
        
        $styleResource = $this->locator->getResource(ModResource::TYPE_STYLESHEET, $lib);
        $styleResource->setContents($css);
        return $styleResource;
    }

    public function cronAction(HTTPRequest $req)
    {
        $gameList = [];
        $gameList[] = 'ambermoon';
        
        $modList = [];
        $modList[] = 'Thalion-v1.05-DE';
        $modList[] = 'Thalion-v1.06-DE';
        // $modList[] = 'Thalion-v1.07-DE';
        // $modList[] = 'Slothsoft-v1.00-DE';
        
        // $libList = array_keys($this->extractionConfig);
        $libList = [];
        $libList[] = 'portraits';
        $libList[] = 'items';
        $libList[] = 'pcs';
        $libList[] = 'npcs';
        $libList[] = 'monsters';
        $libList[] = 'tileset.icons';
        
        $styleList = [];
        $styleList[] = 'portraits';
        $styleList[] = 'items';
        $styleList[] = 'pcs';
        $styleList[] = 'npcs';
        $styleList[] = 'monsters';
        $styleList[] = 'tileset.icons';
        
        $graphicsList = [];
        // $graphicsList[] = 'graphics';
        
        foreach ($gameList as $game) {
            $req->setInputValue('game', $game);
            echo $game . PHP_EOL;
            foreach ($modList as $mod) {
                $req->setInputValue('mod', $mod);
                
                $this->defaultAction($req);
                
                $archiveManager = new ArchiveManager($this->locator->getResourceById('ambtool')->getPath());
                $graphicsManager = new GraphicsManager($this->locator->getResourceById('ambgfx')->getPath());
                
                echo "\t" . $mod . PHP_EOL;
                
                echo "\t\tlib" . PHP_EOL;
                $libs = [];
                foreach ($libList as $lib) {
                    $req->setInputValue('lib', $lib);
                    
                    echo "\t\t\t" . $lib . PHP_EOL;
                    try {
                        $libResource = $this->extractAction($req);
                        if ($libResource) {
                            if ($libDoc = $libResource->getDocument()) {
                                foreach ($libDoc->documentElement->childNodes as $node) {
                                    $libs[] = $libDoc->saveXML($node);
                                }
                            }
                        }
                        $res = $libResource ? sprintf('Created resource "%s"!', $libResource->getName()) : sprintf('Failed to create resource "%s"!', $lib);
                    } catch (Exception $e) {
                        $res = 'EXCEPTION: ' . $e->getMessage();
                    } catch (Error $e) {
                        $res = 'ERROR: ' . $e->getMessage();
                    }
                    
                    echo "\t\t\t\t" . $res . PHP_EOL;
                }
                $libs = '<amberdata xmlns="http://schema.slothsoft.net/amber/amberdata">' . PHP_EOL . implode(PHP_EOL, $libs) . PHP_EOL . '</amberdata>';
                $resource = $this->locator->setResourceContentsById('libs', $libs);
                $res = $resource ? sprintf('Created resource "%s"!', $resource->getName()) : sprintf('Failed to create resource "%s"!', 'libs');
                echo "\t\t\t" . $res . PHP_EOL;
                echo PHP_EOL;
                
                echo "\t\tstyle" . PHP_EOL;
                $styles = [];
                foreach ($styleList as $lib) {
                    $req->setInputValue('lib', $lib);
                    
                    echo "\t\t\t" . $lib . PHP_EOL;
                    try {
                        $libResource = $this->styleAction($req);
                        if ($libResource) {
                            $styles[] = $libResource->getContents();
                        }
                        $res = $libResource ? sprintf('Created resource "%s"!', $libResource->getName()) : sprintf('Failed to create resource "%s"!', $lib);
                    } catch (Exception $e) {
                        $res = 'EXCEPTION: ' . $e->getMessage();
                    } catch (Error $e) {
                        $res = 'ERROR: ' . $e->getMessage();
                    }
                    
                    echo "\t\t\t\t" . $res . PHP_EOL;
                }
                $styles = implode(PHP_EOL, $styles);
                $resource = $this->locator->setResourceContentsById('styles', $styles);
                $res = $resource ? sprintf('Created resource "%s"!', $resource->getName()) : sprintf('Failed to create resource "%s"!', 'styles');
                echo "\t\t\t" . $res . PHP_EOL;
                echo PHP_EOL;
                
                echo "\t\tgfx" . PHP_EOL;
                
                foreach ($graphicsList as $lib) {
                    $req->setInputValue('lib', $lib);
                    $graphicsResource = $this->extractAction($req);
                    // $graphicsResource = $this->locator->getResourceById($lib);
                    $graphicsDoc = $graphicsResource->getDocument();
                    foreach ($graphicsDoc->getElementsByTagNameNS('http://schema.slothsoft.net/amber/amberdata', 'gfx-archive') as $archiveNode) {
                        $archiveName = $archiveNode->getAttribute('file-name');
                        $archivePath = $this->locator->getResource(ModResource::TYPE_SOURCE, $archiveNode->getAttribute('file-path'))
                            ->getPath();
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
                                    $fileIdList = [
                                        $options['id']
                                    ];
                                    break;
                            }
                            
                            foreach ($fileIdList as $fileId) {
                                if (isset($filePathList[$fileId])) {
                                    $sourceFile = $filePathList[$fileId];
                                    $targetFile = $this->locator->getResource(ModResource::TYPE_GRAPHIC, $archiveName . DIRECTORY_SEPARATOR . sprintf('%03d-%02d', $fileId, $options['palette']))->getPath();
                                    
                                    $res = $graphicsManager->convertGraphic($sourceFile, $targetFile, $options);
                                    echo "\t\t\t\t" . ($res ? 'OK: ' : 'ERROR: ') . $targetFile . PHP_EOL;
                                }
                            }
                        }
                        echo PHP_EOL;
                    }
                }
            }
            echo PHP_EOL;
        }
    }
}