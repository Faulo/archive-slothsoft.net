<?php
set_time_limit(TIME_DAY);

$useStream = true;

$doc = $this->getResourceDoc('cron/update-youtube', 'xml');
$xpath = self::loadXPath($doc);

$manager = new \FS\DownloadManager($xpath);
$manager->setOptions($this->httpRequest->input);

if ($useStream) {
	$ret = $manager->getStream();
} else {
	$manager->run();
	$ret = $manager->getLog();
	$ret = \CMS\HTTPFile::createFromString($ret);
}
return $ret;