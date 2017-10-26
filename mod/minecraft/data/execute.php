<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\RCon;
$ret = '';

$command = $this->httpRequest->getInputValue('command', null);

if ($command) {
    try {
        $rcon = new RCon(MINECRAFT_RCON_ADDRESS, MINECRAFT_RCON_PORT, MINECRAFT_RCON_PASSWORD);
        $ret = $rcon->execute($command);
    } catch (\Exception $e) {
        $ret = $e->getMessage();
    }
}

return HTTPFile::createFromString($ret);