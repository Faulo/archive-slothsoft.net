<?php
namespace Slothsoft\Amber;

use Slothsoft\Core\FileSystem;
use Exception;

class ArchiveManager
{

    private $ambtoolPath;

    private static $inspectCache = [];

    public function __construct(string $ambtoolPath)
    {
        assert(file_exists($ambtoolPath));
        
        $this->ambtoolPath = $ambtoolPath;
    }

    public function inspectArchive($archivePath): string
    {
        $ret = '';
        if ($archivePath = realpath($archivePath)) {
            if (isset(self::$inspectCache[$archivePath])) {
                $ret = self::$inspectCache[$archivePath];
            } else {
                $output = $this->ambtool($archivePath);
                
                if (isset($output[1])) {
                    $ret = $output[1];
                }
                self::$inspectCache[$archivePath] = $ret;
            }
        }
        return $ret;
    }

    public function extractArchive(string $archivePath, string $targetDir): bool
    {
        $ret = false;
        $archivePath = realpath($archivePath);
        $targetDir = realpath($targetDir);
        if ($archivePath and $targetDir) {
            $format = $this->inspectArchive($archivePath);
            switch ($format) {
                case 'Format: JH (encrypted)':
                    // double-pass!
                    
                    $tempDir = temp_dir(__CLASS__); // . DIRECTORY_SEPARATOR . '_JH'
                    
                    $this->ambtool($archivePath, $tempDir);
                    
                    $fileList = FileSystem::scanDir($tempDir, FileSystem::SCANDIR_REALPATH);
                    
                    assert(count($fileList) === 1, 'JH archive must contain 1 file');
                    
                    $output = $this->ambtool($fileList[0], $targetDir);
                    
                    if (isset($output[1])) {
                        break;
                    }
                // didn't need double-pass after all...
                case 'Format: AMBR (raw archive)':
                case 'Format: AMNP (compressed/encrypted archive)':
                case 'Format: AMNC (encrypted archive)':
                    $this->ambtool($archivePath, $targetDir);
                    break;
                case '':
                    $ret = copy($archivePath, $targetDir . DIRECTORY_SEPARATOR . '1');
                    break;
                default:
                    throw new Exception(sprintf('unknown ambtool format "%s"!', $format));
            }
        }
        return $ret;
    }

    private function ambtool(...$args)
    {
        $command = escapeshellarg($this->ambtoolPath);
        foreach ($args as $arg) {
            $command .= ' ' . escapeshellarg($arg);
        }
        exec($command, $output);
        return $output;
    }
}