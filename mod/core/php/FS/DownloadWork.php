<?php
namespace Slothsoft\Core\FS;

use Slothsoft\CMS\HTTPFile;
use Slothsoft\Core\FileSystem;
use Slothsoft\Core\Image;
use Slothsoft\Core\Storage;
use Slothsoft\Core\Lambda\Stackable;
use DOMDocument;
use DOMXPath;
use Exception;

class DownloadWork extends Stackable
{

    const HTTP_CACHETIME = TIME_MINUTE;

    private $_options;

    // protected $_result;
    // protected $_log;
    // protected $_isDone;
    public function __construct(array $options)
    {
        if (! isset($options['mode'])) {
            $options['mode'] = 'index';
        }
        $this->_options = (array) $options;
        // $this->_log = '';
        // $this->_isDone = false;
    }

    public function getName()
    {
        $options = (array) $this->_options;
        return isset($options['chapter']) ? sprintf('%s: %d', $options['name'], $options['chapter']) : $options['name'];
    }

    /*
     * public function getResult() {
     * return $this->_result;
     * }
     * public function getLog() {
     * return $this->_log;
     * }
     * public function isDone() {
     * return $this->_isDone;
     * }
     * //
     */
    public function work()
    {
        $ret = [];
        $options = (array) $this->_options;
        try {
            // clearstatcache();
            switch ($options['mode']) {
                case 'index':
                    $options['mode'] = 'fetch';
                    switch ($options['type']) {
                        case 'comic':
                            $ret = $this->workIndexComic($options);
                            break;
                        case 'files':
                            $ret = $this->workIndexFiles($options);
                            break;
                        case 'podcast':
                            $ret = $this->workIndexPodcast($options);
                            break;
                        case 'rss':
                            $ret = $this->workIndexRSS($options);
                            break;
                        case 'manga':
                            $ret = $this->workIndexManga($options);
                            break;
                        case 'hentai':
                            $ret = $this->workIndexHentai($options);
                            break;
                        case 'tool':
                            $ret = $this->workIndexTool($options);
                            break;
                        case 'youtube':
                            $ret = $this->workIndexYoutube($options);
                            break;
                        case 'file':
                            $ret = $this->workIndexFile($options);
                            break;
                        case 'php':
                            $ret = $this->workIndexPHP($options);
                            break;
                    }
                    break;
                case 'fetch':
                    $options['mode'] = 'done';
                    switch ($options['type']) {
                        case 'comic':
                            $ret = $this->workFetchComic($options);
                            break;
                        case 'manga':
                            $ret = $this->workFetchManga($options);
                            break;
                        case 'hentai':
                            $ret = $this->workFetchHentai($options);
                            break;
                        case 'file':
                            $ret = $this->workFetchFile($options);
                            break;
                        case 'download':
                            $ret = $this->workFetchDownload($options);
                            break;
                        case 'php':
                            $ret = $this->workFetchPHP($options);
                            break;
                    }
                    break;
                case 'done':
                    break;
            }
            if (! is_array($ret)) {
                throw new Exception('DownloadWork::work did not return an array?!');
            }
        } catch (Exception $e) {
            $this->log($e->getMessage(), true);
            $this->log($options);
            $this->log($ret);
            $ret = [];
        }
        // $this->_result = $ret;
        // $this->_isDone = true;
        return $ret;
    }

