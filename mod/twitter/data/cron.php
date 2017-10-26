<?php

namespace Slothsoft\CMS;
return new HTTPClosure(
	[
		'isThreaded' => true,
	],
	function() use ($dataDoc) {
		@file_get_contents('http://moodle.log-in-projekt.eu/admin/cron.php');

		$userList = \Twitter\Archive::getUserList();

		$argsList = [];
		foreach ($userList as $user) {
			$args = [];
			$args['userName'] = $user;
			
			$argsList[$user] = $args;
		}

		$code = <<<'EOT'
extract($args);
try {
	$archive = new \Twitter\Archive($userName);
	$res = $archive->fetch();
	$ret = sprintf('Fetched %d tweets for user %s!', $res, $userName);
} catch(\Exception $e) {
	$ret = $e->getMessage();
}
return $ret . PHP_EOL . PHP_EOL;
EOT;

		return \Lambda\Manager::streamClosureList($code, $argsList);
	}
);