<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\FileSystem;
use Slothsoft\Lang\Dictionary;
use DOMXPath;

$file = __DIR__ . '/../res/HistorischerSpieleabend/colors.csv';

$html = <<<EOT
<form xmlns="http://www.w3.org/1999/xhtml" method="POST">
	<input style="display: block" type="submit"/>
	<textarea style="display: block" name="colors" cols="80" rows="80">%s</textarea>
</form>
EOT;

if ($colors = $this->httpRequest->getInputValue('colors')) {
	file_put_contents($file, $colors);
} else {
	$colors = file_get_contents($file);
}

$html = sprintf($html, htmlentities($colors));

$dom = new DOMHelper();
return $dom->parse($html, $dataDoc);