    protected function workIndexComic(array $options)
    {
        $ret = [];
        $targetRoot = $options['dest-root'];
        $targetURI = $options['dest-uri'];
        $sourceURI = $options['source-uri'];
        $blackList = $options['blacklist'];
        $blackList = explode("\n", trim($blackList));
        foreach ($blackList as &$val) {
            $val = trim($val);
        }
        unset($val);
        
        if (! is_dir($targetRoot)) {
            mkdir($targetRoot, 0777, true);
        }
        
        if ($targetPath = realpath($targetRoot)) {
            $i = 1;
            $comicList = [];
            
            do {
                $nextURI = null;
                if (isset($comicList[$sourceURI])) {
                    break;
                }
                $xpath = Storage::loadExternalXPath($sourceURI, TIME_YEAR);
                if ($xpath) {
                    if ($xpath) {
                        $title = $xpath->evaluate($options['source-xpath-title']);
                        $nextURI = $xpath->evaluate($options['source-xpath-uri']);
                        $image = $xpath->evaluate($options['source-xpath-image']);
                        
                        $nextURI = strlen($nextURI) ? $this->_fixURI($nextURI, $sourceURI) : null;
                        $image = strlen($image) ? $this->_fixURI($image, $sourceURI) : null;
                        
                        if ($image) {
                            if (! strlen($title)) {
                                $title = "#$i";
                            }
                            $ext = substr($image, strrpos($image, '.'));
                            $name = sprintf('%04d%s', $i, $ext);
                            $path = sprintf('%s%s%s', $targetPath, DIRECTORY_SEPARATOR, $name);
                            // $thumbFile = sprintf('%s%s%04d.png', $thumbDir, DIRECTORY_SEPARATOR, $i);
                            
                            $arr = [];
                            $arr['key'] = sprintf('%04d', $i);
                            $arr['title'] = $title;
                            $arr['href'] = $sourceURI;
                            $arr['source'] = $image;
                            $arr['path'] = $path;
                            $arr['image'] = $targetURI . $name;
                            if (in_array($title, $blackList)) {
                                $arr['hidden'] = '';
                            }
                            
                            $comicList[$sourceURI] = $arr;
                            $i ++;
                        }
                    }
                }
                $sourceURI = $nextURI;
            } while ($sourceURI and $i < $options['page-count']);
            
            $this->log(sprintf('Prepared to verify %d comic strips of %s!', count($comicList), $options['name']));
            
            $options['comicList'] = array_values($comicList);
            $ret[] = $options;
        }
        return $ret;
    }

    protected function workFetchComic(array $options)
    {
        $ret = [];
        
        $downloadCount = 0;
        foreach ($options['comicList'] as &$comic) {
            /*
             * [title] => Episode 001: We’re going where?
             * [key] => 0001
             * [href] => http://www.nuklearpower.com/2001/03/02/episode-001-were-going-where/
             * [source] => http://www.nuklearpower.com/comics/8-bit-theater/010302.jpg
             * [path] => C:\xampp\htdocs\mod\comics\res\8bit\0001.jpg
             * [image] => /getResource.php/comics/8bit/0001.jpg
             * [width] => 612
             * [height] => 936
             * [mime] => image/jpeg
             * //
             */
            if ($downloadCount < $options['download-count'] and ! file_exists($comic['path'])) {
                if ($file = HTTPFile::createFromURL($comic['source'])) {
                    $downloadCount ++;
                    $file->copyTo(dirname($comic['path']), basename($comic['path']));
                }
            }
            if (file_exists($comic['path'])) {
                $comic += Image::imageInfo($comic['path']);
                // $this->log($comic);
            }
        }
        unset($comic);
        
        $destFile = $options['dest-root'] . DIRECTORY_SEPARATOR . 'index.xml';
        $doc = new DOMDocument();
        $parentNode = $doc->createElement('comic');
        $parentNode->setAttribute('name', $options['name']);
        foreach ($options['comicList'] as $comic) {
            $node = $doc->createElement('page');
            foreach ($comic as $key => $val) {
                $node->setAttribute($key, $val);
            }
            $parentNode->appendChild($node);
        }
        $doc->appendChild($parentNode);
        $doc->save($destFile);
        $this->log(sprintf('Created index file %s containing %d pages for %s!', $destFile, count($options['comicList']), $options['name']));
        return $ret;
    }

    protected function workIndexFiles(array $options)
    {
        $ret = [];
        $targetRoot = $options['dest-root'];
        $sourceURI = $options['source-uri'];
        
        if ($targetPath = realpath($targetRoot)) {
            $fileList = FileSystem::scanDir($targetPath);
            $fileList = array_flip($fileList);
            $targetPath .= DIRECTORY_SEPARATOR;
            
            $options['dest-root'] = $targetPath;
            $options['type'] = 'file';
            
            if ($xpath = $this->downloadXPath($sourceURI)) {
                $nodeList = $xpath->evaluate($options['source-xpath']);
                foreach ($nodeList as $node) {
                    $name = $xpath->evaluate($options['source-xpath-name'], $node);
                    $uri = $this->_fixURI($xpath->evaluate($options['source-xpath-uri'], $node), $sourceURI);
                    
                    if ($name and $uri) {
                        $file = $this->_fixFilename($name, $options['dest-ext']);
                        if (! isset($fileList[$file])) {
                            $options['dest-path'] = $targetPath . $file;
                            $options['source-uri'] = $uri;
                            $ret[] = $options;
                            // break;
                        }
                    }
                }
                $this->log(sprintf('Prepared to download %d files of %s!', count($ret), $options['name']));
            }
        }
        return $ret;
    }

