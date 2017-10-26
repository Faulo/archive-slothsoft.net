<?php

$config = [];
$config['input-resource'] = 'cron/update-comics';
$config['output-stream'] = true;
$config['threads-active'] = true;
$config['threads-count'] = 8;

//my_dump(\CMS\HTTPFile::createFromURL('http://www.nuklearpower.com/comics/8-bit-theater/010302.jpg'));
//die();


set_time_limit(TIME_DAY);

$doc = $this->getResourceDoc($config['input-resource'], 'xml');
$xpath = self::loadXPath($doc);

$manager = new \FS\DownloadManager($xpath);
$manager->setConfig($config);
$manager->setOptions($this->httpRequest->input);

if ($config['output-stream']) {
	$ret = $manager->getStream();
} else {
	$manager->run();
	$ret = $manager->getLog();
	$ret = \CMS\HTTPFile::createFromString($ret);
}

return $ret;