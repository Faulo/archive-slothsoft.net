<?php
$uri = 'https://thesilphroad.com/js/monRater.data.js';

$success = false;
if ($data = $this->loadExternalFile($uri)) {
    preg_match_all('/(\w+) = (.+);/', $data, $matchList, PREG_SET_ORDER);
    $data = [];
    foreach ($matchList as $match) {
        $key = $match[1];
        $val = $match[2];
        $val = preg_replace('/,\s*([\]}])/m', '$1', $val);
        $val = str_replace("'", '"', $val);
        $data[$key] = json_decode($val, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            my_dump($val);
            my_dump(json_last_error());
            die();
        }
    }
    
    if (isset($data['CpM'], $data['exp_req'], $data['candy'], $data['stardust'], $data['pokemon'])) {
        $success = file_put_contents(__DIR__ . '/../res/go-data.json', json_encode($data));
    }
}

echo $success ? 'updated go-data.json!' : 'error?';