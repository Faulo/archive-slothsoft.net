<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\FileSystem;
$dir = __DIR__ . '/../res/tilesets';
$fileList = FileSystem::scanDir($dir, FileSystem::SCANDIR_REALPATH);

foreach ($fileList as $file) {
    if ($image = imagecreatefrompng($file)) {
        imagecolortransparent($image, 0);
        imagepng($image, $file);
        
        echo $file . PHP_EOL;
    }
}