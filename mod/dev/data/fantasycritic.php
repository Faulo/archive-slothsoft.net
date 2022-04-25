<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\Image;
use Slothsoft\Core\Storage;

$uri = 'https://www.fantasycritic.games/api/game/MasterGameYear/2022';

$bannedTags = [];
$bannedTags[] = 'CurrentlyInEarlyAccess';
$bannedTags[] = 'PartialRemake';
$bannedTags[] = 'Port';
$bannedTags[] = 'ReleasedInternationally';
$bannedTags[] = 'Remaster';
$bannedTags[] = 'YearlyInstallment';
$bannedTags[] = 'ExpansionPack';
$bannedTags[] = 'DirectorsCut';

$games = Storage::loadExternalJSON($uri, TIME_DAY);

//var_dump($games);

$fragment = $dataDoc->createDocumentFragment();

foreach ($games as $game) {
	foreach ($game['tags'] as $tag) {
		if (in_array($tag, $bannedTags)) {
			continue 2;
		}
	}
	
	foreach ($game as $key => $val) {
		if (isset($_REQUEST[$key]) and !preg_match("~$_REQUEST[$key]~", $val)) {
			continue 2;
		}
	}
	
	$game['href-fc'] = "https://www.fantasycritic.games/mastergame/$game[masterGameID]";
	$game['href-critic'] = $game['openCriticID']
		? "https://opencritic.com/game/$game[openCriticID]/a"
		: '';
	$game['href-wiki'] = 'https://en.wikipedia.org/w/index.php?title=Special:Search&search=' . urlencode($game['gameName']);
	
	if ($xpath = Storage::loadExternalXPath($game['href-wiki'], TIME_YEAR)) {
		$game['developer'] = $xpath->evaluate(
			'normalize-space(//*[@class="infobox-data"][preceding-sibling::*//@href="/wiki/Video_game_developer"])'
		);
		$game['publisher'] = $xpath->evaluate(
			'normalize-space(//*[@class="infobox-data"][preceding-sibling::*//@href="/wiki/Video_game_publisher"])'
		);
	}
	
	$node = $dataDoc->createElement('game');
	foreach ($game as $key => $val) {
		$val = is_array($val)
			? implode(', ', $val)
			: (string)$val;
		if ($val === '') {
			continue;
		}
		
		$node->setAttribute($key, $val);
	}
	$fragment->appendChild($node);
}

return $fragment;