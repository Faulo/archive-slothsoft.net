<?php
$table = \DBMS\Manager::getTable('cms', 'access_log');

$sql = '';

$sql = 'REQUEST_URI="/TalesOfEternia/MelnicsTranslator/"';

$res = $table->select('HTTP_REFERER', $sql);

$domainList = [];

foreach ($res as $ref) {
    $domain = parse_url($ref, PHP_URL_HOST);
    if (strlen($domain)) {
        if (! isset($domainList[$domain])) {
            $domainList[$domain] = 0;
        }
        $domainList[$domain] ++;
    }
}
arsort($domainList);

my_dump($domainList);

die();