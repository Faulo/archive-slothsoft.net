<?php
namespace Slothsoft\CMS;


$ret = '';
$ret .= sprintf('[%s] Starting updating extensions...%s%s', date(DATE_DATETIME), PHP_EOL, PHP_EOL);

$docList = $this->getResourceDir('extensions/update', 'status');

foreach ($docList as $id => $doc) {
    // $id = substr($id, 0, -4);
    $updateFile = $doc->documentElement->getAttribute('realpath');
    $resDir = dirname(dirname($updateFile));
    $baseDir = $resDir . '/bin/' . $id;
    $extDir = $resDir . '/' . $id;
    if ($binDir = realpath($baseDir)) {
        $binDir .= DIRECTORY_SEPARATOR;
        if ($extDir = realpath($extDir)) {
            $extDir .= DIRECTORY_SEPARATOR;
            $ret .= sprintf('[%s] Updating "%s" at "%s":%s', date(DATE_DATETIME), $id, $binDir, PHP_EOL);
            
            $installFile = $binDir . 'install.rdf';
            if ($installDoc = self::loadDocument($installFile)) {
                $installPath = self::loadXPath($installDoc);
                $installPath->registerNamespace('em', self::NS_EM);
                if ($version = $installPath->evaluate('string(.//em:version)')) {
                    $ret .= sprintf('	Found version %s...%s', $version, PHP_EOL);
                    
                    $xpiFile = $extDir . $version . '.xpi';
                    $rdfFile = $extDir . $version . '.rdf';
                    
                    if (file_exists($rdfFile)) {
                        $ret .= sprintf('	Already up to date!%s', PHP_EOL);
                    } else {
                        $xpiFile = $extDir . 'signing/unsigned.xpi';
                        $rdfFile = $extDir . 'signing/install.rdf';
                        
                        $ret .= sprintf('	Creating XPI at %s...%s', $xpiFile, PHP_EOL);
                        if (file_exists($xpiFile)) {
                            unlink($xpiFile);
                        }
                        
                        // http://www.rogerethomas.com/blog/recursively-zip-entire-directory-using-php
                        $dir = getcwd();
                        chdir($binDir);
                        $command = sprintf('7z a "%s" *', $xpiFile);
                        exec($command);
                        
                        chdir($dir);
                        /*
                         * $zip = new ZipArchive();
                         * $zip->open($xpiFile, ZipArchive::CREATE);
                         * $files = new RecursiveIteratorIterator(
                         * new RecursiveDirectoryIterator($binDir),
                         * RecursiveIteratorIterator::SELF_FIRST
                         * );
                         * $baseName = realpath($binDir);
                         * foreach ($files as $file) {
                         * if (in_array(substr($file, strrpos($file, DIRECTORY_SEPARATOR)+1), ['.', '..'])) {
                         * continue;
                         * }
                         * $relative = substr($file, strlen($baseName));
                         * if (is_dir($file)) {
                         * // Add directory
                         * $added = $zip->addEmptyDir(trim($relative, "\\/"));
                         * if (!$added) {
                         * throw new Exception('Unable to add directory named: ' . trim($relative, "\\/"));
                         * }
                         * } elseif (is_file($file)) {
                         * // Add file
                         * $added = $zip->addFromString(trim($relative, "\\/"), file_get_contents($file));
                         * if (!$added) {
                         * throw new Exception('Unable to add file named: ' . trim($relative, "\\/"));
                         * }
                         * }
                         * }
                         * $zip->close();
                         * //
                         */
                        
                        copy($installFile, $rdfFile);
                        
                        $ret .= sprintf('	UPDATE! Please let Mozilla sign XPI at https://addons.mozilla.org/en-US/developers/addons%s', PHP_EOL);
                        /*
                         * $uri = sprintf('http://slothsoft.net/getFragment.php/extensions/update?id=%s', $id);
                         * if ($updateDoc = self::loadExternalDocument($uri, 'xml', 0)) {
                         * $updateDoc->save($updateFile);
                         * $ret .= sprintf(' UPDATE! Please sign file "%s"~%s', $updateFile, PHP_EOL);
                         * }
                         * //
                         */
                    }
                }
            }
        }
    }
}

$this->progressStatus |= self::STATUS_RESPONSE_SET;
$this->httpResponse->setStatus(HTTPResponse::STATUS_OK);
$this->httpResponse->setBody($ret);
$this->httpResponse->setEtag(HTTPResponse::calcEtag($ret));