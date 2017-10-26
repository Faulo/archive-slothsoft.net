<?php
namespace Slothsoft\Core;

class WebCrawler
{

    public $maxDepth = 0;

    public $maxDocs = 1;

    public $maxTime = 0;

    protected $url;

    protected $linkList;

    protected $errorList;

    public function __construct($url)
    {
        $this->url = $url;
        $this->linkList = [];
        $this->errorList = [];
    }

    public function crawl()
    {
        $href = $this->buildURL('', $this->url);
        $this->crawlURL($href);
        
        asort($this->linkList);
        
        $ret = [];
        $ret[] = $this->url;
        $ret[] = '';
        foreach ($this->linkList as $url => $res) {
            $ret[] = $res ? $res : sprintf('SKIP	wrong domain	%s', $url);
        }
        return implode(PHP_EOL, $ret);
    }

    protected function crawlURL($url, $depth = 0, $parentUrl = '')
    {
        if (! isset($this->linkList[$url])) {
            switch (true) {
                case count($this->linkList) >= $this->maxDocs:
                    $this->linkList[$url] = sprintf('SKIP	maxDocs	%s', $url);
                    break;
                case $depth > $this->maxDepth:
                    $this->linkList[$url] = sprintf('SKIP	maxDepth	%s', $url);
                    break;
                /*
                 * case $doc = \Storage::loadExternalDocument($url, $this->maxTime, null, ['followRedirects' => false]):
                 * $this->linkList[$url] = sprintf('OK document %s', $url);
                 * //my_dump($this->linkList);die();
                 *
                 * $xpath = new \DOMXPath($doc);
                 * $nodeList = $xpath->evaluate('//@href');
                 * foreach ($nodeList as $node) {
                 * $href = $xpath->evaluate('string(.)', $node);
                 * $href = $this->buildURL($href, $url);
                 * if (strlen($href)) {
                 * $this->crawlURL($href, $depth + 1, $url);
                 * }
                 * }
                 *
                 * break;
                 * //
                 */
                case $header = Storage::loadExternalHeader($url, $this->maxTime, null, [
                    'followRedirects' => false
                ]):
                    $this->linkList[$url] = sprintf('OK	header		%s', $url);
                    if ($header['status'] === 200) {
                        if (strpos($header['content-type'], 'text/html') === 0 or strpos($header['content-type'], 'application/xhtml+xml') === 0) {
                            if ($doc = Storage::loadExternalDocument($url, $this->maxTime, null, [
                                'followRedirects' => false
                            ])) {
                                $this->linkList[$url] = sprintf('OK	document	%s', $url);
                                // my_dump($this->linkList);die();
                                
                                $xpath = new \DOMXPath($doc);
                                $nodeList = $xpath->evaluate('//@href');
                                foreach ($nodeList as $node) {
                                    $href = $xpath->evaluate('string(.)', $node);
                                    $href = $this->buildURL($href, $url);
                                    if (strlen($href)) {
                                        $this->crawlURL($href, $depth + 1, $url);
                                    }
                                }
                            }
                        }
                    } else {
                        $this->linkList[$url] = sprintf('ERROR	not found	%s%s	ref URL:	%s', $url, PHP_EOL, $parentUrl);
                        if (isset($header['location'])) {
                            $this->linkList[$url] = sprintf('ERROR	redirect	%s%s	ref URL:	%s', $url, PHP_EOL, $parentUrl);
                        }
                    }
                    break;
                default:
                    $this->linkList[$url] = sprintf('ERROR	not found	%s%s	ref URL:	%s', $url, PHP_EOL, $parentUrl);
                    break;
            }
        }
    }

    protected function buildURL($url, $parentUrl = '')
    {
        $success = true;
        
        $url = preg_replace('/\?.*/', '', $url);
        $data = parse_url($url);
        $parentData = parse_url($parentUrl);
        if (! isset($parentData['path'])) {
            $parentData['path'] = '';
        }
        
        if (isset($data['scheme'])) {
            if ($data['scheme'] !== $parentData['scheme']) {
                $success = false;
            }
        } else {
            $data['scheme'] = $parentData['scheme'];
        }
        if (isset($data['host'])) {
            if ($data['host'] !== $parentData['host']) {
                $success = false;
            }
        } else {
            $data['host'] = $parentData['host'];
        }
        if (isset($data['path'])) {
            // my_dump([$data['path'], $parentData['path']]);
            if (substr($data['path'], 0, 1) !== '/') {
                if (strlen($parentData['path'])) {
                    $path = explode('/', $parentData['path']);
                    $val = array_pop($path);
                    $path[] = '';
                    $parentData['path'] = implode('/', $path);
                }
                $data['path'] = $parentData['path'] . $data['path'];
                // echo $data['path'] . PHP_EOL;
            }
        } else {
            $data['path'] = $parentData['path'];
        }
        if (strlen($data['path'])) {
            $path = $data['path'];
            $path = explode('/', $path);
            $newPath = [];
            foreach ($path as $i => $val) {
                switch ($val) {
                    case '.':
                        break;
                    case '..':
                        array_pop($newPath);
                        break;
                    default:
                        if (strlen($val) or implode('', $newPath) !== '') {
                            $newPath[] = $val;
                        }
                        break;
                }
            }
            $path = $newPath;
            // $path = array_filter($path, 'strlen');
            $path = '/' . implode('/', $path);
            $data['path'] = $path;
        }
        // $data['path'] = str_replace('/./', '/', $data['path']);
        // $data['path'] = preg_replace('/\/[^\/]+\/\.\.\//', '/', $data['path']);
        
        switch ($data['scheme']) {
            case 'javascript':
            case 'mailto':
                $ret = $url;
                $success = false;
                break;
            default:
                $ret = sprintf('%s://%s%s', $data['scheme'], $data['host'], $data['path']);
                break;
        }
        if (! isset($this->linkList[$ret])) {
            $this->linkList[$ret] = null;
        }
        return $success ? $ret : '';
    }
}