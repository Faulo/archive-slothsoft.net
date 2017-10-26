<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\FileSystem;
use Slothsoft\Core\Image;
if ($this->httpRequest->hasInputValue('css')) {
    $portraitDir = __DIR__ . '/../res/portraits';
    $fileList = FileSystem::scanDir($portraitDir, FileSystem::SCANDIR_REALPATH);
    natsort($fileList);
    $fileList = array_values($fileList);
    
    $res = Image::createSprite($portraitDir . '/../portraits.png', 32, 34, 1, 103, $fileList);
    
    $ret = [];
    foreach ($fileList as $id => $tmp) {
        $ret[] = sprintf('*[data-dictionary="PortraitId"][data-picker-value="%d"]::after { background-position-y: -%dem; }', $id, $id);
    }
    return HTTPFile::createFromString(implode(PHP_EOL, $ret));
}

$portraits = <<<'EOT'

human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-male
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
human-female
gnome-male
gnome-male
gnome-male
gnome-male
gnome-male
gnome-male
gnome-male
elf-male
elf-male
elf-male
elf-male
elf-male
elf-male
elf-male
elf-male
elf-female
elf-female
elf-female
elf-female
elf-female
elf-female
elf-female
elf-female
elf-female
elf-female
sylph
sylph
cat
cat
dog
feline
morag
morag
morag
morag
morag
dwarf-male
dwarf-male
dwarf-male
dwarf-male
dwarf-male
dwarf-male
dwarf-male
dwarf-male
dwarf-male
dwarf-female
dwarf-female
dwarf-female
human-male
EOT;

$portraitList = explode(PHP_EOL, $portraits);

$ret = [];
$ret['Menschen und Halb-Elfen ♂'] = [
    'human-male'
];
$ret['Menschen und Halb-Elfen ♀'] = [
    'human-female'
];
$ret['Elfen ♂'] = [
    'elf-male'
];
$ret['Elfen ♀'] = [
    'elf-female'
];
$ret['Zwerge und Gnome ♂'] = [
    'dwarf-male',
    'gnome-male'
];
$ret['Zwerge und Gnome ♀'] = [
    'dwarf-female',
    'gnome-female'
];
$ret['Sylphen'] = [
    'sylph'
];
$ret['Moraner'] = [
    'morag'
];
$ret['Tiere'] = [
    'cat',
    'dog',
    'feline'
];

$labels = [];
foreach ($ret as $key => $tmp) {
    $labels[$key] = range('A', 'Z');
    $labels[$key][] = '$';
    $labels[$key][] = '€';
    $labels[$key][] = '@';
}
foreach ($ret as $listKey => &$list) {
    $val = [];
    foreach ($portraitList as $key => $tmp) {
        if (in_array($tmp, $list)) {
            $val[$key] = array_shift($labels[$listKey]);
        }
    }
    $list = $val;
}
unset($list);

$retFragment = $dataDoc->createDocumentFragment();
foreach ($ret as $label => $list) {
    $parentNode = $dataDoc->createElement('category');
    $parentNode->setAttribute('name', $label);
    foreach ($list as $key => $val) {
        $node = $dataDoc->createElement('portrait');
        $node->setAttribute('id', $key);
        $node->setAttribute('name', $val);
        $parentNode->appendChild($node);
    }
    $retFragment->appendChild($parentNode);
}

return $retFragment;