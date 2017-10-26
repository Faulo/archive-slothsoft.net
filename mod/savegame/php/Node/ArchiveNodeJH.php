<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\FileSystem;
use Exception;
declare(ticks = 1000);

class ArchiveNodeJH extends ArchiveNode
{

    protected function loadArchive()
    {
        $ambtoolPath = $this->ownerEditor->getConfigValue('ambtoolPath');
        
        if (! file_exists($ambtoolPath)) {
            throw new Exception('ambtool not found at ' . $ambtoolPath);
        }
        $command = sprintf('%1$s %2$s', escapeshellarg($ambtoolPath), escapeshellarg($this->filePath));
        exec($command, $output);
        
        if (isset($output[1])) {
            switch ($output[1]) {
                case 'Format: JH (encrypted)':
                    // double-pass!
                    unset($output);
                    
                    $tempDir = temp_dir(__CLASS__);
                    $command = sprintf('%1$s %2$s %3$s', escapeshellarg($ambtoolPath), escapeshellarg($this->filePath), escapeshellarg($tempDir));
                    exec($command);
                    $fileList = FileSystem::scanDir($tempDir, FileSystem::SCANDIR_REALPATH);
                    
                    if (count($fileList) !== 1) {
                        throw new Exception('weird number of files after extraction');
                    }
                    
                    foreach ($fileList as $file) {
                        $command = sprintf('%1$s %2$s %3$s', escapeshellarg($ambtoolPath), escapeshellarg($file), escapeshellarg($this->tempDir));
                        exec($command, $output);
                        if (! isset($output[1])) {
                            $this->tempDir = $tempDir;
                            break;
                        }
                        unset($output);
                    }
                    break;
                case 'Format: AMBR (raw archive)':
                case 'Format: AMNP (compressed/encrypted archive)':
                    $command = sprintf('%1$s %2$s %3$s', escapeshellarg($ambtoolPath), escapeshellarg($this->filePath), escapeshellarg($this->tempDir));
                    exec($command);
                    break;
                default:
                    my_dump($output[1]);
                    die();
                    break;
            }
        } else {
            // switching to raw mode
            copy($this->filePath, $this->tempDir . DIRECTORY_SEPARATOR . '1');
        }
    }
}