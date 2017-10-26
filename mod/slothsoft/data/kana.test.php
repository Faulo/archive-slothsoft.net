<?php
$kanaList = isset($_REQUEST['kana'])
	? $_REQUEST['kana']
	: [
		//*
		"a","ba","bya","cha","da","fa","ga","gya","ha","hya","ja","ka","kya","ma","mya","na","nya","pa","pya","ra","rya","sa","sha","ta","tsa","va","wa","ya","za",
		"be","che","de","e","fe","ge","he","hye","je","ke","me","ne","pe","re","se","she","te","tse","ve","we","ye","ze",
		"bi","chi","di","fi","gi","hi","i","ji","ki","mi","ni","pi","ri","shi","si","ti","tsi","vi","wi","zi",
		"bo","byo","cho","do","fo","go","gyo","ho","hyo","jo","ko","kyo","mo","myo","no","nyo","o","po","pyo","ro","ryo","sho","so","to","tso","vo","wo","yo","zo",
		"bu","byu","chu","du","fu","fyu","gu","gyu","hyu","ju","ku","kyu","mu","myu","nu","nyu","pu","pyu","ru","ryu","shu","su","tsu","tu","u","vu","yu","zu",
		"n",
		//*/
		/*
		'a', 'e', 'i', 'u', 'o', 'ka', 'ke', 'ki', 'ku', 'ko', 'sa', 'se', 'shi', 'su', 'so', 'ta', 'chi', 'tsu', 'te', 'to',
		'na', 'ne', 'ni', 'nu', 'no',
		'ha', 'hi', 'fu', 'he', 'ho',
		'ma', 'me', 'mi', 'mu', 'mo',
		'ya', 'yu', 'yo',
		'ra', 're', 'ri', 'ru', 'ro',
		'wa', 'wi', 'we', 'wo', 'n',
		'che', 'cha', 'cho', 'chu', 'fa', 'fe', 'fi', 'fo', 'fyu', 'hya', 'hyo', 'hyu', 'hye', 'kya', 'kyo', 'kyu', 'mya', 'myo', 'myu', 'nya', 'nyo', 'nyu',
		'rya', 'ryo', 'ryu', 'si', 'she', 'sha', 'sho', 'shu', 'tu', 'ti', 'tsa', 'tse', 'tsi', 'tso', 'ye',
		'ba', 'be', 'bi', 'bo', 'bu', 'bya', 'byo', 'byu',
		'da', 'de', 'di', 'do', 'du',
		'ga', 'ge', 'gi', 'go', 'gu', 'gya', 'gyo', 'gyu',
		//*/
		//'je', 'ji', 'ja', 'jo', 'ju', 'pa', 'pe', 'pi', 'po', 'pu', 'pya', 'pyo', 'pyu', 'va', 've', 'vi', 'vo', 'vu', 'za', 'ze', 'zi', 'zo', 'zu',
		
];

$kanaList = array_unique($kanaList);
//my_dump($kanaList);

$kanaQuery = $this->httpRequest->getInputValue('kanaQuery', 'hiragana | katakana');
$randLimit = $this->httpRequest->getInputValue('kanaCount', 10);
$userKanaKey = 'kana-list';
$userKanaList = $this->session->getDataValue($userKanaKey, []);

$doc = $this->getResourceDoc('slothsoft/kana', 'xml');
$xpath = new \DOMXPath($doc);
$query = '//kana[@skip]';
$nodeList = $xpath->evaluate($query);
foreach ($nodeList as $node) {
	$node->parentNode->removeChild($node);
}
$query = '//kana[@latin = "%s"]';
foreach ($kanaList as $c) {
	$nodeList = $xpath->evaluate(
		sprintf($query, $c)
	);
	foreach ($nodeList as $node) {
		$node->setAttribute('checked', '');
	}
}
/*
$kanaList = [
	"a","ba","bya","cha","da","fa","ga","gya","ha","hya","ja","ka","kya","ma","mya","na","nya","pa","pya","ra","rya","sa","sha","ta","tsa","va","wa","ya","za",
	"be","che","de","e","fe","ge","he","hye","je","ke","me","ne","pe","re","se","she","te","tse","ve","we","ye","ze",
	"bi","chi","di","fi","gi","hi","i","ji","ki","mi","ni","pi","ri","shi","si","ti","tsi","vi","wi","zi",
	"bo","byo","cho","do","fo","go","gyo","ho","hyo","jo","ko","kyo","mo","myo","no","nyo","o","po","pyo","ro","ryo","sho","so","to","tso","vo","wo","yo","zo",
	"bu","byu","chu","du","fu","fyu","gu","gyu","hyu","ju","ku","kyu","mu","myu","nu","nyu","pu","pyu","ru","ryu","shu","su","tsu","tu","u","vu","yu","zu",
	"n"
];
$hiraganaList = [];
$query = '//kana[@latin = "%s"]';
foreach ($kanaList as $c) {
	$hiList = $xpath->evaluate(
		sprintf($query . '/hiragana', $c)
	);
	$kaList = $xpath->evaluate(
		sprintf($query . '/katakana', $c)
	);
	foreach ($kaList as $kaNode) {
		foreach ($hiList as $hiNode) {
			if (!isset($hiraganaList[$hiNode->getAttribute('name')])) {
				//$hiraganaList[$hiNode->getAttribute('name')] = $kaNode->getAttribute('name');
			}
			if (!isset($hiraganaList[$kaNode->getAttribute('name')])) {
				$hiraganaList[$kaNode->getAttribute('name')] = $hiNode->getAttribute('name');
			}
		}
	}
}
$str = '[';
foreach ($hiraganaList as $name => $c) {
	$str .= "'$name' => '$c', ";
}
$str .= ']';
die($str);
//*/
//$query = '//kana[@latin = "%s"]';


