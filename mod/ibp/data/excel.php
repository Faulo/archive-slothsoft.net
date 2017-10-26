<?php
namespace VPlan;

$fileDir = __DIR__ . DIRECTORY_SEPARATOR . 'input-excel';

$retFragment = $dataDoc->createDocumentFragment();

$requestList = $this->httpRequest->getInputValue('file', []);

$outputList = [];
$outputDir = null;

$dirList = \FileSystem::scanDir($fileDir, \FileSystem::SCANDIR_REALPATH);
foreach ($dirList as $dir) {
    $name = basename($dir);
    $dirNode = $dataDoc->createElement('dir');
    $dirNode->setAttribute('name', $name);
    $retFragment->appendChild($dirNode);
    
    $fileList = \FileSystem::scanDir($dir, \FileSystem::SCANDIR_REALPATH);
    foreach ($fileList as $file) {
        $name = basename($file);
        $fileNode = $dataDoc->createElement('file');
        $fileNode->setAttribute('name', $name);
        $dirNode->appendChild($fileNode);
        
        if (in_array($name, $requestList)) {
            $outputDir = basename($dir);
            $outputList[] = $file;
        }
    }
}

if ($outputList) {
    $masterFile = null;
    $fileList = [];
    foreach ($outputList as $output) {
        $ext = pathinfo($output, PATHINFO_EXTENSION);
        switch ($ext) {
            case 'csv':
                $masterFile = new CSVFile($output);
                break;
            case 'mfd':
                $fileList[] = new MFDFile($output);
                break;
            default:
                throw new \Exception("unknown extension? $ext");
        }
    }
    
    $table = null;
    if ($masterFile) {
        foreach ($fileList as $file) {
            $masterFile->addCompareFile($file);
        }
        $table = $masterFile->asTable();
    } else {
        $table = [];
        foreach ($fileList as $file) {
            $table = array_merge($table, $file->asTable());
        }
    }
    
    if ($table) {
        $doc = new \DOMDocument();
        $tableNode = $doc->createElement('table');
        $tableNode->setAttribute('name', $outputDir);
        
        $headNode = $doc->createElement('head');
        foreach (array_keys($table[0]) as $val) {
            $node = $doc->createElement('cell');
            $node->setAttribute('val', $val);
            $headNode->appendChild($node);
        }
        $tableNode->appendChild($headNode);
        
        foreach ($table as $row) {
            $rowNode = $doc->createElement('row');
            $rowStatus = [];
            foreach ($row as $key => $val) {
                if (is_bool($val)) {
                    $rowStatus[] = (int) $val;
                    $val = $val ? '☑' : '☐';
                }
                $node = $doc->createElement('cell');
                $node->setAttribute('key', $key);
                $node->setAttribute('val', $val);
                $rowNode->appendChild($node);
            }
            $rowNode->setAttribute('status', implode(' ', $rowStatus));
            $tableNode->appendChild($rowNode);
        }
        $doc->appendChild($tableNode);
        
        return \DOMDocumentSmart::parseTemplate($doc, __DIR__ . DIRECTORY_SEPARATOR . 'table.xsl');
    }
}

return $retFragment;