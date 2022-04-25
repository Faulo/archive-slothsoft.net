<?php
use Slothsoft\Core\FileSystem;
use Slothsoft\CMS\HTTPFile;
use Slothsoft\Core\Storage;

const SCORESABER_DOMAIN = 'https://scoresaber.com';
const SCORESABER_USERS_PER_PAGE = 12;

$users = [];
$users['/u/76561197989374307'] = 'Faulo';

$playlist = [];
$playlist['/leaderboard/64518'] = [
	'rank' => 1,
	'name' => ' - Fear Not This Night ft. Asja',
	'difficulty' => 'Normal',
	'href' => 'https://scoresaber.com/leaderboard/64518',
	'image' => 'https://scoresaber.com/imports/images/songs/DC3C7D02008E0FB9E11728B5B47B263B.png',
];

foreach ($users as $userUrl => $user) {
	$songUrls = [];
	for ($page = 1; $page < 10; $page++) {
		$pageIsEmpty = true;
		if ($xpath = Storage::loadExternalXPath(SCORESABER_DOMAIN."$userUrl?page=$page", TIME_HOUR)) {
			foreach ($xpath->evaluate('//*[@class="song"]//*[@href]') as $songNode) {
				$pageIsEmpty = false;
				$rank = $xpath->evaluate('normalize-space(preceding::*[@class="rank"][1])', $songNode);
				$rank = preg_replace('~\D+~', '', $rank);
				$rank = (int) $rank;
				$name = $xpath->evaluate('normalize-space(*[1]/text())', $songNode);
				$difficulty = $xpath->evaluate('normalize-space(*[1]/*)', $songNode);
				$href = $xpath->evaluate('normalize-space(@href)', $songNode);
				$image = $xpath->evaluate('normalize-space(preceding::*[@src][1]/@src)', $songNode);
				
				$playlist[$href] = [
					'rank' => $rank,
					'name' => $name,
					'difficulty' => $difficulty,
					'href' => SCORESABER_DOMAIN.$href,
					'image' => SCORESABER_DOMAIN.$image,
				];
			}
		}
		if ($pageIsEmpty) {
			break;
		}
	}
}

usort($playlist, function($a, $b) { return $a['rank'] - $b['rank']; });

$dataFragment = $dataDoc->createDocumentFragment();
foreach ($playlist as $song) {
	$node = $dataDoc->createElement('song');
	foreach ($song as $key => $val) {
		$node->setAttribute($key, $val);
	}
	$dataFragment->appendChild($node);
}
return $dataFragment;