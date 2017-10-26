<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\DOMHelper;
$firstYear = 2010;
$firstMonth = 12;

$lastYear = (int) date('Y', $this->httpRequest->time);
$lastMonth = (int) date('n', $this->httpRequest->time);

// my_dump([$firstYear, $lastYear, $firstMonth, $lastMonth]);die();

$xml = '';
for ($year = $lastYear; $year >= $firstYear; $year --) {
    // <page status-active="" status-public="" redirect="/Minecraft/Log/" name="2014" title="2014">
    $xml .= sprintf('<page status-active="" status-public="" redirect="/Minecraft/Log/" name="%s" title="%s">', $year, $year);
    $startMonth = $year === $lastYear ? $lastMonth : 12;
    $endMonth = $year === $firstYear ? $firstMonth : 1;
    for ($month = $startMonth; $month >= $endMonth; $month --) {
        // <page status-active="" status-public="" ref="archive" name="03" title="month/03">
        // <param name="chat-start" value="1393628400"/><param name="chat-end" value="1396303200"/>
        $xml .= sprintf('<page status-active="" status-public="" ref="archive" name="%02d" title="month/%02d">', $month, $month);
        $xml .= sprintf('<param name="chat-start" value="%d"/>', mktime(0, 0, 0, $month, 1, $year));
        $xml .= sprintf('<param name="chat-end" value="%d"/>', mktime(0, 0, 0, $month + 1, 1, $year));
        $xml .= '</page>';
    }
    $xml .= '</page>';
}

$dom = new DOMHelper();
return $dom->parse($xml, $dataDoc);