    protected function workIndexPodcast(array $options)
    {
        $ret = [];
        $targetRoot = $options['dest-root'];
        $name = $options['name'];
        $sourceHost = $options['source-host'];
        $sourcePath = $options['source-path'];
        
        $targetPath = $targetRoot . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
        
        if (! is_dir($targetPath)) {
            mkdir($targetPath, 0777, true);
        }
        if ($targetPath = realpath($targetPath)) {
            $targetPath .= DIRECTORY_SEPARATOR;
            
            $options['dest-root'] = $targetPath;
            $options['type'] = 'file';
            
            $uriList = $this->downloadURIList($sourceHost . $sourcePath, $options['source-xpath']);
            foreach ($uriList as $sourceURI) {
                if ($xpath = $this->downloadXPath($sourceURI)) {
                    $name = $xpath->evaluate('normalize-space(//h2)');
                    $uri = $this->_fixURI($xpath->evaluate('string(//a[normalize-space(.) = "Download"]/@href)'), $sourceURI);
                    
                    if ($name and $uri) {
                        $path = $targetPath . $this->_fixFilename($name, 'mp3');
                        if (! file_exists($path)) {
                            $options['dest-path'] = $path;
                            $options['source-uri'] = $uri;
                            $ret[] = $options;
                        }
                    }
                }
            }
        }
        
        $this->log(sprintf('Prepared to download %d podcasts of %s!', count($ret), $options['name']));
        return $ret;
    }

    protected function workIndexRSS(array $options)
    {
        $ret = [];
        $targetRoot = $options['dest-root'];
        $name = $options['name'];
        $sourceHost = $options['source-host'];
        $sourcePath = $options['source-path'];
        
        $targetPath = $targetRoot . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
        
        if (! is_dir($targetPath)) {
            mkdir($targetPath);
        }
        $options['dest-root'] = $targetPath;
        $options['type'] = 'file';
        
        $sourceURI = $sourceHost . $sourcePath;
        if ($xpath = $this->downloadXPath($sourceURI)) {
            $itemNodeList = $xpath->evaluate(sprintf('//item[enclosure][contains(title, "%s")]', $name));
            foreach ($itemNodeList as $itemNode) {
                $title = $xpath->evaluate('normalize-space(title)', $itemNode);
                $time = $xpath->evaluate('normalize-space(pubDate)', $itemNode);
                $time = strtotime($time);
                $uri = $this->_fixURI($xpath->evaluate('normalize-space(enclosure/@url)', $itemNode), $sourceURI);
                // $type = $xpath->evaluate('normalize-space(enclosure/@type)', $itemNode);
                $file = pathinfo($uri, PATHINFO_BASENAME);
                $file = preg_replace('/\?.*/', '', $file);
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                
                if ($title and $uri) {
                    $name = $title;
                    if (isset($options['preg-file']) and preg_match($options['preg-file'], $file, $match)) {
                        $name = sprintf('%03d', $match[1]);
                    }
                    if (isset($options['preg-title']) and preg_match($options['preg-title'], $title, $match)) {
                        $name = sprintf('%03d - %s', $match[1], $match[2]);
                    }
                    $file = $this->_fixFilename($name, $ext);
                    
                    $path = $targetPath . $file;
                    if (file_exists($path)) {
                        if ($time > 0) {
                            touch($path, $time);
                        }
                    } else {
                        $options['dest-path'] = $path;
                        $options['source-uri'] = $uri;
                        if ($time > 0) {
                            $options['dest-time'] = $time;
                        }
                        $ret[] = $options;
                    }
                }
            }
        }
        
        $this->log(sprintf('Prepared to download %d podcasts of %s!', count($ret), $options['name']));
        return $ret;
    }

