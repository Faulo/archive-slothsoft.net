<?php
namespace Slothsoft\CMS;

use InterExec;

$workspaceDir = realpath('C:/Unity/workspace') . DIRECTORY_SEPARATOR;

$url = 'https://github.com/Faulo/Oilcatz';
$start = 'TrialOfTwo.master';

$branches = [];
if (isset($_REQUEST['branch'])) {
	$branches[] = $_REQUEST['branch'];
} else {
	chdir($workspaceDir . $start);

	exec('git branch -r', $output);
	array_shift($output);
	foreach ($output as $val) {
		$branches[] = trim($val);
	}
}

foreach ($branches as $branch) {
	$localBranch = substr($branch, strlen('origin/'));
	$folder = 'TrialOfTwo/' . $localBranch;
	$folder = str_replace('/', '.', $folder);
	$folder = $workspaceDir . $folder;
	if (is_dir($folder)) {
		chdir($folder);
		$cmd = sprintf('git pull origin %s', escapeshellarg($localBranch));
		//die($cmd . PHP_EOL);
		return new InterExec($cmd);
	} else {
		$cmd = sprintf('git clone %s --single-branch --branch %s %s', escapeshellarg($url), escapeshellarg($localBranch), escapeshellarg($folder));
		die($cmd . PHP_EOL);
		return new InterExec($cmd);
	}
}

die;