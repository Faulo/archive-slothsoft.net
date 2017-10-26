<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\FS\DownloadManager;
use Slothsoft\Core\CloudFlareScraper;

return new HTTPClosure(
	[
		'isThreaded' => true,
	],
	function() {
		$config = [];
		$config['input-resource'] = 'cron/update-hentai';
		$config['output-stream'] = true;
		$config['threads-active'] = false;
		$config['threads-count'] = 16;


		set_time_limit(TIME_DAY);

		$doc = $this->getResourceDoc($config['input-resource'], 'xml');
		$xpath = self::loadXPath($doc);

		$manager = new DownloadManager($xpath);
		$manager->setConfig($config);
		$manager->setOptions($this->httpRequest->input + ['downloader' => new CloudFlareScraper()]);

		if ($config['output-stream']) {
			$ret = $manager->getStream();
		} else {
			$manager->run();
			$ret = $manager->getLog();
			$ret = HTTPFile::createFromString($ret);
		}

		return $ret;
	}
);