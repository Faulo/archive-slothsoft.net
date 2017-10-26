<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\Storage;

echo 'Storage->cron: ';
$storage = new Storage();
$res = $storage->cron();
echo $res ? 'success!' : 'failure!';