<?php
namespace Minecraft;

sleep(30);

set_time_limit(0);


const LOG_FILE 	= 'C:/Minecraft/server/logs/latest.log';
const LOG_DB 	= 'cms';
const LOG_TABLE = 'minecraft_log';
const LOG_INTERVAL	= 1;

$log = new Log();
$log->init(LOG_DB, LOG_TABLE);
$log->watch(LOG_FILE, LOG_INTERVAL);