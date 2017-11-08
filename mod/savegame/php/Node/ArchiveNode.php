<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\FileSystem;
use Exception;
declare(ticks = 1000);

class ArchiveNode extends AbstractNode
{

    const ARCHIVE_TYPE_RAW = 'Raw';

    const ARCHIVE_TYPE_AM2 = 'AM2';

    const ARCHIVE_TYPE_AMBR = 'AMBR';

    const ARCHIVE_TYPE_JH = 'JH';

    protected $filePathList;

    protected $archivePath;

    protected $tempDir;

    public function __construct()
    {
        parent::__construct();
        $this->strucData['file-name'] = '';
        $this->strucData['file-time'] = '';
        $this->strucData['file-md5'] = '';
        $this->strucData['type'] = '';
        
        $this->archivePath = '';
        $this->filePathList = [];
    }

    public function loadStruc()
    {
        $this->ownerArchive = $this;
        
        parent::loadStruc();
        
        $defaultFile = $this->ownerEditor->buildDefaultFile($this->strucData['file-name']);
        $tempFile = $this->ownerEditor->buildTempFile($this->strucData['file-name']);
        
        if ($uploadedArchives = $this->ownerEditor->getConfigValue('uploadedArchives')) {
            if (isset($uploadedArchives[$this->strucData['file-name']])) {
                move_uploaded_file($uploadedArchives[$this->strucData['file-name']], $tempFile);
            }
        }
        
        $path = file_exists($tempFile) ? $tempFile : $defaultFile;
        $this->setArchivePath($path);
    }

    protected function loadNode()
    {
        if ($this->ownerEditor->shouldLoadArchive($this->strucData['file-name'])) {
            
            $this->filePathList = FileSystem::scanDir($this->tempDir, FileSystem::SCANDIR_REALPATH);
            if (! count($this->filePathList)) {
                $this->loadArchive();
                $this->filePathList = FileSystem::scanDir($this->tempDir, FileSystem::SCANDIR_REALPATH);
            }
        }
    }

    protected function loadChildren()
    {
        foreach ($this->filePathList as $filePath) {
            $strucData = [];
            $strucData['file-name'] = basename($filePath);
            $strucData['file-path'] = $filePath;
            
            $this->loadChild($this->getStrucElement(), 'file', $strucData);
        }
    }

    protected function loadArchive()
    {
        $ambtoolPath = $this->ownerEditor->getConfigValue('ambtoolPath');
        
        if (! file_exists($ambtoolPath)) {
            throw new Exception('ambtool not found at ' . $ambtoolPath);
        }
        $command = sprintf('%1$s %2$s', escapeshellarg($ambtoolPath), escapeshellarg($this->archivePath));
        exec($command, $output);
        
        if (isset($output[1])) {
            switch ($output[1]) {
                case 'Format: JH (encrypted)':
                    // double-pass!
                    unset($output);
                    
                    $tempDir = temp_dir(__CLASS__ . DIRECTORY_SEPARATOR . '_JH');
                    $command = sprintf('%1$s %2$s %3$s', escapeshellarg($ambtoolPath), escapeshellarg($this->archivePath), escapeshellarg($tempDir));
                    exec($command);
                    $fileList = FileSystem::scanDir($tempDir, FileSystem::SCANDIR_REALPATH);
                    
                    assert('count($fileList) === 1', 'JH archive must contain 1 file');
                    
                    $file = $fileList[0];
                    
                    $command = sprintf('%1$s %2$s %3$s', escapeshellarg($ambtoolPath), escapeshellarg($file), escapeshellarg($this->tempDir));
                    exec($command, $output);
                    
                    if (isset($output[1])) {
                        break;
                    }
                // didn't need double-pass after all...
                case 'Format: AMBR (raw archive)':
                case 'Format: AMNP (compressed/encrypted archive)':
                    $command = sprintf('%1$s %2$s %3$s', escapeshellarg($ambtoolPath), escapeshellarg($this->archivePath), escapeshellarg($this->tempDir));
                    exec($command);
                    break;
                default:
                    throw new Exception('unknown ambtool format: ' . $output[1]);
            }
        } else {
            // switching to raw mode
            copy($this->archivePath, $this->tempDir . DIRECTORY_SEPARATOR . '1');
        }
    }

    public function writeArchive()
    {
        $path = $this->ownerEditor->buildTempFile($this->strucData['file-name']);
        $ret = file_put_contents($path, $this->getArchive());
        if ($ret) {
            $this->setArchivePath($path);
        }
        return $ret;
    }

    public function getArchive()
    {
        $ret = null;
        switch ($this->strucData['type']) {
            case self::ARCHIVE_TYPE_AMBR:
                $header = [];
                $body = [];
                $maxId = 0;
                foreach ($this->childNodeList as $child) {
                    $id = (int) $child->getFileName();
                    if ($id > $maxId) {
                        $maxId = $id;
                    }
                    $val = $child->getContent();
                    $header[$id] = pack('N', strlen($val));
                    $body[$id] = $val;
                }
                for ($id = 1; $id < $maxId; $id ++) {
                    if (! isset($header[$id])) {
                        $header[$id] = pack('N', 0);
                        $body[$id] = '';
                    }
                }
                ksort($header);
                ksort($body);
                
                array_unshift($header, 'AMBR' . pack('n', count($body)));
                
                $ret = implode('', $header) . implode('', $body);
                break;
            default:
                $ret = '';
                foreach ($this->childNodeList as $child) {
                    $ret .= $child->getContent();
                }
                break;
        }
        return $ret;
    }

    public function getArchiveId()
    {
        return $this->strucData['file-name'];
    }

    protected function setArchivePath($path)
    {
        $this->archivePath = $path;
        
        $this->strucData['file-size'] = FileSystem::size($this->archivePath);
        $this->strucData['file-time'] = date(DATE_DATETIME, FileSystem::changetime($this->archivePath));
        $this->strucData['file-md5'] = md5_file($this->archivePath);
        
        $dir = [];
        $dir[] = sys_get_temp_dir();
        $dir[] = str_replace(NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, __CLASS__);
        $dir[] = $this->strucData['file-name'];
        $dir[] = $this->strucData['file-md5'];
        
        $this->tempDir = implode(DIRECTORY_SEPARATOR, $dir);
        
        if (! is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
    }
}