    protected function workIndexManga(array $options)
    {
        $ret = [];
        $targetRoot = $options['dest-root'];
        $name = $options['name'];
        $sourceHost = $options['source-host'];
        $sourcePath = $options['source-path'];
        
        $targetPath = $targetRoot . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
        
        if (! is_dir($targetPath)) {
            mkdir($targetPath);
        }
        $options['dest-root'] = $targetPath;
        
        $startChapter = 0;
        $chapterList = FileSystem::scanDir($targetPath, FileSystem::SCANDIR_EXCLUDE_FILES);
        foreach ($chapterList as $chapter) {
            if (preg_match('/(\d+)/', $chapter, $match)) {
                $no = (int) $match[1];
                if ($no > $startChapter) {
                    $startChapter = $no;
                }
            }
        }
        if (isset($options['chapter-start'])) {
            $startChapter = (int) $options['chapter-start'];
        }
        $notFound = 0;
        for ($i = $startChapter; $i - $startChapter < $options['chapter-count']; $i ++) {
            $options['chapter'] = $i;
            $options['page'] = 1;
            $options['source-uri'] = $options['source-host'] . sprintf($options['source-path'], $options['chapter'], $options['page']);
            // $this->log($options['source-uri']);
            // $this->log($options);
            if ($res = $this->downloadString($options['source-uri'], $options['source-xpath-image'])) {
                $notFound = 0;
                $ret[] = $options;
            } else {
                $notFound ++;
                if ($notFound > (int) $options['data-missing-count']) {
                    break;
                }
            }
        }
        $this->log(sprintf('Prepared to download %d chapter(s) of %s! (%s)', count($ret), $options['name'], $options['source-uri']));
        return $ret;
    }

    protected function workFetchManga(array $options)
    {
        $ret = [];
        $lastImg = null;
        $lastData = null;
        $firstURI = null;
        $pageCount = 0;
        $chapterName = sprintf($options['dest-path'], $options['name'], $options['chapter']);
        $targetDir = $options['dest-root'] . $chapterName . DIRECTORY_SEPARATOR;
        if (! is_dir($targetDir)) {
            mkdir($targetDir);
        }
        $notFound = 0;
        $lastExt = null;
        for ($i = $options['page']; $i < $options['page-count']; $i ++) {
            $continue = false;
            $options['page'] = $i;
            $options['source-uri'] = $options['source-host'] . sprintf($options['source-path'], $options['chapter'], $options['page']);
            if (! $firstURI) {
                $firstURI = $options['source-uri'];
            }
            if ($lastExt) {
                $targetFile = sprintf($options['dest-file'], $options['page'], $ext);
                $target = $targetDir . $targetFile;
                if (file_exists($target)) {
                    $continue = true;
                    $lastData = file_get_contents($target);
                }
            }
            if (! $continue) {
                if ($img = $this->downloadURI($options['source-uri'], $options['source-xpath-image'])) {
                    if ($img === $lastImg) {} else {
                        $lastImg = $img;
                        
                        $ext = $img;
                        if (strlen($ext)) {
                            $ext = explode('.', $ext);
                            $ext = array_pop($ext);
                            if (strlen($ext)) {
                                $ext = explode('?', $ext);
                                $ext = array_shift($ext);
                                if (strlen($ext)) {
                                    $ext = explode('#', $ext);
                                    $ext = array_shift($ext);
                                }
                            }
                        }
                        $lastExt = $ext;
                        
                        $targetFile = sprintf($options['dest-file'], $options['page'], $ext);
                        
                        $target = $targetDir . $targetFile;
                        
                        if (file_exists($target)) {
                            // $ret[] = $target;
                            $continue = true;
                        } else {
                            @$data = file_get_contents($img);
                            
                            if ($data === $lastData) {} else {
                                $lastData = $data;
                                if (strlen($data) > $options['data-length-min']) {
                                    // $this->log(sprintf('downloading %s ...', $img));
                                    file_put_contents($target, $data);
                                    // $ret[] = $target;
                                    $continue = true;
                                    $pageCount ++;
                                } else {
                                    // $ret .= sprintf(' ERROR downloading %s! °A°%s', $img, PHP_EOL);
                                }
                            }
                        }
                    }
                } else {
                    // $this->log(sprintf('No manga page image? %s (%s)', $options['source-uri'], $options['source-xpath-image']), true);
                }
            }
            if ($continue) {
                $notFound = 0;
            } else {
                $notFound ++;
                if ($notFound > (int) $options['data-missing-count']) {
                    break;
                }
            }
        }
        if ($pageCount) {
            $this->log(sprintf('Downloaded %d pages for %s! (%s)', $pageCount, $chapterName, $firstURI), true);
        } else {
            $this->log(sprintf('Already here: %s! (%s)', $chapterName, $firstURI));
        }
        return $ret;
    }

