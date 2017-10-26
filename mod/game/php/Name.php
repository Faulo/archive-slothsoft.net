<?php
namespace Slothsoft\Game;

use Slothsoft\Core\Storage;

class Name
{

    const GENERATE_URI = 'http://listofrandomnames.com/index.cfm';

    const GENERATE_CONFIG_ALLITERATION = 'allit';

    const GENERATE_CONFIG_FIRSTNAMEONLY = 'fnameonly';

    const GENERATE_CONFIG_COUNT = 'numberof';

    protected static $generateConfig = [
        'generated' => '',
        'action' => 'main.generate',
        'nameType' => 'na',
        'allit' => 1,
        'fnameonly' => 1,
        'numberof' => 1
    ];

    public static function generate(array $config = [])
    {
        $ret = null;
        $param = self::$generateConfig;
        foreach ($param as $key => &$val) {
            if (isset($config[$key])) {
                settype($config[$key], gettype($val));
                $val = $config[$key];
            }
        }
        unset($val);
        
        if ($xpath = Storage::loadExternalXPath(self::GENERATE_URI, 0, $param, [
            'method' => 'POST'
        ])) {
            $ret = [];
            $nodeList = $xpath->evaluate('//*[@id="nameres"]/*');
            foreach ($nodeList as $node) {
                $ret[] = $xpath->evaluate('normalize-space(.)', $node);
            }
        }
        return $ret;
    }
}