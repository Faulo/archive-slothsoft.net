<?php
namespace Slothsoft\Amber;

use Slothsoft\Core\Image;

class GraphicsManager {
	private $ambgfxPath;
	
	public function __construct(string $ambgfxPath) {
		assert(file_exists($ambgfxPath));
		
		$this->ambgfxPath = $ambgfxPath;
	}
	
	public function convertGraphic(string $sourceFile, string $targetFile, array $options = []) : bool {
		$ret = false;
		$sourceFile = realpath($sourceFile);
		if ($sourceFile) {
			$tempFile = temp_file(__CLASS__);
			
			$args = [];
			$args['out'] = $tempFile;
			if (isset($options['palette'])) {
				$args['p'] = (int) $options['palette'];
			}
			if (isset($options['bitplanes'])) {
				$args['b'] = (int) $options['bitplanes'];
			}
			if (isset($options['width'])) {
				$args['w'] = (int) $options['width'];
			}
			if (isset($options['offset'])) {
				$args['off'] = (int) $options['offset'];
			}
			if (isset($options['size'])) {
				$args['s'] = (int) $options['size'];
			}
			$setTransparent = isset($options['transparent'])
				? (int) $options['transparent']
				: 0;
			
			$this->ambgfx($sourceFile, $args);
			
			if (file_exists($tempFile)) {
				Image::convertFile($tempFile, $targetFile);
				if (file_exists($targetFile)) {
					if ($setTransparent) {
						if ($image = imagecreatefrompng($targetFile)) {
							imagecolortransparent($image, 0);
							$ret = imagepng($image, $targetFile);
						}
					} else {
						$ret = true;
					}
				}
			}
		}
		return $ret;
	}
	
	/*
	Options:

    -out <filename>  Specify output filename, defaults to
                     source filename with ".tga" suffix.

    -b[pl] <num>     Number of bitplanes (1-6), default 5.

    -p[al] <num>     Palette number (0-49), default 49.

    -w[idth] <num>   Image width (4-640), default 16.

    -off <num>       Offset into file, default 0. Use this
                     option to skip the first <num> bytes
                     of the input file (if graphic data is
                     in the middle of some file).

    -s[ize] <num>    Size of file, default (filesize - offset).
                     Limits data block. Usually used
                     together with the -off option. 
	*/
	private function ambgfx(string $file, array $args) {
		$command = escapeshellarg($this->ambgfxPath) . ' ' . escapeshellarg($file);
		foreach ($args as $key => $val) {
			$command .= sprintf(' -%s %s', $key, escapeshellarg($val));
		}
		//echo $command . PHP_EOL;
		exec($command, $output);
		return $output;
	}
}