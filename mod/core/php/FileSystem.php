<?php
/***********************************************************************
 * FileSystem v1.00 19.10.2012 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.00 19.10.2012
 *			initial release
 ***********************************************************************/
namespace Slothsoft\Core;

use COM;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use finfo;

abstract class FileSystem
{

    const ZIP_PATH = 'C:/Program Files/7-Zip/7z.exe';

    const ZIP_QUERY_EXTRACT = 'e "%1$s" -y -o"%2$s"';

    const ZIP_QUERY_CREATE = 'a -t7z "%1$s" "%2$s" -mx0 -ms=off -mmt -y';

    const ZIP_QUERY_UPDATE = 'u -t7z "%1$s" "%2$s" -mx0 -ms=off -mmt -y';

    const FFMPEG_PATH = 'C:/NetzwerkDaten/Dropbox/Tools/ffmpeg/ffmpeg.exe';

    const SCANDIR_EXCLUDE_DIRS = 1;

    const SCANDIR_EXCLUDE_FILES = 2;

    const SCANDIR_EXCLUDE_HIDDEN = 16;

    const SCANDIR_REALPATH = 4;

    const SCANDIR_WEBPATH = 8;

    const SCANDIR_SORT = 32;

    const ARCHIVE_MAX_FILES = 1000;

    public static $sizeUnits = [
        'B',
        'kB',
        'MB',
        'GB',
        'TB'
    ];

    public static $videoExtensions = [
        'avi',
        'mkv',
        'mp4',
        'mov',
        'ogv',
        'wmv',
        'mpg',
        'webm',
        'm4v',
        'flv',
        'ogm'
    ];

    public static $audioExtensions = [
        'mp3',
        'wav',
        'ogg',
        'wma',
        'ogm',
        'mid',
        'm4a'
    ];

    public static $subttitleExtensions = [
        'vtt',
        'srt',
        'ssa',
        'ass'
    ];

    public static function isVideo($fileName)
    {
        return in_array(strtolower(self::extension($fileName)), self::$videoExtensions);
    }

    public static function isAudio($fileName)
    {
        return in_array(strtolower(self::extension($fileName)), self::$audioExtensions);
    }

    public static function isSubttitle($fileName)
    {
        return in_array(strtolower(self::extension($fileName)), self::$subttitleExtensions);
    }

    public static function drawBytes($size, $precision = 2)
    {
        for ($i = 0; $size > 1024; $i ++) {
            $size /= 1024.0;
        }
        return sprintf('%.' . $precision . 'f %s', $size, self::$sizeUnits[$i]);
    }

    public static function getStorage()
    {
        $storage = null;
        if (! $storage) {
            $storage = new Storage('FileSystem');
        }
        return $storage;
    }

    public static function generateStorageKey($path, $hash = '')
    {
        $key = realpath($path);
        if (! $key) {
            throw new Exception(sprintf('PATH NOT FOUND: "%s"', $path));
        }
        if (strlen($hash)) {
            $key .= '#' . $hash;
        }
        return sprintf('FileSystem://%s', $key);
    }

