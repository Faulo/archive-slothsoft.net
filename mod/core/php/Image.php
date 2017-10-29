<?php
namespace Slothsoft\Core;

use Slothsoft\CMS\Cache;
use Exception;

class Image
{

    const IRFANVIEW_ACTIVE = true;

    // const IRFANVIEW_PATH = 'C:/Program Files (x86)/IrfanView/i_view32.exe';
    // const IRFANVIEW_PATH = 'C:/NetzwerkDaten/Dropbox/Tools/irfanview/i_view32.exe';
    const IRFANVIEW_PATH = 'i_view32';

    const IRFANVIEW_QUERY_SIZE = '%1$s /resize=(%3$d,%4$d) /aspectratio /silent /convert=%2$s';

    const IRFANVIEW_QUERY_CONVERT = '%1$s /silent /convert=%2$s';

    const IRFANVIEW_QUERY_SCALE = '%1$s /silent /resize=(%3$d,%4$d) /convert=%2$s';

    const FFMPEG_ACTIVE = true;
 // DEAKTIVIERT: temp.tmp kann nicht gelesen werden!!!!!
                                // const FFMPEG_PATH = 'C:/NetzwerkDaten/Dropbox/Tools/ffmpeg/ffmpeg.exe';
    const FFMPEG_PATH = 'ffmpeg';

    const FFMPEG_QUERY_CONVERT = '-i %1$s %2$s -y';
 // /encode
    const OPTIPNG_ACTIVE = true;

    const OPTIPNG_PATH = 'optipng';

    const OPTIPNG_QUERY_MINIFY = '-silent %1$s';

    const THUMBNAIL_WIDTH = 320;

    const THUMBNAIL_HEIGHT = 240;

    protected static function getTempFile()
    {
        // $ret = tempnam(sys_get_temp_dir(), __CLASS__);
        $ret = temp_file(__CLASS__);
        // my_dump($ret);
        return $ret;
    }

    public static function createSprite($destFile, $spriteWidth, $spriteHeight, $cols = 1, $rows = 1, array $imageList = [])
    {
        $width = $spriteWidth * $cols;
        $height = $spriteHeight * $rows;
        $ret = imagepng(imagecreatetruecolor($width, $height), $destFile, 9);
        if ($ret and $imageList) {
            self::addSprite($destFile, $imageList, $spriteWidth, $spriteHeight);
        }
        return $ret;
    }

    public static function addSprite($destFile, array $sourceFileList, $spriteWidth = null, $spriteHeigh = null)
    {
        $destInfo = self::imageInfo($destFile);
        $destImage = self::createFromFile($destFile);
        foreach ($sourceFileList as $index => $sourceFile) {
            if ($sourceFile) {
                $sourceInfo = self::imageInfo($sourceFile);
                
                $width = $spriteWidth === null ? $sourceInfo['width'] : $spriteWidth;
                $height = $spriteHeigh === null ? $sourceInfo['height'] : $spriteHeigh;
                
                $x = $width * $index;
                $y = 0;
                while ($x >= $destInfo['width']) {
                    $x -= $destInfo['width'];
                    $y += $height;
                }
                $sourceImage = self::createFromFile($sourceFile);
                imagecopy($destImage, $sourceImage, $x, $y, 0, 0, $sourceInfo['width'], $sourceInfo['height']);
                imagedestroy($sourceImage);
            }
        }
        return imagepng($destImage, $destFile, 9);
    }

    public static function imageInfo($file)
    {
        $arr = getimagesize($file);
        return [
            'width' => $arr[0],
            'height' => $arr[1],
            'mime' => $arr['mime']
        ];
    }

    public static function createFromFile($file)
    {
        $size = getimagesize($file);
        switch ($size['mime']) {
            case 'image/jpeg':
                return imagecreatefromjpeg($file); // jpeg file
            case 'image/gif':
                return imagecreatefromgif($file); // gif file
            case 'image/png':
                return imagecreatefrompng($file); // png file
            default:
                throw new Exception(sprintf('MIME TYPE NOT SUPPORTED: "%s"', basename($file)));
        }
    }

    public static function setTransparency($image_source, $new_image)
    {
        $transparencyIndex = imagecolortransparent($image_source);
        $transparencyColor = array(
            'red' => 255,
            'green' => 255,
            'blue' => 255
        );
        
        if ($transparencyIndex > - 1) {
            $transparencyColor = imagecolorsforindex($image_source, $transparencyIndex);
        }
        
        $transparencyIndex = imagecolorallocate($new_image, $transparencyColor['red'], $transparencyColor['green'], $transparencyColor['blue']);
        imagefill($new_image, 0, 0, $transparencyIndex);
        imagecolortransparent($new_image, $transparencyIndex);
    }

    public static function splitImage($file, $x, $y, $new_width, $new_height)
    {
        $pic = self::createFromFile($file);
        $new_image = imagecreatetruecolor($new_width, $new_height);
        self::setTransparency($pic, $new_image);
        $originaltransparentcolor = imagecolortransparent($pic);
        if ($originaltransparentcolor > - 1) {
            $transparentcolor = imagecolorsforindex($pic, $originaltransparentcolor);
        }
        imagecopyresized($new_image, $pic, 0, 0, $x, $y, $new_width, $new_height, $new_width, $new_height);
        if ($originaltransparentcolor > - 1) {
            $newtransparentcolor = imagecolorallocate($new_image, $transparentcolor['red'], $transparentcolor['green'], $transparentcolor['blue']);
            imagecolortransparent($new_image, $newtransparentcolor);
        }
        imagedestroy($pic);
        return $new_image;
    }

