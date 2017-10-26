<?php

$data = json_decode(file_get_contents(__DIR__ .  '/../res/go-data.json'), true);

$id = 78;
$statList = [];
$statList[] = [
	'cp' => 945,
	'hp' => 75,
	'stardust' => 1900,
];

$id = 81;
$statList = [];
$statList[] = [
	'cp' => 425,
	'hp' => 34,
	'stardust' => 2200,
	'lvl' => 17.0,
];

//*
$id = 18;
$statList = [];
$statList[] = [
	'cp' => 1051,
	'hp' => 102,
	'stardust' => 2200,
	'lvl' => 18.5,
];
$statList[] = [
	'cp' => 1080,
	'hp' => 104,
	'stardust' => 2500,
	'lvl' => 19.0,
];
$statList[] = [
	'cp' => 1108,
	'hp' => 105,
	'stardust' => 2500,
	'lvl' => 19.5,
];
$statList[] = [
	'cp' => 1137,
	'hp' => 106,
	'stardust' => 2500,
	'lvl' => 20.0,
];
$statList[] = [
	'cp' => 1165,
	'hp' => 108,
	'stardust' => 2500,
	'lvl' => 20.5,
];
$statList[] = [
	'cp' => 1193,
	'hp' => 109,
	'stardust' => 3000,
	'lvl' => 21.0,
];
$statList[] = [
	'cp' => 1222,
	'hp' => 110,
	'stardust' => 3000,
	'lvl' => 21.5,
];
$statList[] = [
	'cp' => 1250,
	'hp' => 112,
	'stardust' => 3000,
	'lvl' => 22.0,
];
$statList[] = [
	'cp' => 1279,
	'hp' => 113,
	'stardust' => 3000,
	'lvl' => 22.5,
];
//*/

/*
$id = 97;
$statList = [];
$statList[] = [
	'cp' => 1105,
	'hp' => 102,
	'stardust' => 2200,
	'lvl' => 18.5,
];
$statList[] = [
	'cp' => 1135,
	'hp' => 103,
	'stardust' => 2500,
	'lvl' => 19.0,
];
$statList[] = [
	'cp' => 1165,
	'hp' => 105,
	'stardust' => 2500,
	'lvl' => 19.5,
];
//*/
/*
$id = 37;
$statList = [];
$statList[] = [
	'cp' => 318,
	'hp' => 43,
	'stardust' => 1600,
	'lvl' => 14.0,
];
$statList[] = [
	'cp' => 329,
	'hp' => 44,
	'stardust' => 1600,
	'lvl' => 14.5,
];
//*/

/*
$id = 147;
$statList = [];
$statList[] = [
	'cp' => 543,
	'hp' => 57,
	'stardust' => 2500,
	'lvl' => 20.0,
];
//*/
/*
$statList[] = [
	'cp' => 533,
	'hp' => 54,
	'stardust' => 2500,
	'lvl' => 20.0,
];
//*/

$data['CpM'] = [
	0.094,
	0.135137432,
	0.16639787,
	0.192650915,
	0.21573247,
	0.236572655,
	0.25572005,
	0.273530379,
	0.29024988,
	0.30605738,
	0.3210876,
	0.335445035,
	0.34921268,
	0.362457752,
	0.37523559,
	0.387592414,
	0.39956728,
	0.411193549,
	0.42250001,
	0.432926414,
	0.44310755,
	0.453059958,
	0.46279839,
	0.472336078,
	0.48168495,
	0.490855809,
	0.49985844,
	0.508701759,
	0.51739395,
	0.525942511,
	0.53435433,
	0.542635761,
	0.55079269,
	0.558830597,
	0.56675452,
	0.574569149,
	0.58227891,
	0.589887913,
	0.59740001,
	0.60482366,
	0.61215729,
	0.619404115,
	0.62656713,
	0.633649182,
	0.64065295,
	0.647580959,
	0.65443563,
	0.661219261,
	0.667934,
	0.674581899,
	0.68116492,
	0.687684904,
	0.69414365,
	0.700542894,
	0.70688421,
	0.713169102,
	0.71939909,
	0.725575613,
	0.7317,
	0.734741007,
	0.73776948,
	0.74078557,
	0.74378943,
	0.746781204,
	0.74976104,
	0.752729104,
	0.75568551,
	0.758630369,
	0.76156384,
	0.764486069,
	0.76739717,
	0.770297274,
	0.7731865,
	0.776064943,
	0.77893275,
	0.781790063,
	0.78463697,
	0.787473581,
	0.79030001,
	0.793116367,
];