    public static function asNode($path, DOMDocument $dataDoc = null)
    {
        $retNode = null;
        $storage = null;
        $returnDocument = ! $dataDoc;
        if ($path = realpath($path)) {
            $name = basename($path);
            if ($name === 'Thumbs.db') {
                @unlink($path);
            } else {
                if (is_dir($path)) {
                    $modifyTime = max(self::dirModifyTime($path));
                    $storage = self::getStorage();
                    $storageKey = self::generateStorageKey($path);
                    if ($tmpNode = $storage->retrieveXML($storageKey, $modifyTime, $dataDoc)) {
                        if (! $dataDoc) {
                            $dataDoc = $tmpNode->ownerDocument;
                        }
                        if ($tmpNode->firstChild instanceof DOMElement) {
                            $retNode = $tmpNode->removeChild($tmpNode->firstChild);
                        }
                    }
                }
                if (! $dataDoc) {
                    $dataDoc = new DOMDocument();
                }
                if (! $retNode) {
                    $tagName = null;
                    $childNodes = [];
                    $attr = [];
                    if (is_file($path)) {
                        $tagName = 'file';
                        $attr['mime'] = self::mime($path);
                        $attr['size-int'] = self::size($path);
                        $attr['size-string'] = self::drawBytes($attr['size-int']);
                        $attr['ext'] = self::extension($path);
                        if (self::isVideo($path)) {
                            $attr['isVideo'] = '';
                            
                            /*
                             * $mediaInfo = self::mediaInfo($path);
                             * $mediaTypes = ['video', 'audio', 'subtitle'];
                             * $parentNode = $dataDoc->createElement('mediaInfo');
                             * $childNodes[] = $parentNode;
                             * foreach ($mediaTypes as $type) {
                             * foreach ($mediaInfo[$type] as $key => $stream) {
                             * $node = $dataDoc->createElement('stream');
                             * $node->setAttribute('type', $type);
                             * $node->setAttribute('key', $key);
                             * foreach ($stream as $k => $v) {
                             * $node->setAttribute($k, $v);
                             * }
                             * $parentNode->appendChild($node);
                             * }
                             * }
                             * //
                             */
                        }
                        if (self::isAudio($path)) {
                            $attr['isAudio'] = '';
                        }
                        if (self::isSubttitle($path)) {
                            $attr['isSubttitle'] = '';
                        }
                    }
                    if (is_dir($path)) {
                        $tagName = 'folder';
                        $attr['size-int'] = 0;
                        // $attr['free-int'] = self::free($path);
                        // $attr['free-string'] = self::drawBytes($attr['free-int']);
                        $childList = self::scanDir($path, self::SCANDIR_REALPATH | self::SCANDIR_SORT);
                        foreach ($childList as $child) {
                            if ($node = self::asNode($child, $dataDoc)) {
                                $attr['size-int'] += (float) $node->getAttribute('size-int');
                                $childNodes[] = $node;
                            }
                        }
                        $attr['size-string'] = self::drawBytes($attr['size-int']);
                    }
                    if ($tagName) {
                        $retNode = $dataDoc->createElement($tagName);
                        $time = self::changetime($path);
                        $attr['change-datetime'] = date(DATE_DATETIME, $time);
                        $attr['change-utc'] = date(DATE_UTC, $time);
                        $time = self::maketime($path);
                        $attr['make-datetime'] = date(DATE_DATETIME, $time);
                        $attr['make-utc'] = date(DATE_UTC, $time);
                        $attr['path'] = $path;
                        $attr['id'] = 'id-' . md5($path);
                        $attr['name'] = $name;
                        $attr['title'] = isset($attr['ext']) ? substr($attr['name'], 0, - (1 + strlen($attr['ext']))) : $attr['name'];
                        foreach ($attr as $key => $val) {
                            $retNode->setAttribute($key, utf8_encode($val));
                        }
                        $retNode->setIdAttribute('id', true);
                        foreach ($childNodes as $node) {
                            $retNode->appendChild($node);
                        }
                        if ($storage) {
                            $storage->storeXML($storageKey, $retNode, $modifyTime);
                        }
                    }
                }
                if ($returnDocument and $retNode) {
                    $dataDoc->appendChild($retNode);
                    $retNode = $dataDoc;
                }
            }
        }
        return $retNode;
    }

    public static function webpath($fileName, $absolute = true)
    {
        return $absolute ? str_replace(DIRECTORY_SEPARATOR, '/', substr($fileName, strlen($_SERVER['DOCUMENT_ROOT']))) : str_replace(DIRECTORY_SEPARATOR, '/', substr($fileName, strlen(dirname($_SERVER['SCRIPT_FILENAME']) . '/')));
    }

    public static function size($fileName)
    {
        $size = null;
        if (is_readable($fileName)) {
            $size = filesize($fileName);
        } elseif ($file = self::lookupFile($fileName)) {
            $size = $file->Size;
        }
        return $size;
    }

