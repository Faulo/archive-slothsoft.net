<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Amber\ArchiveManager;
use Slothsoft\Core\FileSystem;
use DomainException;
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
        $this->strucData['path'] = '';
        $this->strucData['name'] = '';
        $this->strucData['timestamp'] = '';
        $this->strucData['md5'] = '';
        $this->strucData['type'] = '';
        
        $this->archivePath = '';
        $this->filePathList = [];
    }

    public function loadStruc()
    {
        parent::loadStruc();
        
        if (! $this->strucData['name']) {
            $this->strucData['name'] = basename($this->strucData['path']);
        }
        
        $defaultFile = $this->ownerEditor->buildDefaultFile($this->strucData['path']);
        $tempFile = $this->ownerEditor->buildTempFile($this->strucData['name']);
        
        if ($uploadedArchives = $this->ownerEditor->getConfigValue('uploadedArchives')) {
            if (isset($uploadedArchives[$this->strucData['name']])) {
                move_uploaded_file($uploadedArchives[$this->strucData['name']], $tempFile);
            }
        }
        
        $path = file_exists($tempFile) ? $tempFile : $defaultFile;
        $this->setArchivePath($path);
    }

    protected function loadNode()
    {
        if ($this->ownerEditor->shouldLoadArchive($this->strucData['name'])) {
            if ($this->tempDir) {
                $this->filePathList = FileSystem::scanDir($this->tempDir, FileSystem::SCANDIR_REALPATH);
                if (! count($this->filePathList)) {
                    $this->loadArchive();
                    $this->filePathList = FileSystem::scanDir($this->tempDir, FileSystem::SCANDIR_REALPATH);
                }
            } else {
                $this->filePathList = [];
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
        
        switch ($this->strucData['type']) {
            case self::ARCHIVE_TYPE_AMBR:
            case self::ARCHIVE_TYPE_JH:
                $manager = new ArchiveManager($ambtoolPath);
                $manager->extractArchive($this->archivePath, $this->tempDir);
                break;
            case self::ARCHIVE_TYPE_AM2:
            case self::ARCHIVE_TYPE_RAW:
                copy($this->archivePath, $this->tempDir . DIRECTORY_SEPARATOR . '1');
                break;
            default:
                throw new DomainException(sprintf('unknown archive type "%s"!', $this->strucData['type']));
        }
    }

    public function writeArchive()
    {
        $path = $this->ownerEditor->buildTempFile($this->strucData['name']);
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
            case self::ARCHIVE_TYPE_JH:
            case self::ARCHIVE_TYPE_AM2:
            case self::ARCHIVE_TYPE_RAW:
                $ret = '';
                foreach ($this->childNodeList as $child) {
                    $ret .= $child->getContent();
                }
                break;
            default:
                throw new DomainException(sprintf('unknown archive type "%s"!', $this->strucData['type']));
        }
        return $ret;
    }

    public function getArchiveId()
    {
        return $this->strucData['name'];
    }

    protected function setArchivePath($path)
    {
        $this->archivePath = $path;
        
        if (file_exists($this->archivePath)) {
            $this->strucData['size'] = FileSystem::size($this->archivePath);
            $this->strucData['timestamp'] = date(DATE_DATETIME, FileSystem::changetime($this->archivePath));
            $this->strucData['md5'] = md5_file($this->archivePath);
            
            $dir = [];
            $dir[] = sys_get_temp_dir();
            $dir[] = str_replace(NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, __CLASS__);
            $dir[] = $this->strucData['name'];
            $dir[] = $this->strucData['md5'];
            
            $this->tempDir = implode(DIRECTORY_SEPARATOR, $dir);
            
            if (! is_dir($this->tempDir)) {
                mkdir($this->tempDir, 0777, true);
            }
        }
    }
}