<?php
namespace Slothsoft\CMS;

function lookupCharName(array $mappingList, $oldName)
{
    $name = str_replace(' ', '_', $oldName);
    return isset($mappingList[$name]) ? $mappingList[$name] : $oldName;
}

function addSupport(array &$convoList, $char1, $char2)
{
    if (! isset($convoList[$char1])) {
        $convoList[$char1] = [];
    }
    if (! isset($convoList[$char2])) {
        $convoList[$char2] = [];
    }
    if (! in_array($char2, $convoList[$char1])) {
        $convoList[$char1][] = $char2;
    }
    if (! in_array($char1, $convoList[$char2])) {
        $convoList[$char2][] = $char1;
    }
}

$gameKey = isset($this->httpRequest->input['game']) ? $this->httpRequest->input['game'] : null;

$saveDir = realpath(sprintf('%s/../res/fire-emblem-%s/', dirname(__FILE__), $gameKey));
if (! $saveDir) {
    return null;
}
$saveDir .= DIRECTORY_SEPARATOR;

$statName = sprintf('slothsoft/fire-emblem-%s', $gameKey);
$statList = $this->getResourceDir($statName, 'xml');

$dataDoc = $this->getResourceDoc('slothsoft/fire-emblem', 'xml');
$dataPath = new DOMXPath($dataDoc);
$dataRoot = $dataPath->evaluate('//*[@wiki-uri]');
$dataRoot = $dataRoot->item(0);

$wikiURI = $dataRoot->getAttribute('wiki-uri');

