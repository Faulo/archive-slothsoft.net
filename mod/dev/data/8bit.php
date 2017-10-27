<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\Image;
use Slothsoft\Core\Storage;

$uri = 'http://www.nuklearpower.com/2001/03/02/episode-001-were-going-where/';

$blackList = <<<'EOT'
Episode 1019: You Already Saw This One.
Thanksgiving 2002 Guest Comic
Thanksgiving 2002 Guest Comic
track 1 – i throw my toys around
E3 Guest Week #1 “Citizen Mage”
E3 Guest Week #2 “Bad Dudes”
E3 Guest Week #3 “MST8BT”
E3 Guest Week #4 “Finally!”
E3 Guest Week #5 “I Wouldn’t Handle E3 Well”
E3 Guest Week #6 “Where Are They Now?”
E3 Guest Week #7 “The Gong Comic”
Vacation 2003: #1 Cookies!
Vacation 2003: #2 Twinkin’ Out
Vacation 2003: #3 Requiem for a Black Mage
Vacation 2003 #4: Demands
Vacation 2003 #5: Chromatic Discussion
Vacation 2003 #6: The Day the Music Stopped
Thanksgiving ’03: Cheap Cop Out–I Mean–Mail Bag!
Christmas ’03 Special So Brian Can Rest
Propaganda Posters of Vana’diel
8-Bit Theater #1 Gold Foil Embossed Collection Edition!
8-bit Theater: The Movie
Last Weird MegaCon Side Comic Thing
Teaser!
Vacation ’04 #1: The Sosa-like Entry
Vacation ’04 #2 “It won’t get weird.”
Vacation ’04 #3: I Love Bridge Jokes
Vacation ’04 #4: In A World…
Vacation ’04 #5: About Class Changes
Vacation ’04 #6: “I didn’t do it.”
Vacation ’04 #7: The Real Story
Vacation ’04 #8: Something Amiss
For Michael
I’ll sleep when I’m dead. Like now.
Self Indulgent Tripe
I’m just a stupid place holder
Logo Imitates Life
Half-Life 2 Comic? On the internet? Now that’s new!
Season’s WARNINGS
And now, a special New Year presentation!
Technicolor Yawn
E3 ’05: The Heart of the Matter
E3 ’05: You’re in Trouble
E3 ’05: Sprite Noir
E3 ’05: Uneventful Reunion
E3 ’05: Checks and Balances
E3 ’05: White Magic for Dummies
E3 ’05: Dream Analysis
E3 ’05: Monster Manual
Episode: 566: Spelunk’d!
Field of Battle Prelude
Field of Battle Chapter 1
Field of Battle Chapter 2
Field of Battle Chapter 3
Field of Battle Chapter 4
Field of Battle Chapter 5
Field of Battle Chapter 6
Field of Battle: Top Secret Files
Operation: Nomenclature
Nuklear Age 2.0 cover preview
A Story, Page 1
A Story, Page 2
A Story, Page 3
A Story, Page 4
A Story, Page 5
VACATION ’07: Take A Gander at Robo!
VACATION ’07: Take Another Gander At Robo!
Civilization Daydreams: FAILURE
Civilization Daydreams: Research, Research, Research
VACATION ’07: One Last Gander For You
Christmas ’07 Cop Out!
Guest Comic Surprise!
Dead Computer Day
EOT;
$blackList = explode(PHP_EOL, $blackList);
// my_dump($blackList);die();

$imageDir = realpath(__DIR__ . '/../res/8bit');
if (! $imageDir) {
    return;
}
$thumbDir = realpath(__DIR__ . '/../res/8bit-thumb');
if (! $thumbDir) {
    return;
}

$imageFile = $imageDir . DIRECTORY_SEPARATOR . '../8bit.xml';
$thumbWidth = 256;
$thumbHeight = 256;
$thumbFactor = 4;

$queries = [];
$queries['uri'] = 'normalize-space(//html:a[@rel="next"]/@href)';
$queries['title'] = 'normalize-space(//*[@id="comic"]//@title)';
$queries['image'] = 'normalize-space(//*[@id="comic"]//@src)';

$i = 1;
$comicList = [];

do {
    echo $uri . PHP_EOL;
    
    $href = $uri;
    $xpath = Storage::loadExternalXPath($uri, TIME_YEAR);
    $uri = null;
    if ($xpath) {
        $uri = $xpath->evaluate($queries['uri']);
        $title = $xpath->evaluate($queries['title']);
        $image = $xpath->evaluate($queries['image']);
        
        if ($image) {
            $ext = substr($image, strrpos($image, '.'));
            $name = sprintf('%04d%s', $i, $ext);
            $path = sprintf('%s%s%s', $imageDir, DIRECTORY_SEPARATOR, $name);
            $thumbFile = sprintf('%s%s%04d.png', $thumbDir, DIRECTORY_SEPARATOR, $i);
            
            $arr = [];
            $arr['title'] = $title;
            $arr['key'] = sprintf('%04d', $i);
            $arr['image'] = sprintf('/getResource.php/dev/8bit-comics/%s', $name);
            $arr['thumb'] = sprintf('/getResource.php/dev/8bit-thumbs/%s', $arr['key']);
            $arr['href'] = $href;
            
            // my_dump([$uri, $title, $image, $path]);die();
            
            $i ++;
            
            if (! file_exists($path)) {
                if ($file = HTTPFile::createFromURL($image)) {
                    echo $path . PHP_EOL;
                    $file->copyTo($imageDir, $name);
                }
            }
            if (file_exists($path)) {
                // if (!file_exists($thumbFile)) {
                try {
                    $arr += Image::imageInfo($path);
                    $image = Image::createFromFile($path);
                    $thumb = imagecreatetruecolor($thumbWidth / $thumbFactor, $thumbHeight / $thumbFactor);
                    imagecopyresized($thumb, $image, 0, 0, 0, 0, $thumbWidth / $thumbFactor, $thumbHeight / $thumbFactor, $thumbWidth, $thumbHeight);
                    imagepng($thumb, $thumbFile);
                } catch (\Exception $e) {}
                // }
            }
            
            if (in_array($title, $blackList)) {
                echo '	SKIPPING!' . PHP_EOL;
            } else {
                $comicList[] = $arr;
            }
        }
    }
} while ($uri and $i < 1385);

$doc = new \DOMDocument('1.0', 'UTF-8');
$rootNode = $doc->createElement('data');
$doc->appendChild($rootNode);
foreach ($comicList as $arr) {
    $node = $doc->createElement('comic');
    foreach ($arr as $key => $val) {
        $node->setAttribute($key, $val);
    }
    $rootNode->appendChild($node);
}
$doc->save($imageFile);

//file_put_contents($imageFile, json_encode($comicList));