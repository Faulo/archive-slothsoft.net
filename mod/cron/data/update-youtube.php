<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\FS\DownloadManager;

set_time_limit(TIME_DAY);

$useStream = true;

$doc = $this->getResourceDoc('cron/update-youtube', 'xml');
$xpath = self::loadXPath($doc);

$manager = new DownloadManager($xpath);
$manager->setOptions($this->httpRequest->input);

if ($useStream) {
    $ret = $manager->getStream();
} else {
    $manager->run();
    $ret = $manager->getLog();
    $ret = HTTPFile::createFromString($ret);
}
return $ret;