    protected function workIndexHentai(array $options)
    {
        $ret = [];
        $targetRoot = $options['dest-root'];
        $name = $options['name'];
        $sourceHost = $options['source-host'];
        $sourcePath = $options['source-path'];
        
        $targetPath = $targetRoot . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
        
        if (! is_dir($targetPath)) {
            mkdir($targetPath);
        }
        $options['dest-root'] = $targetPath;
        
        $options['source-uri'] = $options['source-host'] . $options['source-path'];
        $notFound = 0;
        
        do {
            $xpath = $options['downloader']->getXPath($options['source-uri']);
            if (! $xpath) {
                // $this->log(Storage::loadExternalFile($options['source-uri']));
                break;
            }
            $nodeList = $this->downloadNodeList($xpath, $options['source-xpath']);
            $nextURI = $this->downloadString($xpath, $options['source-xpath-next']);
            $nextURI = $this->_fixURI($nextURI, $options['source-uri']);
            if ($nodeList) {
                $notFound = 0;
                foreach ($nodeList as $node) {
                    $uri = $node->getAttribute('href');
                    if (strlen($uri)) {
                        $uri = $this->_fixURI($uri, $options['source-uri']);
                        
                        $opt = $options;
                        $opt['source-uri'] = $uri;
                        $ret[$uri] = $opt;
                    }
                    /*
                     * $id = preg_match('/\d+/', $uri, $match)
                     * ? (int) $match[0]
                     * : null;
                     * $title = $node->textContent;
                     * $title = FileSystem::filenameSanitize($title);
                     * if ($uri and $title) {
                     * $options['chapter'] = $title;
                     * $options['dest-path'] = $title . DIRECTORY_SEPARATOR;
                     * $options['source-uri'] = $uri;
                     * if (isset($options['source-xpath-download'])) {
                     * //hentai.ms
                     * if (!is_dir($options['dest-root'] . $options['dest-path'])) {
                     * $ret[$uri] = $options;
                     * }
                     * } else {
                     * //nhentai.net
                     * if ($id) {
                     * $options['chapter'] = $id;
                     * $options['page'] = 1;
                     * $options['type'] = 'manga';
                     * $options['source-path'] = '/g/%d/%d/';
                     *
                     * $ret[$uri] = $options;
                     * }
                     * }
                     * }
                     * //
                     */
                }
            } else {
                $notFound ++;
            }
            $options['source-uri'] = $nextURI;
        } while ($nextURI and $notFound < (int) $options['data-missing-count'] and count($ret) < $options['chapter-count']);
        $this->log(sprintf('Prepared to download %d manga of %s! (%s)', count($ret), $options['name'], $options['source-uri']));
        return $ret;
    }

