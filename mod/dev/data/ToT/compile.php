<?php
namespace Slothsoft\CMS;

$workspaceDir = realpath('C:/Unity/workspace') . DIRECTORY_SEPARATOR;
$buildDir = realpath('C:/Webserver/htdocs/vhosts/daniel-schulz.slothsoft.net/public/Games/TrialOfTwo') . DIRECTORY_SEPARATOR;
$projectFile = 'ProjectSettings/ProjectVersion.txt';

$url = 'https://github.com/Faulo/Oilcatz';
$start = 'TrialOfTwo.master';
$unityFile = 'C:/Unity/%s/Editor/Unity.exe';

$branches = [];
if (isset($_REQUEST['branch'])) {
	$branches[] = 'origin/' . $_REQUEST['branch'];
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
	$projectDir = 'TrialOfTwo/' . $localBranch;
	$projectDir = str_replace('/', '.', $projectDir);
	$projectDir = $workspaceDir . $projectDir;
	$logFile = $buildDir . str_replace('/', '.', $localBranch) . '.log';
	if (is_dir($projectDir)) {
		chdir($projectDir);
		
		$unityVersion = file_get_contents($projectFile);
		if (preg_match('~m_EditorVersion: (.+)~', $unityVersion, $match)) {
			$unityVersion = trim($match[1]);
			
			$unity = sprintf($unityFile, $unityVersion);
			if (!realpath($unity)) {
				die("Unity version $unityVersion must be installed at $unity");
			}
			$unity = realpath($unity);
		
			$cmd = sprintf(
				'%s -quit -accept-apiupdate -batchmode -nographics -logFile %s -projectPath %s -buildTarget WebGL %s',
				$unity,
				escapeshellarg($logFile),
				escapeshellarg($projectDir),
				escapeshellarg($buildDir . $localBranch)
			);
			die($cmd . PHP_EOL);
			return new HTTPCommand($cmd);
		} else {
			die("Could not determine unity version from $projectFile");
		}
	} else {
		$_REQUEST['branch'] = $localBranch;
		return include(__DIR__ . DIRECTORY_SEPARATOR . 'pull.php');
	}
}