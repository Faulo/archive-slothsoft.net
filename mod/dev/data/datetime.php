<?php

$commands = [];
$commands[] = sprintf('date %s', date('d-m-y'));
$commands[] = sprintf('time %s', date('H:i'));

return Slothsoft\CMS\HTTPFile::createFromString(json_encode($commands));