    protected function workFetchHentai(array $options)
    {
        $ret = [];
        if (isset($options['source-xpath-download'])) {
            if ($uri = $this->downloadURI($options['source-uri'], $options['source-xpath-download'])) {
                if ($file = HTTPFile::createFromURL($uri)) {
                    FileSystem::extractArchive($file->getPath(), $options['dest-root'] . $options['dest-path']);
                    $this->log(sprintf('Downloaded "%s"!', $options['chapter']));
                } else {
                    $this->log(sprintf('Download Archive not found: %s!', $uri));
                }
            } else {
                $this->log(sprintf('Download URL not found: %s! (%s)', $options['source-uri'], $options['source-xpath-download']));
            }
        }
        if (isset($options['source-xpath-read'])) {
            $xpath = $options['downloader']->getXPath($options['source-uri']);
            if ($xpath) {
                $title = $this->downloadString($xpath, $options['source-xpath-title']);
                $title = FileSystem::filenameSanitize($title);
                $uri = $this->downloadString($xpath, $options['source-xpath-read']);
                $uri = $this->_fixURI($uri, $options['source-uri']);
                
                $path = $options['dest-root'] . $title . DIRECTORY_SEPARATOR;
                
                if (strlen($title) and strlen($uri)) {
                    $this->log(sprintf('Downloading hentai "%s" (%s)', $title, $uri));
                    
                    $xpath = $options['downloader']->getXPath($uri);
                    foreach ($xpath->evaluate('//script') as $scriptNode) {
                        if (preg_match('~var chapters = ([^;]+);~', $scriptNode->textContent, $match)) {
                            $chapters = $match[1];
                            $chapters = json_decode($chapters, true);
                            if ($chapters) {
                                if (! is_dir($path)) {
                                    mkdir($path, 0777, true);
                                }
                                foreach ($chapters as $chapter) {
                                    $image = $chapter['image'];
                                    $file = sprintf('%s%03d.%s', $path, $chapter['page'], pathinfo($chapter['image'], PATHINFO_EXTENSION));
                                    
                                    if (file_exists($file)) {
                                        // nothing to do \o/
                                    } else {
                                        if ($data = $options['downloader']->getFile($image)) {
                                            file_put_contents($file, $data);
                                        }
                                    }
                                }
                                if (! FileSystem::scanDir($path)) {
                                    unlink($path);
                                }
                            }
                        }
                    }
                }
            }
        }
        /*
         * if ($uri = $this->downloadURI($options['source-uri'], $options['source-xpath-download'])) {
         * if ($file = HTTPFile::createFromURL($uri)) {
         * FileSystem::extractArchive($file->getPath(), $options['dest-root'] . $options['dest-path']);
         * $this->log(sprintf('Downloaded "%s"!', $options['chapter']));
         * } else {
         * $this->log(sprintf('Download Archive not found: %s!', $uri));
         * }
         * } else {
         * $this->log(sprintf('Download URL not found: %s! (%s)', $options['source-uri'], $options['source-xpath-download']));
         * }
         * //
         */
        return $ret;
    }

    public function workIndexTool(array $options)
    {
        $ret = [];
        $sourceURI = $options['source-uri'];
        if (isset($options['source-xpath'])) {
            $sourceXPathList = [
                $options['source-xpath']
            ];
        } else {
            $sourceXPathList = [];
            for ($i = 0; $i < 10; $i ++) {
                if (isset($options['source-xpath-' . $i])) {
                    $sourceXPathList[] = $options['source-xpath-' . $i];
                }
            }
        }
        $options['type'] = 'file';
        while (count($sourceXPathList)) {
            $sourceXPath = array_shift($sourceXPathList);
            $this->log(sprintf('Checking website "%s"...', $sourceURI));
            if ($uri = $this->downloadURI($sourceURI, $sourceXPath)) {
                if (count($sourceXPathList)) {
                    $sourceURI = $uri;
                } else {
                    $options['source-uri'] = $uri;
                    $ret[] = $options;
                }
            } else {
                $this->log(sprintf('Could not find URL at %s (%s) ???', $sourceURI, $sourceXPath), true);
                break;
            }
        }
        // $this->log(sprintf('Prepared to download %d files for %s!', count($ret), $options['name']));
        return $ret;
    }

    public function workIndexFile(array $options)
    {
        $ret = [];
        $ret[] = $options;
        $this->log(sprintf('Prepared to download %s!', $options['name']));
        return $ret;
    }

    public function workFetchFile(array $options)
    {
        $ret = [];
        if ($tempFile = HTTPFile::createFromURL($options['source-uri'])) {
            $tempPath = $tempFile->getPath();
            $destPath = $options['dest-path'];
            
            $copy = true;
            if (file_exists($destPath)) {
                if (md5_file($tempPath) === md5_file($destPath)) {
                    $this->log(sprintf('File "%s" is already up to date!', $destPath));
                    $copy = false;
                }
            }
            if ($copy) {
                if ($tempFile->copyTo(dirname($destPath), basename($destPath), $options['copy-cmd'])) {
                    // $copyExec = sprintf($options['copy-cmd'], escapeshellarg($tempPath), escapeshellarg($destPath));
                    // $res = exec($copyExec);
                    // if (file_exists($destPath)) {
                    if (isset($options['dest-time'])) {
                        touch($destPath, $options['dest-time']);
                    }
                    $this->log(sprintf('Updated file "%s"!', $destPath), true);
                    if ($options['success-cmd']) {
                        $successExec = sprintf($options['success-cmd'], escapeshellarg($destPath));
                        // $res = exec($successExec);
                        pclose(popen($successExec, 'r')); // async maybe
                    }
                    // $this->log($options['success-php']);
                    if ($options['success-php']) {
                        try {
                            $res = $this->_eval($options['success-php']);
                        } catch (Exception $e) {
                            $this->log($e->getMessage(), true);
                        }
                    }
                } else {
                    $this->log(sprintf('Copy failed??? (%s to %s)', json_encode($tempPath), json_encode($destPath)), true);
                    $this->log(base64_encode($destPath));
                    // $this->log(sprintf('Copy failed??? (%s)', $copyExec), true);
                    // $this->log(json_encode($res));
                }
                // my_dump($res);
            }
        } else {
            $this->log(sprintf('Download failed??? (%s)', $options['source-uri']), true);
        }
        return $ret;
    }

