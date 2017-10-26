<?php
namespace Slothsoft\CMS;
return new HTTPClosure(
	[
		'isThreaded' => true,
	],
	function() {
		$urlList = [];
		$urlList[] = 'http://lodb.log-in-projekt.eu/scripts/shopExport/cron.php';
		$urlList[] = 'http://red.steinlese.de/sites/www/scripts/shopExport/cron.php';
		$urlList[] = 'http://red.steinlese.ibp-dresden.de/sites/www/scripts/shopExport/cron.php';
		//$urlList[] = 'http://www.steinlese2.ibp-dresden.de/scripts/shopExport/cron.php';
		$urlList[] = 'http://www.lebensart-wzf.de/scripts/shopExport/cron.php';
		//$urlList[] = 'http://www.lebensart-wzf.ibp-dresden.de/scripts/shopExport/cron.php';
		$urlList[] = 'http://www.glashuetter-uhren-shop.de/scripts/shopExport/cron.php';
		$urlList[] = 'http://www.versace-uhren-shop.com/scripts/shopExport/cron.php';
		$urlList[] = 'http://shop.glt.ibp-dresden.de/scripts/shopExport/cron.php';
		//$urlList[] = 'http://shop.em.ibp-dresden.de/scripts/shopExport/cron.php';
		//$urlList[] = 'http://www.gold-ankauf-dresden.de/scripts/shopExport/cron.php';
		$urlList[] = 'http://www.gold-kaufen-dresden.com/scripts/shopExport/cron.php';

		$code = '
$url = $args[0];
$ret = \\Storage::loadExternalFile($url);
$this->log($url . PHP_EOL . $ret . PHP_EOL);
		';

		return \Lambda\Manager::streamClosureList($code, $urlList);
		
		/*
		$resList = \Lambda\Manager::executeList($code, $urlList);
		return \CMS\HTTPFile::createFromString(implode(PHP_EOL, $resList));
		//*/

		/*
		foreach ($urlList as $url) {
			echo file_get_contents($url);
			echo PHP_EOL;
		}
		//*/
	}
);