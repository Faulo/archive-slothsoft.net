<?php

echo 'Storage->cron: ';
$storage = new Storage();
$res = $storage->cron();
echo $res ? 'success!' : 'failure!';