    public static function free($fileName)
    {
        return disk_free_space($fileName);
    }

    public static function mime($fileName)
    {
        $fInfo = new FInfo(FILEINFO_MIME_TYPE);
        @$ret = $fInfo->file($fileName);
        return $ret;
    }

    public static function extension($fileName)
    {
        return pathinfo($fileName, PATHINFO_EXTENSION);
    }

    public static function changetime($fileName)
    {
        // $time = filemtime($fileName);
        $time = null;
        if (is_readable($fileName)) {
            $time = filemtime($fileName);
        } elseif ($file = self::lookupFile($fileName)) {
            $time = strtotime($file->DateLastModified);
        }
        return $time;
    }

    public static function maketime($fileName)
    {
        // $time = filemtime($fileName);
        $time = null;
        if (is_readable($fileName)) {
            $time = filectime($fileName);
        } elseif ($file = self::lookupFile($fileName)) {
            $time = strtotime($file->DateLastModified);
        }
        return $time;
    }

    public static function lookupFile($fileName)
    {
        if (is_readable($fileName)) {
            $com = new COM('Scripting.FileSystemObject');
            return is_file($fileName) ? $com->GetFile($fileName) : $com->GetFolder($fileName);
        } elseif ($folder = self::lookupFile(dirname($fileName))) {
            $base = basename($fileName);
            foreach ($folder->files as $file) {
                if ($file->name === $base) {
                    return $file;
                }
            }
            foreach ($folder->SubFolders as $file) {
                if ($file->name === $base) {
                    return $file;
                }
            }
        }
        return null;
    }

    public static function filenameEncode($filename, $removeRoot = false)
    {
        if ($removeRoot) {
            if (strpos($filename, SERVER_ROOT) === 0) {
                $filename = substr($filename, strlen(SERVER_ROOT));
            }
        }
        return str_replace([
            ':',
            '/',
            '\\',
            ' '
        ], '-', $filename);
    }

