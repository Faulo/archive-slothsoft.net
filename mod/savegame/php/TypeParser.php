<?php
namespace Slothsoft\Savegame;

use Slothsoft\Savegame\Node\AbstractNode;

/**
 *
 * @author Daniel Schulz
 *        
 */
class TypeParser
{

    /**
     *
     * @return \Slothsoft\Savegame\TypeParser
     */
    public static function getInstance()
    {
        static $instance;
        if (! $instance) {
            $instance = new TypeParser();
        }
        return $instance;
    }

    protected $resultCache = [];

    public function evaluate($expression, AbstractNode $context)
    {
        if (is_int($expression)) {
            return $expression;
        }
        $expression = trim($expression);
        if ($expression === '') {
            return 0;
        }
        if (is_numeric($expression)) {
            return (int) $expression;
        }
        if (preg_match('/^0x(\w+)$/', $expression, $match)) {
            return hexdec($match[1]);
        }
        
        $id = $context->getNodeId();
        if (! isset($this->resultCache[$id])) {
            $this->resultCache[$id] = [];
        }
        if (! isset($this->resultCache[$id][$expression])) {
            preg_match_all('/\$([A-Za-z0-9\-\.]+)/', $expression, $matches);
            $translate = [];
            foreach ($matches[0] as $i => $key) {
                if ($node = $context->getValueByName($matches[1][$i])) {
                    $val = $node->getValue();
                } else {
                    $val = 0;
                }
                $translate[$key] = $val;
            }
            $code = strtr($expression, $translate);
            $code = trim($code);
            // echo $code . PHP_EOL;
            $this->resultCache[$id][$expression] = $this->evaluateMath($code);
            // echo $expression . PHP_EOL . $code . PHP_EOL . $this->resultCache[$id][$expression] . PHP_EOL . PHP_EOL;
        }
        return $this->resultCache[$id][$expression];
    }

    public function evaluateMath($code)
    {
        static $evalList = [];
        if (! isset($evalList[$code])) {
            $evalList[$code] = eval("return ($code);");
            // echo $code . PHP_EOL . $evalList[$code] . PHP_EOL . PHP_EOL;
        }
        return $evalList[$code];
    }
}