$gameNodeList = $dataPath->evaluate(sprintf('//game[@key = "%s"]', $gameKey));
foreach ($gameNodeList as $gameNode) {
    $gameNode->setAttribute('active', '');
    $gameNames = [];
    $gameNames[] = $gameNode->getAttribute('name');
    if ($gameNode->hasAttribute('alternate')) {
        $gameNames = array_merge($gameNames, explode(',', $gameNode->getAttribute('alternate')));
    }
    $gameNames[] = $gameNode->getAttribute('name');
    $mappingList = [];
    $mappingText = $dataPath->evaluate('string(mapping)', $gameNode);
    preg_match_all('/(\w+)\/([^\s]+)/', $mappingText, $matches);
    foreach ($matches[0] as $i => $tmp) {
        $char1 = $matches[1][$i];
        $char2 = $matches[2][$i];
        $mappingList[$char1] = $char2;
    }
    $filterList = false;
    $filterText = $dataPath->evaluate('string(filter)', $gameNode);
    if ($filterText) {
        $filterList = [];
        preg_match_all('/(\w+)\s?/', $filterText, $matches);
        foreach ($matches[0] as $i => $tmp) {
            $filterList[] = lookupCharName($mappingList, $matches[1][$i]);
        }
    }
    $convoList = [];
    // $convoText = $dataPath->evaluate('string(support)', $gameNode);
    $convoType = $dataPath->evaluate('string(support/@type)', $gameNode);
    if ($convoNode = $gameNode->getElementsByTagName('support')->item(0)) {
        $convoText = $convoNode->textContent;
        switch ($convoType) {
            case '/':
                preg_match_all('/([\w\']+)\/([\w\']+)/', $convoText, $matches);
                foreach ($matches[0] as $i => $tmp) {
                    $char1 = lookupCharName($mappingList, $matches[1][$i]);
                    $char2 = lookupCharName($mappingList, $matches[2][$i]);
                    addSupport($convoList, $char1, $char2);
                }
                break;
            case ':':
                $arr = explode(':', $convoText);
                $activeChar = null;
                foreach ($arr as $tmp) {
                    if ($activeChar) {
                        preg_match_all('/([\w\']+)\s+\d/', $tmp, $matches);
                        foreach ($matches[1] as $char2) {
                            $char2 = lookupCharName($mappingList, $char2);
                            addSupport($convoList, $activeChar, $char2);
                        }
                    }
                    if (preg_match('/([\w\']+)$/', $tmp, $match)) {
                        $activeChar = $match[1];
                        $activeChar = lookupCharName($mappingList, $activeChar);
                    }
                }
                
                break;
        }
    }
    
    $charList = [];
    foreach ($statList as $statDoc) {
        $xpath = self::loadXPath($statDoc);
        $attrList = [];
        $nodeList = $xpath->evaluate('/*/line[1]/cell');
        foreach ($nodeList as $i => $node) {
            $attrList[$i] = $xpath->evaluate('string(@val)', $node);
        }
        $charNodeList = $xpath->evaluate('/*/line[position() > 1]');
        foreach ($charNodeList as $charNode) {
            $charName = $xpath->evaluate('string(cell[1]/@val)', $charNode);
            $charName = lookupCharName($mappingList, $charName);
            if ($filterList and ! in_array($charName, $filterList)) {
                continue;
            }
            if (! isset($charList[$charName])) {
                $charList[$charName] = [];
            }
            $nodeList = $xpath->evaluate('cell', $charNode);
            foreach ($nodeList as $i => $node) {
                $charList[$charName][$attrList[$i]] = $xpath->evaluate('string(@val)', $node);
            }
            if (isset($charList[$charName]['Strength-Growth']) and ! isset($charList[$charName]['Magic-Growth'])) {
                $charList[$charName]['Magic-Growth'] = $charList[$charName]['Strength-Growth'];
            }
            $charList[$charName]['MappedName'] = $charName;
        }
    }
    if ($filterList) {
        $tmpList = [];
        foreach ($filterList as $charName) {
            if (isset($charList[$charName])) {
                $uri = $wikiURI . $charName;
                $tmpList[$uri] = $charList[$charName];
            } else {
                echo '/' . $charName . PHP_EOL;
            }
        }
        $charList = $tmpList;
    } else {
        $tmpList = [];
        foreach ($charList as $charName => $char) {
            $uri = $wikiURI . $charName;
            $tmpList[$uri] = $char;
        }
        $charList = $tmpList;
    }
    $uriList = array_keys($charList);
    // my_dump($uriList);die();
    $code = '\Storage::loadExternalDocument($args, TIME_MONTH);';
    \Lambda\Manager::executeList($code, $uriList);
    
    // my_dump($charList);
    
    // load HTML
    foreach ($charList as $charURI => &$charData) {
        if ($xpath = \Storage::loadExternalXPath($charURI, TIME_MONTH)) {
            $nodeList = $xpath->evaluate('//table[contains(., "Game")][1]'); // class="toccolours"
            if (! $nodeList->length) {
                echo $charURI . PHP_EOL;
            }
            foreach ($nodeList as $node) {
                $fallbackImage = $xpath->evaluate('string(.//a[1]/@href)', $node);
                $charName = $xpath->evaluate('normalize-space(.//b[1])', $node);
                $classNodes = $xpath->evaluate('.//tr[td/b = "Starting Class"]/td[2]', $node);
                foreach ($classNodes as $classNode) {
                    $className = $xpath->evaluate('string(node()[1])', $classNode);
                    $tmpList = $xpath->evaluate('.//a', $classNode);
                    foreach ($tmpList as $tmpNode) {
                        if ($tmpNode->nextSibling instanceof \DOMText) {
                            $tmp = $tmpNode->nextSibling->wholeText;
                            if (stripos($tmp, $gameNode->getAttribute('name')) !== false) {
                                $className = $xpath->evaluate('string(.)', $tmpNode);
                            }
                        }
                    }
                }
                if (preg_match('/[\w\s]+/', $className, $match)) {
                    $className = $match[0];
                }
                $imgList = $xpath->evaluate('//*[@class="wikia-gallery-item"]');
                $image = null;
                $isPortrait = false;
                foreach ($imgList as $img) {
                    $uri = $xpath->evaluate('string(.//img/@src)', $img);
                    $text = $xpath->evaluate('normalize-space(.//*[@class="lightbox-caption"])', $img);
                    if ($uri and $text) {
                        $imageFindList = [
                            [
                                'Astrid as she appears',
                                'Path of Radiance'
                            ],
                            [
                                'Sothe as a Rogue',
                                'Radiant Dawn'
                            ],
                            [
                                'Jagen in',
                                'Shadow Dragon'
                            ],
                            [
                                'Lyn',
                                'portrait in Rekka no Ken'
                            ],
                            [
                                'Eliwood',
                                'Tactician'
                            ],
                            [
                                'Profile picture.'
                            ],
                            [
                                'Cormag\'s in-game portrait'
                            ],
                            [
                                'Micaiah, as a Light Mage'
                            ],
                            [
                                'Cynthia\'s full sized confession.'
                            ],
                            [
                                'Ena\'s portrait'
                            ]
                        ];
                        $imageNameBreaks = [
                            'Astrid',
                            'Sothe',
                            'Jagen',
                            'Julian',
                            'Lyn',
                            'Eliwood'
                        ];
                        foreach ($gameNames as $tmp) {
                            $imageFindList[] = [
                                'portrait',
                                $charName,
                                $tmp
                            ];
                            $imageFindList[] = [
                                'appears in',
                                $charName,
                                $tmp
                            ];
                            $imageFindList[] = [
                                'appeared in',
                                $charName,
                                $tmp
                            ];
                        }
                        // $imageFindList[] = ['portrait', $charName];
                        $imageNot = [
                            'face',
                            'miniportrait'
                        ];
                        foreach ($imageFindList as $imageFind) {
                            $found = true;
                            foreach ($imageFind as $tmp) {
                                if (stripos($text, $tmp) === false) {
                                    $found = false;
                                    break;
                                }
                            }
                            foreach ($imageNot as $tmp) {
                                if (stripos($text, $tmp) !== false) {
                                    $found = false;
                                    break;
                                }
                            }
                            if ($found) {
                                if (! $image or ! $isPortrait) {
                                    $image = $uri;
                                    $isPortrait = (stripos($text, 'portrait') !== false);
                                    if (in_array($charName, $imageNameBreaks)) {
                                        break 2;
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
                if (! $image) {
                    foreach ($imgList as $img) {
                        $uri = $xpath->evaluate('string(.//img/@src)', $img);
                        $text = $xpath->evaluate('normalize-space(.//*[@class="lightbox-caption"])', $img);
                        if ($uri and $text) {
                            $imageFind = [
                                'portrait'
                            ];
                            $imageNot = [
                                'face',
                                'miniportrait'
                            ];
                            $found = true;
                            foreach ($imageFind as $tmp) {
                                if (stripos($text, $tmp) === false) {
                                    $found = false;
                                    break;
                                }
                            }
                            foreach ($imageNot as $tmp) {
                                if (stripos($text, $tmp) !== false) {
                                    $found = false;
                                    break;
                                }
                            }
                            if ($found) {
                                if (! $image or ! $isPortrait) {
                                    $image = $uri;
                                    $isPortrait = (stripos($text, 'portrait') !== false);
                                    if (in_array($charName, $imageNameBreaks)) {
                                        break 2;
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
                if (! $image) {
                    $image = $fallbackImage;
                }
                if (preg_match('/^(.+?\.png)/', $image, $match)) {
                    // $image = $match[1];
                }
                // my_dump($charName);
                $char = [
                    'wiki-name' => $charName,
                    'wiki-image' => $image,
                    'wiki-href' => $charURI,
                    'wiki-class-name' => $className,
                    'wiki-class-href' => $wikiURI . str_replace(' ', '_', $className)
                ];
                foreach ($char as $key => $val) {
                    $charData[$key] = $val;
                }
            }
        }
    }
    unset($charData);
    
    // create XML
    foreach ($charList as $charURI => $charData) {
        $charName = $charData['MappedName'];
        $charNode = $dataDoc->createElement('char');
        foreach ($charData as $key => $val) {
            $tmpNode = $dataDoc->createElement('data');
            $tmpNode->setAttribute('key', $key);
            $tmpNode->appendChild($dataDoc->createTextNode($val));
            $charNode->appendChild($tmpNode);
        }
        if (isset($convoList[$charName])) {
            foreach ($convoList[$charName] as $char2) {
                $tmpNode = $dataDoc->createElement('support');
                $tmpNode->setAttribute('with', $char2);
                $charNode->appendChild($tmpNode);
            }
        } else {
            // my_dump($charName);
        }
        $gameNode->appendChild($charNode);
    }
}
return $dataDoc;

/*
$classes = [
	'Finesse Base' => [
		'Cavalier', 'Pegasus Knight', 'Archer', 'Myrmidon', 'Mage', 'Cleric', 
	],
	'Finesse Promoted' => [
		'Paladin', 'Sage', 'Sniper', 'Dracoknight', 'Bishop', 
	],
	'Brute Base' => [
		'Mercenary', 'Axe Fighter', 'Hunter', 'Pirate', 'Knight', 'Dark Mage',
	],
	'Brute Promoted' => [
		'Sorcerer', 'Horseman', 'Berserker', 'Warrior', 'Hero', 'General', 
	],
	'Other' => [
		'Lord', 'Thief', 'Manakete', 'Ballistician', 'Chameleon', 
	],
];
//*/