    public static function convertFile($sourceFile, $destFile)
    {
        $sourceFile = str_replace('/', '\\', $sourceFile);
        $destFile = str_replace('/', '\\', $destFile);
        if (self::IRFANVIEW_ACTIVE) {
            $command = '"' . self::IRFANVIEW_PATH . '" ';
            $command .= sprintf(self::IRFANVIEW_QUERY_CONVERT, escapeshellarg($sourceFile), escapeshellarg($destFile));
            exec($command);
        } elseif (self::FFMPEG_ACTIVE) {
            $command = '"' . self::FFMPEG_PATH . '" ';
            $command .= sprintf(self::FFMPEG_QUERY_CONVERT, escapeshellarg($sourceFile), escapeshellarg($destFile));
            exec($command);
        }
        $ret = file_exists($destFile);
        
        if (self::OPTIPNG_ACTIVE) {
            if ($ret and strtolower(pathinfo($destFile, PATHINFO_EXTENSION)) === 'png') {
                $command = '"' . self::OPTIPNG_PATH . '" ';
                $command .= sprintf(self::OPTIPNG_QUERY_MINIFY, escapeshellarg($destFile));
                exec($command);
            }
        }
        
        return $ret;
    }

    public static function scaleFile($sourceFile, $destFile, $width, $height)
    {
        $sourceFile = str_replace('/', '\\', $sourceFile);
        $destFile = str_replace('/', '\\', $destFile);
        if (self::IRFANVIEW_ACTIVE) {
            $command = '"' . self::IRFANVIEW_PATH . '" ';
            $command .= sprintf(self::IRFANVIEW_QUERY_SCALE, escapeshellarg($sourceFile), escapeshellarg($destFile), $width, $height);
            exec($command);
        }
        
        $ret = file_exists($destFile);
        
        if (self::OPTIPNG_ACTIVE) {
            if ($ret and strtolower(pathinfo($destFile, PATHINFO_EXTENSION)) === 'png') {
                $command = '"' . self::OPTIPNG_PATH . '" ';
                $command .= sprintf(self::OPTIPNG_QUERY_MINIFY, escapeshellarg($destFile));
                exec($command);
            }
        }
        
        return $ret;
    }

    public static function cropFile($sourceFile, $destFile, $width, $height)
    {}

    public static function mergeFile($sourceFile, $appendFile, $targetFile = null)
    {
        if ($targetFile === null) {
            $targetFile = $sourceFile;
        }
        $ret = false;
        
        $sourceImage = self::createFromFile($sourceFile);
        $appendImage = self::createFromFile($appendFile);
        
        if ($sourceImage and $appendImage) {
            imagealphablending($sourceImage, true);
            imagesavealpha($sourceImage, true);
            
            $appendInfo = self::imageInfo($appendFile);
            imagecopy($sourceImage, $appendImage, 0, 0, 0, 0, $appendInfo['width'], $appendInfo['height']);
            
            $tempFile = self::getTempFile();
            imagepng($sourceImage, $tempFile);
            $ret = self::convertFile($tempFile, $targetFile);
        }
        return $ret;
    }

    public static function generateThumbnail($sourceFile, $thumbWidth = null, $thumbHeight = null, $returnLink = true)
    {
        static $cache = null;
        $ret = null;
        if (! $cache) {
            $cache = new Cache();
        }
        if (! $thumbWidth) {
            $thumbWidth = self::THUMBNAIL_WIDTH;
        }
        if (! $thumbHeight) {
            $thumbHeight = self::THUMBNAIL_HEIGHT;
        }
        if ($sourceFile = realpath($sourceFile)) {
            $fileHash = md5_file($sourceFile);
            $fileName = basename($sourceFile);
            $fileExt = strrpos($fileName, '.');
            if ($fileExt === false) {
                $fileExt = '.' . $fileName;
            } else {
                $fileExt = substr($fileName, $fileExt);
            }
            $fileExt = strtolower($fileExt);
            $tmpFile = sprintf('%s.%dx%d%s', $fileHash, $thumbWidth, $thumbHeight, $fileExt);
            // $tmpFile = FileSystem::filenameEncode($tmpFile, true);
            // echo $tmpFile . PHP_EOL;
            
            $destFile = $cache->getPath($tmpFile, 'images/');
            $destLink = $cache->getURI($tmpFile, 'images/');
            
            if ($sourceFile and ! file_exists($destFile) or filemtime($destFile) < filemtime($sourceFile)) {
                if (self::IRFANVIEW_ACTIVE and is_readable(self::IRFANVIEW_PATH)) {
                    $command = '"' . self::IRFANVIEW_PATH . '" ';
                    $command .= sprintf(self::IRFANVIEW_QUERY_SIZE, $sourceFile, str_replace('/', '\\', $destFile), $thumbWidth, $thumbHeight);
                    // echo $command . PHP_EOL;
                    exec($command);
                } else {
                    try {
                        $pic = self::createFromFile($sourceFile);
                        $width = imagesx($pic);
                        $height = imagesy($pic);
                        // calculate the image ratio
                        $imgratio = ($width / $height);
                        if ($imgratio > 1) {
                            $new_width = $thumbWidth;
                            $new_height = (int) ($thumbWidth / $imgratio);
                        } else {
                            $new_height = $thumbHeight;
                            $new_width = (int) ($thumbHeight * $imgratio);
                        }
                        $new_image = imagecreatetruecolor($new_width, $new_height);
                        imagecopyresized($new_image, $pic, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                        imagedestroy($pic);
                        imagejpeg($new_image, $destFile);
                    } catch (Exception $e) {}
                }
            }
            if (file_exists($destFile)) {
                $ret = $returnLink ? $destLink : $destFile;
            }
        }
        return $ret;
    }
}