    public function workIndexPHP(array $options)
    {
        $ret = [];
        $ret[] = $options;
        $this->log(sprintf('Prepared to execute %s!', $options['name']));
        return $ret;
    }

    public function workFetchPHP(array $options)
    {
        $ret = [];
        if ($tempFile = HTTPFile::createFromPHP($options['source-uri'])) {
            $tempPath = $tempFile->getPath();
            $destPath = $options['dest-path'];
            
            $copy = true;
            if (file_exists($destPath)) {
                if (md5_file($tempPath) === md5_file($destPath)) {
                    $this->log(sprintf('File "%s" is already up to date!', $destPath));
                    $copy = false;
                }
            }
            if ($copy) {
                if ($tempFile->copyTo(dirname($destPath), basename($destPath), $options['copy-cmd'])) {
                    // $copyExec = sprintf($options['copy-cmd'], escapeshellarg($tempPath), escapeshellarg($destPath));
                    // $res = exec($copyExec);
                    // if (file_exists($destPath)) {
                    if (isset($options['dest-time'])) {
                        touch($destPath, $options['dest-time']);
                    }
                    $this->log(sprintf('Updated file "%s"!', $destPath), true);
                    if ($options['success-cmd']) {
                        $successExec = sprintf($options['success-cmd'], escapeshellarg($destPath));
                        // $res = exec($successExec);
                        pclose(popen($successExec, 'r')); // async maybe
                    }
                    // $this->log($options['success-php']);
                    if ($options['success-php']) {
                        try {
                            $res = $this->_eval($options['success-php']);
                        } catch (Exception $e) {
                            $this->log($e->getMessage(), true);
                        }
                    }
                } else {
                    $this->log(sprintf('Copy failed??? (%s to %s)', json_encode($tempPath), json_encode($destPath)), true);
                    $this->log(base64_encode($destPath));
                    // $this->log(sprintf('Copy failed??? (%s)', $copyExec), true);
                    // $this->log(json_encode($res));
                }
                // my_dump($res);
            }
        } else {
            $this->log(sprintf('PHP failed??? (%s)', $downloadExec), true);
            $this->log(json_encode($res));
        }
        return $ret;
    }

    /*
     * public function workIndexYoutube(array $options) {
     * $ret = [];
     * $sourceURI = $options['source-uri'];
     * $this->log(sprintf('Checking "%s" at "%s"...', $options['name'], $sourceURI));
     * $options['type'] = 'download';
     *
     * if (!file_exists($options['dest-root'])) {
     * mkdir($options['dest-root'], 0777, true);
     * }
     *
     * if ($tempPath = realpath($options['dest-root'])) {
     * $tempPath .= DIRECTORY_SEPARATOR . $options['dest-file'];
     *
     * $options['download-cmd'] = sprintf($options['download-cmd'], escapeshellarg(urldecode($options['source-uri'])), $tempPath);
     * $ret[] = $options;
     * }
     * return $ret;
     * }
     * public function workFetchDownload(array $options) {
     * $ret = [];
     * $this->log(sprintf('Downloading "%s"...', $options['name']));
     * //$this->log($options['download-cmd']);
     *
     * $oldList = FileSystem::scanDir($options['dest-root']);
     * exec($options['download-cmd'], $res);
     * $newList = FileSystem::scanDir($options['dest-root']);
     *
     * foreach ($res as $row) {
     * if (strpos($row, 'ERROR') !== false) {
     * $this->log($row, true);
     * }
     * }
     *
     * $list = array_diff($newList, $oldList);
     * foreach ($list as $file) {
     * $this->log(sprintf('Downloaded "%s"!', $file), true);
     * }
     *
     * return $ret;
     * }
     * //
     */
    protected function downloadXPath($sourceURI)
    {
        if ($sourceURI instanceof DOMXPath) {
            return $sourceURI;
        }
        $ret = null;
        try {
            if ($xpath = Storage::loadExternalXPath($sourceURI, self::HTTP_CACHETIME)) {
                $ret = $xpath;
            }
        } catch (Exception $e) {
            $this->log($e->getMessage(), true);
        }
        return $ret;
    }

