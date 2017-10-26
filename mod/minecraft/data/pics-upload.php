<?php 
// © 2010 Daniel Schulz
$dir = realpath(dirname(__FILE__) . '/../res/pictures/');
$key = 'pic';

$id = $this->httpRequest->getInputValue('view');

if ($dir and isset($_FILES[$key])) {
    foreach ($_FILES[$key]['error'] as $i => $error) {
        if ($error === 0) {
            // $ext = strtolower(pathinfo($_FILES[$key]['name'][$i], PATHINFO_EXTENSION));
            $finfo = new FInfo(FILEINFO_MIME);
            $type = $finfo->file($_FILES[$key]['tmp_name'][$i]);
            if (strpos($type, 'image/') === 0) {
                $id = sprintf('%d - %s', time(), $_FILES[$key]['name'][$i]);
                $path = sprintf('%s/%s', $dir, $id);
                $res = move_uploaded_file($_FILES[$key]['tmp_name'][$i], $path);
                // my_dump($path);
            }
        }
    }
}