$data['stardust'] = [
	200,
	200,
	200,
	200,
	400,
	400,
	400,
	400,
	600,
	600,
	600,
	600,
	800,
	800,
	800,
	800,
	1000,
	1000,
	1000,
	1000,
	1300,
	1300,
	1300,
	1300,
	1600,
	1600,
	1600,
	1600,
	1900,
	1900,
	1900,
	1900,
	2200,
	2200,
	2200,
	2200,
	2500,
	2500,
	2500,
	2500,
	3000,
	3000,
	3000,
	3000,
	3500,
	3500,
	3500,
	3500,
	4000,
	4000,
	4000,
	4000,
	4500,
	4500,
	4500,
	4500,
	5000,
	5000,
	5000,
	5000,
	6000,
	6000,
	6000,
	6000,
	7000,
	7000,
	7000,
	7000,
	8000,
	8000,
	8000,
	8000,
	9000,
	9000,
	9000,
	9000,
	10000,
	10000,
	10000,
	10000,
];

//$cp = 1036;
//$hp = 78;


//my_dump(['cp' => $cp, 'hp' => $hp, 'stardust' => $stardust, 'data' => $data['pokemon'][$id]]);

function getIVList($id, array $stat, array $data) {
	$resultList = [];
	
	//unset($stat['lvl']);
	
	$mon = $data['pokemon'][$id];
	
	$name = $mon['Name'];
	$hpBase = $mon['BaseStamina'];
	$attBase = $mon['BaseAttack'];
	$defBase = $mon['BaseDefense'];
	
	
	$lvl = 1.0;
	foreach ($data['stardust'] as $i => $val) {
		if ($val === $stat['stardust']) {
			if (isset($stat['lvl'])) {
				if ($stat['lvl'] !== $lvl) {
					$lvl += 0.5;
					continue;
				}
			}
			$lvlMult = $data['CpM'][$i];
			
			$hpIV = 0;
			$attIV = 0;
			$defIV = 0;
			$hp = ($hpBase + $hpIV) * $lvlMult;
			$hp = (int) floor(max($hp, 10));
			$att = ($attBase + $attIV) * $lvlMult;
			//$att = ceil($att);
			$def = ($defBase + $defIV) * $lvlMult;
			//$def = ceil($def);
			$cp = (sqrt($hp) * sqrt($def) * $att) / 10;
			$cp = (int) floor(max($cp, 10));
			//my_dump($cp);
			$cp = max(
				floor((($attBase+$attIV) * sqrt($defBase+$defIV) * sqrt($hpBase+$hpIV) * $lvlMult * $lvlMult) / 10),
				10
			);
			//my_dump($cp);
  
			
			//$lvlMult = 0.01783805*$lvl + 0.17850625;
			for ($hpIV = 0; $hpIV < 16; $hpIV++) {
				$val = ($hpBase + $hpIV) * $lvlMult;
				$val = (int) floor(max($val, 10));
				if ($val === $stat['hp']) {
					for ($attIV = 0; $attIV < 16; $attIV++) {
						for ($defIV = 0; $defIV < 16; $defIV++) {
							$val = (($attBase+$attIV) * sqrt($defBase+$defIV) * sqrt($hpBase+$hpIV) * $lvlMult * $lvlMult) / 10;
							$val = (int) floor(max($val, 10));
							/*
							$att = ($attBase + $attIV) * $lvlMult;
							$def = ($defBase + $defIV) * $lvlMult;

							$val = (sqrt($stat['hp']) * sqrt($def) * $att) / 10;
							$val = (int) floor(max($val, 10));
							//*/
							if ($val === $stat['cp']) {
								$resultList[] = ['hp' => $hpIV, 'att' => $attIV, 'def' => $defIV, 'lvl' => $lvl];
							}
						}
					}
				}
			}
		}
		$lvl += 0.5;
	}
	return $resultList;
}
function filterIVMatrix(array $ivMatrix) {
	$ret = [];
	foreach ($ivMatrix as $ivList) {
		foreach ($ivList as $iv) {
			$break = false;
			foreach ($ivMatrix as $testList) {
				$found = false;
				foreach ($testList as $test) {
					if ($test['hp'] === $iv['hp'] and $test['att'] === $iv['att'] and $test['def'] === $iv['def']) {
						$found = true;
						break;
					}
				}
				if (!$found) {
					$break = true;
					break;
				}
			}
			if (!$break) {
				$key = sprintf('%s-%s-%s', $iv['hp'], $iv['att'], $iv['def']);
				$ret[$key] = $iv;
			}
		}
	}
	return array_values($ret);
}

$ivMatrix = [];
foreach ($statList as $stat) {
	$ivMatrix[] = getIVList($id, $stat, $data);
}

$resList = filterIVMatrix($ivMatrix);
//my_dump($resList);

echo $data['pokemon'][$id]['Name'] . PHP_EOL;
foreach ($resList as $i => $res) {
	printf('	#%d: 	hp: %2d	att: %2d	def: %2d%s', $i+1, $res['hp'], $res['att'], $res['def'], PHP_EOL);
}
echo PHP_EOL . PHP_EOL;

foreach ($ivMatrix as $resList) {
	foreach ($resList as $res) {
		printf('lvl: %.1f	hp: %2d	att: %2d	def: %2d%s', $res['lvl'], $res['hp'], $res['att'], $res['def'], PHP_EOL);
	}
	echo PHP_EOL;
}