    protected function downloadNode($sourceURI, $query)
    {
        $ret = null;
        if ($xpath = $this->downloadXPath($sourceURI)) {
            $ret = $xpath->evaluate($query);
        }
        return $ret;
    }

    protected function downloadNodeList($sourceURI, $query)
    {
        $ret = [];
        if ($xpath = $this->downloadXPath($sourceURI)) {
            $nodeList = $xpath->evaluate($query);
            foreach ($nodeList as $node) {
                $ret[] = $node;
            }
        }
        return $ret;
    }

    protected function downloadString($sourceURI, $query)
    {
        $ret = null;
        if ($xpath = $this->downloadXPath($sourceURI)) {
            $ret = $xpath->evaluate(sprintf('string(%s)', $query));
        } else {
            // $this->log(sprintf('downloadString could not download: %s', $sourceURI), true);
        }
        return $ret;
    }

    protected function downloadStringList($sourceURI, $query)
    {
        $ret = [];
        if ($xpath = $this->downloadXPath($sourceURI)) {
            $nodeList = $xpath->evaluate($query);
            if (is_object($nodeList)) {
                foreach ($nodeList as $node) {
                    $ret[] = $xpath->evaluate('string(.)', $node);
                }
            } else {
                $ret[] = $nodeList;
            }
        }
        return $ret;
    }

    protected function downloadURI($sourceURI, $query)
    {
        $ret = null;
        $uri = $this->downloadString($sourceURI, $query);
        if (strlen($uri)) {
            $ret = $this->_fixURI($uri, $sourceURI);
        }
        return $ret;
    }

    protected function downloadURIList($sourceURI, $query)
    {
        $ret = [];
        $uriList = $this->downloadStringList($sourceURI, $query);
        foreach ($uriList as $uri) {
            if (strlen($uri)) {
                $ret[] = $this->_fixURI($uri, $sourceURI);
            }
        }
        return $ret;
    }

    protected function _fixFilename($name, $ext = null)
    {
        return $ext === null ? FileSystem::filenameSanitize($name) : sprintf('%s.%s', FileSystem::filenameSanitize($name), $ext);
    }

    protected function _fixURI($uri, $sourceURI)
    {
        if (substr($uri, 0, 2) === '//') {
            $uri = 'http:' . $uri;
        }
        $ret = $uri;
        $sourceParam = parse_url($sourceURI);
        if (strpos($ret, '://') === false) {
            if (strpos($ret, '/') === 0) {
                $sourceParam['path'] = '';
            }
            if (strpos($ret, './') === 0) {
                $ret = substr($ret, 2);
            }
            if (strlen($sourceParam['path'])) {
                $i = strrpos($sourceParam['path'], '/');
                if ($i !== null) {
                    $sourceParam['path'] = substr($sourceParam['path'], 0, $i + 1);
                }
            }
            $ret = sprintf('%s://%s%s%s', $sourceParam['scheme'], $sourceParam['host'], $sourceParam['path'], $ret);
        }
        return $ret;
    }

    protected function log($message, $important = false)
    {
        if (! is_string($message)) {
            $message = print_r($message, true);
        }
        if ($important) {
            $message = '!!! ' . $message;
        } else {
            $message = '    ' . $message;
        }
        return parent::log($message);
        // $this->_log .= sprintf('[%s] %s %s%s', date(DATE_DATETIME), $important, $message, PHP_EOL);
    }

    protected function _eval($code)
    {
        return eval($code);
    }
}