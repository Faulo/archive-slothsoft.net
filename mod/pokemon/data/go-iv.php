<?php
$trainer = $this->httpRequest->getInputValue('trainer');

$data = json_decode(file_get_contents(__DIR__ . '/../res/go-data.json'), true);
$data['CpM'] = json_decode(file_get_contents(__DIR__ . '/../res/go-CpM.json'), true);
$data['stardust'] = json_decode(file_get_contents(__DIR__ . '/../res/go-stardust.json'), true);

$statSchema = [
    'name' => 'stat',
    'attributes' => [
        'cp' => 'integer',
        'hp' => 'integer',
        'stardust' => 'integer',
        'lvl' => 'float',
        'new' => 'boolean'
    ]
];
$ivSchema = [
    'name' => 'iv',
    'attributes' => [
        'hp' => 'integer',
        'att' => 'integer',
        'def' => 'integer',
        'lvl' => 'float'
    ]
];
$monSchema = [
    'name' => 'mon',
    'attributes' => [
        'name' => 'string',
        'species' => 'string',
        'id' => 'integer'
    ],
    'elements' => [
        $statSchema,
        $ivSchema
    ]
];
$trainerSchema = [
    'name' => 'trainer',
    'elements' => [
        $monSchema
    ]
];

function getIVList($id, array $stat, array $data)
{
    $resultList = [];
    
    // unset($stat['lvl']);
    
    $mon = $data['pokemon'][$id];
    
    $name = $mon['Name'];
    $hpBase = $mon['BaseStamina'];
    $attBase = $mon['BaseAttack'];
    $defBase = $mon['BaseDefense'];
    
    if ($stat['new']) {
        $stat['lvl'] = 1.0;
        $break = 0;
        foreach ($data['stardust'] as $i => $val) {
            if ($val === $stat['stardust']) {
                $break ++;
                if ($break > 2) {
                    break;
                }
            }
            $stat['lvl'] += 0.5;
        }
    }
    
    $lvl = 1.0;
    foreach ($data['stardust'] as $i => $val) {
        if ($val === $stat['stardust']) {
            if ($stat['lvl'] > 0) {
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
            // $att = ceil($att);
            $def = ($defBase + $defIV) * $lvlMult;
            // $def = ceil($def);
            $cp = (sqrt($hp) * sqrt($def) * $att) / 10;
            $cp = (int) floor(max($cp, 10));
            // my_dump($cp);
            $cp = max(floor((($attBase + $attIV) * sqrt($defBase + $defIV) * sqrt($hpBase + $hpIV) * $lvlMult * $lvlMult) / 10), 10);
            // my_dump($cp);
            
            // $lvlMult = 0.01783805*$lvl + 0.17850625;
            for ($hpIV = 0; $hpIV < 16; $hpIV ++) {
                $val = ($hpBase + $hpIV) * $lvlMult;
                $val = (int) floor(max($val, 10));
                if ($val === $stat['hp']) {
                    for ($attIV = 0; $attIV < 16; $attIV ++) {
                        for ($defIV = 0; $defIV < 16; $defIV ++) {
                            $val = (($attBase + $attIV) * sqrt($defBase + $defIV) * sqrt($hpBase + $hpIV) * $lvlMult * $lvlMult) / 10;
                            $val = (int) floor(max($val, 10));
                            /*
                             * $att = ($attBase + $attIV) * $lvlMult;
                             * $def = ($defBase + $defIV) * $lvlMult;
                             *
                             * $val = (sqrt($stat['hp']) * sqrt($def) * $att) / 10;
                             * $val = (int) floor(max($val, 10));
                             * //
                             */
                            if ($val === $stat['cp']) {
                                $resultList[] = [
                                    'hp' => $hpIV,
                                    'att' => $attIV,
                                    'def' => $defIV,
                                    'lvl' => $lvl
                                ];
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

function filterIVMatrix(array $ivMatrix)
{
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
                if (! $found) {
                    $break = true;
                    break;
                }
            }
            if (! $break) {
                $key = sprintf('%02d-%02d-%02d-%02d', $iv['hp'] + $iv['att'] + $iv['def'], $iv['hp'], $iv['att'], $iv['def']);
                $ret[$key] = $iv;
            }
        }
    }
    krsort($ret);
    return array_values($ret);
}

function getPokemonId(array $data, $name)
{
    foreach ($data['pokemon'] as $id => $mon) {
        if ($mon and $mon['Name'] === $name) {
            return $id;
        }
    }
    return null;
}

$resourceList = $this->getResourceDir('pokemon/go-trainers', 'xml');
$retNode = null;
if (isset($resourceList[$trainer])) {
    $trainerElement = \Schema\DataElement::loadDocument($resourceList[$trainer], $trainerSchema);
    
    foreach ($trainerElement->getElementList() as $monElement) {
        $mon = $monElement->getAttributes();
        if ($monId = getPokemonId($data, $mon['species'])) {
            $ivMatrix = [];
            foreach ($monElement->getElementList() as $statElement) {
                $stat = $statElement->getAttributes();
                if ($stat['cp']) {
                    $ivMatrix[] = getIVList($monId, $stat, $data);
                }
            }
            $mon['id'] = $monId;
            $ivList = filterIVMatrix($ivMatrix);
            
            $monElement->appendAttributeList($mon);
            $monElement->appendDataList('iv', $ivList);
        }
    }
    $retNode = $trainerElement->getDataNode();
}
return $retNode;

$ivMatrix = [];
foreach ($statList as $stat) {
    $ivMatrix[] = getIVList($id, $stat, $data);
}

$resList = filterIVMatrix($ivMatrix);
// my_dump($resList);

echo $data['pokemon'][$id]['Name'] . PHP_EOL;
foreach ($resList as $i => $res) {
    printf('	#%d: 	hp: %2d	att: %2d	def: %2d%s', $i + 1, $res['hp'], $res['att'], $res['def'], PHP_EOL);
}
echo PHP_EOL . PHP_EOL;

foreach ($ivMatrix as $resList) {
    foreach ($resList as $res) {
        printf('lvl: %.1f	hp: %2d	att: %2d	def: %2d%s', $res['lvl'], $res['hp'], $res['att'], $res['def'], PHP_EOL);
    }
    echo PHP_EOL;
}