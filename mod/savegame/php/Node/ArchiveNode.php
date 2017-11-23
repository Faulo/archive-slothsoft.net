<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Amber\ArchiveManager;
use Slothsoft\Core\FileSystem;
use Slothsoft\Savegame\EditorElement;
use DomainException;
declare(ticks = 1000);

class ArchiveNode extends AbstractNode
{

    const ARCHIVE_TYPE_RAW = 'Raw';

    const ARCHIVE_TYPE_AM2 = 'AM2';

    const ARCHIVE_TYPE_AMBR = 'AMBR';

    const ARCHIVE_TYPE_JH = 'JH';

    private $path;
    private $name;
    private $type;
    
    private $timestamp;
    private $md5;
    private $size;
    
    private $filePathList = [];

    private $archivePath;

    private $tempDir;

    protected function getXmlAttributes() : string
    {
        $ret = parent::getXmlAttributes();
        $ret .= sprintf(
            ' name="%s" path="%s" timestamp="%s" md5="%s" type="%s" size="%d"',
            htmlspecialchars($this->name, ENT_COMPAT | ENT_XML1),
            htmlspecialchars($this->path, ENT_COMPAT | ENT_XML1),
            htmlspecialchars($this->timestamp, ENT_COMPAT | ENT_XML1),
            $this->md5,
            $this->type,
            $this->size
        );
        return $ret;
    }

    public function loadStruc()
    {
        parent::loadStruc();
        
        $this->path = $this->loadStringAttribute('path');
        $this->name = $this->loadStringAttribute('name', basename($this->path));
        $this->type = $this->loadStringAttribute('type');
        
        
        $defaultFile = $this->getOwnerEditor()->buildDefaultFile($this->path);
        $tempFile = $this->getOwnerEditor()->buildTempFile($this->name);
        
        if ($uploadedArchives = $this->getOwnerEditor()->getConfigValue('uploadedArchives')) {
            if (isset($uploadedArchives[$this->name])) {
                move_uploaded_file($uploadedArchives[$this->name], $tempFile);
            }
        }
        
        $path = file_exists($tempFile) ? $tempFile : $defaultFile;
        $this->setArchivePath($path);
    }

    protected function loadNode()
    {
        if ($this->getOwnerEditor()->shouldLoadArchive($this->name)) {
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
            $this->loadChild($this->getStrucElement()->clone(EditorElement::NODE_TYPES['file'], $strucData));
            log_execution_time(__FILE__, __LINE__);
        }
    }

    protected function loadArchive()
    {
        $ambtoolPath = $this->getOwnerEditor()->getConfigValue('ambtoolPath');
        
        switch ($this->type) {
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
                throw new DomainException(sprintf('unknown archive type "%s"!', $this->type));
        }
    }

    public function writeArchive()
    {
        $path = $this->getOwnerEditor()->buildTempFile($this->name);
        $ret = file_put_contents($path, $this->getArchive());
        if ($ret) {
            $this->setArchivePath($path);
        }
        return $ret;
    }

    public function getArchive()
    {
        $ret = null;
        switch ($this->type) {
            case self::ARCHIVE_TYPE_AMBR:
                $header = [];
                $body = [];
                $maxId = 0;
				foreach ($this->getChildNodeList() as $child) {
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
				foreach ($this->getChildNodeList() as $child) {
						$ret .= $child->getContent();
				    }
                break;
            default:
                throw new DomainException(sprintf('unknown archive type "%s"!', $this->type));
        }
        return $ret;
    }

    public function getArchiveId()
    {
        return $this->name;
    }

    protected function setArchivePath($path)
    {
        $this->archivePath = $path;
        
        if (file_exists($this->archivePath)) {
            $this->size = FileSystem::size($this->archivePath);
            $this->timestamp = date(DATE_DATETIME, FileSystem::changetime($this->archivePath));
            $this->md5 = md5_file($this->archivePath);
            
            $dir = [];
            $dir[] = sys_get_temp_dir();
            $dir[] = str_replace(NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, __CLASS__);
            $dir[] = $this->name;
            $dir[] = $this->md5;
            
            $this->tempDir = implode(DIRECTORY_SEPARATOR, $dir);
            
            if (! is_dir($this->tempDir)) {
                mkdir($this->tempDir, 0777, true);
            }
        }
    }
}