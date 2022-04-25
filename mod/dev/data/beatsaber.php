<?php
use Slothsoft\Core\FileSystem;
use Slothsoft\CMS\HTTPFile;
use Slothsoft\Core\Storage;

const SCORESABER_DOMAIN = 'https://scoresaber.com';
const SCORESABER_USERS_PER_PAGE = 12;

$users = [];
$users['/u/76561197989374307'] = 'Faulo';
$users['/u/76561198083736544'] = 'Digitale Medien';
$users['/u/76561198209814432'] = 'Petrichora';

function mixScores(array $scores) {
	$ret = [];
	foreach ($scores as $score) {
		$key = floatval($score['_score'] . '.' . $score['_timestamp']);
		$ret[$key] = $score;
	}
	krsort($ret);
	return array_values($ret);
}

$dir = realpath(__DIR__ . '/../res/beatsaber/') . DIRECTORY_SEPARATOR;

if (isset($_FILES['file']) and $_FILES['file']['error'] === UPLOAD_ERR_OK) {
	if (json_decode(file_get_contents($_FILES['file']['tmp_name']), true)) {
		move_uploaded_file($_FILES['file']['tmp_name'], $dir . date('Y-m-d H-i-s').'.json');
	}
}

$global = [];
$songIds = [];
$songs = [];
$songHashes = [];
$fcThresholds = [];

foreach (FileSystem::scanDir($dir, FileSystem::SCANDIR_REALPATH) as $file) {
	$data = json_decode(file_get_contents($file), true);
	if (isset($data['_leaderboardsData'])) {
		//leaderboard
		foreach ($data['_leaderboardsData'] as $localSong) {
			$id = $localSong['_leaderboardId'];
			if (!isset($global[$id])) {
				$global[$id] = [];
			}
			$global[$id] = array_merge($global[$id], $localSong['_scores']);
			foreach ($localSong['_scores'] as $score) {
				if ($score['_fullCombo']) {
					$fcThresholds[$id] = isset($fcThresholds[$id])
						? min($fcThresholds[$id], $score['_score'])
						: $score['_score'];
				}
			}
			$info = explode(json_decode('"\u220e"'), $id);
			if (count($info) > 1) {
				$song = [
					'title' => $info[1],
					'artist' => $info[2],
					'source' => $info[3],
					'difficulty' => $info[5],
				];
				
				$name = "$song[artist] - $song[title] $song[difficulty]";
				$name = trim(preg_replace('~\s+~', ' ', $name));
				$songIds[$name] = $id;
				$songs[$id] = $song;
			} else {
			}
		}
	} else {
		//song hashes
		foreach ($data as $path => $arr) {
			$songHashes[$arr['songHash']] = basename($path);
		}
	}
}

foreach ($global as $key => $sores) {
	if (isset($songs[$key])) {
		$song = $songs[$key];
		foreach ($songHashes as $id => $path) {
			$title = substr($path, - strlen($song['title']));
			if (strcasecmp($title, $song['title']) === 0) {
				$key = "custom_level_$id$song[difficulty]";
				if (!isset($global[$key])) {
					$global[$key] = [];
				}
				$global[$key] = array_merge($global[$key], $sores);
			}
		}
	}
}

foreach ($users as $userUrl => $user) {
	$songUrls = [];
	for ($page = 1; $page < 10; $page++) {
		$pageIsEmpty = true;
		if ($xpath = Storage::loadExternalXPath(SCORESABER_DOMAIN."$userUrl?page=$page", TIME_DAY)) {
			foreach ($xpath->evaluate('//*[@class="song"]//*[@href]') as $songNode) {
				$pageIsEmpty = false;
				$rank = $xpath->evaluate('normalize-space(preceding::*[@class="rank"][1])', $songNode);
				$name = $xpath->evaluate('normalize-space(*[1])', $songNode);
				$href = $xpath->evaluate('normalize-space(@href)', $songNode);
				
				if (isset($songIds[$name])) {
					$id = $songIds[$name];
					$rank = preg_replace('~\D+~', '', $rank);
					$rank = (int) $rank;
					$subPage = 1 + floor($rank / SCORESABER_USERS_PER_PAGE); //magic
					if ($subXpath = Storage::loadExternalXPath(SCORESABER_DOMAIN."$href?page=$subPage", TIME_DAY)) {
						foreach ($subXpath->evaluate('//*[@class="player"][*/@href = "'.$userUrl.'"]') as $scoreNode) {
							$score = $subXpath->evaluate('normalize-space(following::*[@class="score"])', $scoreNode);
							$score = preg_replace('~\D+~', '', $score);
							$score = (int) $score;
							$global[$id][] = [
								'_score' => $score,
								'_playerName' => $user,
								'_fullCombo' => isset($fcThresholds[$id]) ? $score > $fcThresholds[$id] : false,
								'_timestamp' => 0
							];
						}
					}
				} else {
					if (isset($_REQUEST['party'])) {
						echo $name . PHP_EOL;
					}
				}
			}
		}
		if ($pageIsEmpty) {
			break;
		}
	}
}

$ret = [];
foreach ($global as $key => $val) {
	$ret[] = [
		'_leaderboardId' => $key,
		'_scores' => mixScores($val),
	];
}


return HTTPFile::createFromJSON(['_leaderboardsData' => $ret], 'beatsaber.json');