    public static function filenameSanitize($filename)
    {
        $notAllowed = [
            ':',
            '\\',
            '/',
            '⁄',
            '*',
            '"',
            '?',
            '!',
            '<',
            '>',
            '|'
        ];
        $toReplace = [
            ' -',
            ''
        ];
        $filename = str_replace($notAllowed, $toReplace, $filename);
        $filename = htmlentities($filename, ENT_QUOTES, 'UTF-8');
        $filename = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~iu', '$1', $filename);
        $filename = html_entity_decode($filename, ENT_QUOTES, 'UTF-8');
        $filename = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $filename);
        $filename = str_replace($notAllowed, $toReplace, $filename);
        $filename = preg_replace('~\s+~iu', ' ', $filename);
        $filename = trim($filename);
        return $filename;
    }

    public static function download($filePath, $downloadName)
    {
        // error_reporting(0);
        $size = (float) self::size($filePath);
        $start = 0.0;
        $end = $size;
        $headers = [
            'HTTP/1.1 200 OK',
            'Connection: close',
            'Content-Description: File Transfer',
            'Content-Type: application/octet-stream',
            'Content-Transfer-Encoding: binary',
            'Accept-Ranges: bytes',
            'Cache-Control: must-revalidate',
            'Transfer-Encoding: chunked'
        ];
        if (isset($_SERVER['HTTP_RANGE'])) {
            $range = $_SERVER['HTTP_RANGE'];
            if (preg_match('/^bytes=(\d+)-$/', $range, $match)) {
                $start = (float) $match[1];
                // file_put_contents('start.'.time() . '.1.json', json_encode($start));
            } elseif (preg_match('/^bytes=(\d+)-(\d+)$/', $range, $match)) {
                $start = (float) $match[1];
                $end = (float) $match[2];
                file_put_contents('ENDBYTE.' . time() . '.txt', $range);
            }
        }
        $length = $end - $start;
        if ($start or $end !== $size) {
            $headers[0] = 'HTTP/1.1 206 Partial content';
            $headers[] = sprintf('Content-Range: bytes %1$.0f-%2$.0f/%3$.0f', $start, $end - 1, $size);
        }
        $headers[] = sprintf('Content-Disposition: attachment; filename*=UTF-8\'\'%1$s', rawurlencode($downloadName));
        $headers[] = sprintf('Content-Length: %1$.0f', $length);
        foreach ($headers as $head) {
            header($head);
        }
        self::outputChunk($filePath, $start, $length);
    }

    public static function outputChunk($filePath, $start, $length)
    {
        set_time_limit(0);
        // how many bytes per chunk
        $chunksize = 16384; // 1 kB
        $chunksize = 1073741824; // 1 GB
        $chunksize = 16777216; // 16 MB
        $chunksize = 8192; // 8 kB
        $chunksize = 1048576; // 1 MB
        $handle = fopen($filePath, 'rb');
        if ($start < PHP_INT_MAX) {
            fseek($handle, $start, SEEK_SET);
        } else {
            /*
             * fseek($handle, PHP_INT_MAX, SEEK_SET);
             * $start -= PHP_INT_MAX;
             * while ($start > 0) {
             * fread($handle, min($chunksize, $start));
             * $start -= $chunksize;
             * }
             * //
             */
            while ($start > 0) {
                fread($handle, min($chunksize, $start));
                $start -= $chunksize;
            }
        }
        // *
        while ($length > 0) {
            $read = min($chunksize, $length);
            echo dechex($read) . "
";
            echo fread($handle, $read) . "
";
            flush();
            $length -= $chunksize;
        }
        echo "0

";
        // */
        // fpassthru($handle);
        fclose($handle);
    }

    public static function scanDir($relPath, $options = 0, $filter = null)
    {
        $dirPath = realpath($relPath);
        if ($dirPath === false) {
            return [];
        }
        $dirPath .= DIRECTORY_SEPARATOR;
        $ret = array_diff(scandir($dirPath), [
            '.',
            '..'
        ]);
        $exclude = [];
        if ($options & self::SCANDIR_EXCLUDE_DIRS) {
            foreach ($ret as $file) {
                if (is_dir($dirPath . $file)) {
                    $exclude[] = $file;
                }
            }
        }
        if ($options & self::SCANDIR_EXCLUDE_FILES) {
            foreach ($ret as $file) {
                if (is_file($dirPath . $file)) {
                    $exclude[] = $file;
                }
            }
        }
        if ($options & self::SCANDIR_EXCLUDE_HIDDEN) {
            foreach ($ret as $file) {
                if ($file[0] === '.') {
                    $exclude[] = $file;
                }
                /*
                 * //TAKES FOREVER DO NOT USE
                 * $attributes = shell_exec('attrib ' . escapeshellarg($dirPath . $file));
                 * $attributes = substr($attributes, 0, 12);
                 * if (strpos($attributes, 'H') !== false) {
                 * $exclude[] = $file;
                 * }
                 * //
                 */
            }
        }
        if ($filter !== null) {
            foreach ($ret as $file) {
                if (! preg_match($filter, $file)) {
                    $exclude[] = $file;
                }
            }
        }
        $ret = array_values(array_diff($ret, $exclude));
        if ($options & self::SCANDIR_REALPATH) {
            foreach ($ret as &$val) {
                $val = $dirPath . $val;
            }
            unset($val);
        } elseif ($options & self::SCANDIR_WEBPATH) {
            foreach ($ret as &$val) {
                $val = self::webpath($dirPath . $val);
            }
            unset($val);
        }
        if ($options & self::SCANDIR_SORT) {
            $tmp = [];
            foreach ($ret as $i => $val) {
                $tmp[$val] = preg_replace('/\.[^\.]+$/', '', $val);
            }
            asort($tmp);
            $ret = array_keys($tmp);
        }
        return $ret;
    }

    public static function dirModifyTime($dirPath, $rootPath = null)
    {
        if ($rootPath === null) {
            $dirPath = realpath($dirPath);
            $rootPath = dirname($dirPath);
        }
        if (is_dir($dirPath)) {
            $rootLength = strlen($rootPath) + 1;
            $ret = [
                substr($dirPath, $rootLength) => self::changetime($dirPath)
            ];
            $arr = self::scanDir($dirPath);
            foreach ($arr as $file) {
                $filePath = $dirPath . DIRECTORY_SEPARATOR . $file;
                if (is_dir($filePath)) {
                    $ret += self::dirModifyTime($filePath);
                } else {
                    $ret[substr($filePath, $rootLength)] = self::changetime($filePath);
                }
            }
            return $ret;
        }
        return false;
    }

    public static function mediaInfo($filePath)
    {
        $filePath = realpath($filePath);
        $ret = [
            'file' => $filePath,
            'video' => [],
            'audio' => [],
            'subtitle' => [],
            'attachment' => [],
            'data' => [],
            'unknown' => []
        ];
        if ($filePath) {
            $modifyTime = self::changetime($filePath);
            $storage = self::getStorage();
            $storageKey = self::generateStorageKey($filePath, 'mediaInfo');
            if ($mediaInfo = $storage->retrieveJSON($storageKey, $modifyTime)) {
                $ret = $mediaInfo;
            } else {
                $command = sprintf('%s -i "%s" 2>&1', self::FFMPEG_PATH, $filePath);
                exec($command, $res);
                foreach ($res as $line) {
                    if (preg_match('/^\s*Stream #(\d+):(\d+)\(*(.*?)\)*:\s+(\w+):\s+([\w\d_]+)(.*)$/', $line, $match)) {
                        // array_shift($match);
                        // $ret[] = $match;
                        $key = $match[1] . ':' . $match[2];
                        $lang = $match[3];
                        $type = strtolower($match[4]);
                        $codec = $match[5];
                        $settings = $match[5] . $match[6];
                        if (! isset($ret[$type])) {
                            die($match[0]);
                        }
                        $ret[$type][$key] = [
                            'lang' => $lang,
                            'codec' => $codec,
                            'settings' => $settings
                        ];
                        if (preg_match('/(\d{2,4})x(\d{2,4})/', $settings, $m)) {
                            $ret[$type][$key]['width'] = $m[1];
                            $ret[$type][$key]['height'] = $m[2];
                        }
                    } elseif (preg_match('/^\s*Stream.+$/', $line, $match)) {
                        die($match[0]);
                    }
                }
                $storage->storeJSON($storageKey, $ret, $modifyTime);
            }
        }
        return $ret;
    }

    public static function extractArchive($archivePath, $targetDirectory)
    {
        if (! is_readable(self::ZIP_PATH)) {
            throw new Exception('7-Zip not found @ ' . self::ZIP_PATH);
        }
        if (! is_file($archivePath)) {
            return false;
        }
        $command = '"' . self::ZIP_PATH . '" ';
        $command .= sprintf(self::ZIP_QUERY_EXTRACT, $archivePath, $targetDirectory);
        exec($command);
        return true;
    }

    public static function archivePath($directoryPath, $archiveName, $checkUpdate = false)
    {
        $directoryPath = realpath($directoryPath);
        // $rootPath = realpath($directoryPath . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR;
        // $dirPath = substr($directoryPath, strlen($rootPath));
        
        if (! is_readable(self::ZIP_PATH)) {
            throw new Exception('7-Zip not found @ ' . self::ZIP_PATH);
        }
        if (is_file($archiveName) and max(self::dirModifyTime($directoryPath)) < self::changetime($archiveName)) {
            return false;
        }
        $command = '"' . self::ZIP_PATH . '" ';
        $command .= sprintf(self::ZIP_QUERY_UPDATE, $archiveName, $directoryPath);
        exec($command);
        return true;
    }

    private static function add2archive($path, $archive, $rootPath, &$fileCount = 0)
    {
        $ret = true;
        if (is_dir($rootPath . $path)) {
            $archive->addEmptyDir($path);
            $arr = self::scanDir($rootPath . $path);
            foreach ($arr as $childPath) {
                $ret = ($ret and self::add2archive($path . DIRECTORY_SEPARATOR . $childPath, $archive, $rootPath, $fileCount));
            }
        }
        if (is_file($rootPath . $path)) {
            $fileCount ++;
            if ($fileCount > self::ARCHIVE_MAX_FILES) {
                $fileCount = 0;
                $name = $archive->filename;
                $ret = ($ret and $archive->close() and $archive->open($name));
            }
            if ($ret) {
                $archive->addFile($rootPath . $path, $path, $fileCount);
            }
        }
        return $ret;
    }

    public static function downloadByURI($destPath, $sourceURI, array $options = [])
    {
        $ret = 'ERROR';
        $downloadCommand = isset($options['download-cmd']) ? $options['download-cmd'] : 'curl %s -o %s';
        $copyCommand = isset($options['copy-cmd']) ? $options['copy-cmd'] : 'copy %s %s /y';
        $successCommand = isset($options['success-cmd']) ? $options['success-cmd'] : null;
        $successPHP = isset($options['success-php']) ? $options['success-php'] : null;
        
        // $tempPath = tempnam(sys_get_temp_dir(), 'FS');
        $tempPath = temp_file(__CLASS__);
        $downloadExec = sprintf($downloadCommand, escapeshellarg($sourceURI), escapeshellarg($tempPath));
        // my_dump($downloadExec);
        $res = exec($downloadExec);
        // my_dump($res);
        if (file_exists($tempPath)) {
            if (self::size($tempPath)) {
                $download = true;
                if (file_exists($destPath)) {
                    if (md5_file($tempPath) === md5_file($destPath)) {
                        $ret = sprintf('File "%s" is already up to date!', $destPath);
                        $download = false;
                    }
                }
                if ($download) {
                    $copyExec = sprintf($copyCommand, escapeshellarg($tempPath), escapeshellarg($destPath));
                    $res = exec($copyExec);
                    if (file_exists($destPath)) {
                        $ret = sprintf('Updated file "%s"!', $destPath);
                        if ($successCommand) {
                            $successExec = sprintf($successCommand, escapeshellarg($destPath));
                            exec($successExec);
                        }
                        if ($successPHP) {
                            eval($successPHP);
                        }
                    }
                    // my_dump($res);
                }
            }
        }
        return $ret;
    }

    public static function getLinkByXPath($sourceURI, $linkQuery)
    {
        $ret = false;
        $sourceParam = parse_url($sourceURI);
        $doc = new DOMDocument();
        if (@$doc->loadHTMLFile($sourceURI)) {
            $xpath = new DOMXPath($doc);
            if ($uri = $xpath->evaluate(sprintf('string(%s)', $linkQuery))) {
                if (strpos($uri, '://') === false) {
                    if (strpos($uri, './') === 0) {
                        $uri = substr($uri, 2);
                    }
                    $uri = sprintf('%s://%s%s%s', $sourceParam['scheme'], $sourceParam['host'], $sourceParam['path'], $uri);
                }
                $ret = $uri;
            }
        }
        return $ret;
    }

    public static function loadCSV($path, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        $ret = null;
        if ($handle = fopen($path, 'r')) {
            $ret = [];
            while (($row = fgetcsv($handle, 0, $delimiter, $enclosure, $escape)) !== false) {
                $ret[] = $row;
            }
            fclose($handle);
        }
        return $ret;
    }

    protected static $base64Source = [
        '+',
        '/',
        '='
    ];

    protected static $base64Target = [
        '-',
        '_',
        '~'
    ];

    public static function base64Encode($fileName)
    {
        $fileName = base64_encode($fileName);
        $fileName = str_replace(self::$base64Source, self::$base64Target, $fileName);
        return $fileName;
    }

    public static function base64Decode($fileName)
    {
        $fileName = str_replace(self::$base64Target, self::$base64Source, $fileName);
        $fileName = base64_decode($fileName);
        return $fileName;
    }
}