$kanaMap = [];
$kanaCompleteMap = [];
$query = '//kana[@latin]';
$nodeList = $xpath->evaluate($query);
foreach ($nodeList as $node) {
	$childList = $xpath->evaluate($kanaQuery, $node);
	foreach ($childList as $childNode) {
		$kanaMap[$childNode->getAttribute('name')] = $node->getAttribute('latin');
	}
	
	$childList = $xpath->evaluate('*', $node);
	foreach ($childList as $childNode) {
		$kanaCompleteMap[$childNode->getAttribute('name')] = $node->getAttribute('latin');
	}
}

if (isset($_REQUEST['testKana'], $_REQUEST['testLatin'])) {
	$testKana = $_REQUEST['testKana'];
	$testLatin = $_REQUEST['testLatin'];
	$testKana = explode(' ', $testKana);
	$testLatin = explode(' ', $testLatin);
	
	$resNode = $doc->createElement('testResult');
	$doc->documentElement->appendChild($resNode);
	
	for ($i = 0; $i < count($testKana); $i++) {
		$kana = $testKana[$i];
		$latin = (isset($testLatin[$i]) and strlen($testLatin[$i]))
			? $testLatin[$i]
			: '-';
		if (isset($kanaCompleteMap[$kana])) {
			$correct = (int) ($latin === $kanaCompleteMap[$kana]);
			if (!isset($userKanaList[$kana])) {
				$userKanaList[$kana] = 0;
			}
			if ($correct) {
				$userKanaList[$kana]++;
			} else {
				$userKanaList[$kana]--;
			}
			
			$node = $doc->createElement('kana');
			$node->setAttribute('name', $kana);
			$node->setAttribute('input', $latin);
			$node->setAttribute('latin', $kanaCompleteMap[$kana]);
			$node->setAttribute('correct', $correct);
			
			$resNode->appendChild($node);
		}
	}
}

foreach ($userKanaList as $kana => $num) {
	$expr = sprintf('//*[@name="%s"]', $kana);
	$nodeList = $xpath->evaluate($expr);
	foreach ($nodeList as $node) {
		$node->setAttribute('user-count', $num);
	}
}

$userKanaAvg = count($userKanaList)
	? array_sum($userKanaList) / count($userKanaList)
	: 0;


if ($kanaList) {
	$kanaArr = [];
	foreach ($kanaList as $latin) {
		foreach ($kanaMap as $kana => $tmp) {
			if ($tmp === $latin) {
				$kanaArr[] = $kana;
			}
		}
	}
	if ($kanaArr) {
		$max = count($kanaArr) - 1;
		$userKanaSum = 0;
		$userKanaCount = 0;
		foreach ($kanaArr as $kana) {
			$userKanaCount++;
			if (isset($userKanaList[$kana])) {
				$userKanaSum += $userKanaList[$kana];
			}
		}
		$userKanaAvg = $userKanaSum / $userKanaCount;
		
		$randList = [];
		$lastKana = null;
		//my_dump($lastKana);
		while (count($randList) < $randLimit) {
			$kana = $kanaArr[rand(0, $max)];
			$userKanaChance = isset($userKanaList[$kana])
				? $userKanaList[$kana]
				: 0;
			$userKanaChance -= $userKanaAvg;
			$userKanaChance = tanh($userKanaChance);
			$userKanaChance = 50 - 50*$userKanaChance;
			//echo ($kana .':'.$userKanaChance.PHP_EOL);
			if ($kana !== $lastKana and rand(0, 100) < $userKanaChance) {
				$randList[] = $kana;
				$lastKana = $kana;
			}
		}
		
		$kanaNode = $doc->createElement('testKana');
		$kanaNode->setAttribute('kanaQuery', $kanaQuery);
		$kanaNode->setAttribute('kanaCount', $randLimit);
		$kanaNode->setAttribute('text', implode(' ', $randList));
		$doc->documentElement->appendChild($kanaNode);
	}
}

$this->session->setDataValue($userKanaKey, $userKanaList);

return $doc;
?>