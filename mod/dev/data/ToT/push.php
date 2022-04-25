<?php
namespace Slothsoft\CMS;

$buffer = __FILE__ . '.txt';

$input = @json_decode(file_get_contents('php://input'), true);

if ($input) {
	file_put_contents($buffer, json_encode($input));
} else {
	$input = @json_decode(file_get_contents($buffer), true);
}

if (isset($input['ref']) and preg_match('~^refs/heads/(.+)$~', $input['ref'], $match)) {
	$branch = $match[1];
	//echo "Noticed push to branch $branch, starting pull+compile..." . PHP_EOL;
	
	$_REQUEST['branch'] = $branch;
	return include(__DIR__ . DIRECTORY_SEPARATOR . 'pull.php');
	return include(__DIR__ . DIRECTORY_SEPARATOR . 'compile.php');
}

return HTTPFile::createFromString('ok');