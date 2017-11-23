<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class FileContainer extends AbstractContainerContent
{
    private $filePath;
    private $fileName;
    
    /**
     *
     * @var string
     */
    private $content;
    
    private $evaluateCache = [];


    protected function getXmlAttributes() : string
    {
        $ret = parent::getXmlAttributes();
        $ret .= sprintf(
            ' file-name="%s"',
            htmlspecialchars($this->fileName, ENT_COMPAT | ENT_XML1)
        );
        return $ret;
    }
    
    public function getOwnerFile()
    {
        return $this;
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->filePath = $this->loadStringAttribute('file-path');
        $this->fileName = $this->loadStringAttribute('file-name');
        
        assert(file_exists($this->filePath), '$this->filePath must exist');
        
        $this->setContent(file_get_contents($this->filePath));

     
    }

    public function extractContent($offset, $length)
    {
        $ret = null;
        switch ($length) {
            case 'auto':
                $ret = '';
                for ($i = $offset, $j = strlen($this->content); $i < $j; $i ++) {
                    $char = $this->content[$i];
                    if ($char === "\0") {
                        break;
                    } else {
                        $ret .= $char;
                    }
                }
                break;
            default:
                $ret = substr($this->content, $offset, $length);
                $ret = str_pad($ret, $length, "\0");
                break;
        }
        return $ret;
    }

    public function insertContent($offset, $length, $value)
    {
        $this->content = substr_replace($this->content, $value, $offset, $length);
    }

    public function insertContentBit($offset, $bit, $value)
    {
        // echo "setting bit $bit at position $offset to " . ($value?'ON':'OFF') . PHP_EOL;
        $byte = $this->extractContent($offset, 1);
        $byte = hexdec(bin2hex($byte));
        if ($value) {
            $byte |= $bit;
        } else {
            $byte &= ~ $bit;
        }
        $byte = substr(pack('N', $byte), - 1);
        return $this->insertContent($offset, 1, $byte);
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getFileName()
    {
        return $this->fileName;
    }
    
    public function evaluate($expression)
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
        
        if (! isset($this->evaluateCache[$expression])) {
            preg_match_all('/\$([A-Za-z0-9\-\.]+)/', $expression, $matches);
            $translate = [];
            foreach ($matches[0] as $i => $key) {
                if ($node = $this->getOwnerEditor()->getValueByName($matches[1][$i], $this)) {
                    $val = $node->getValue();
                } else {
                    $val = 0;
                }
                $translate[$key] = $val;
            }
            $code = strtr($expression, $translate);
            $code = trim($code);
            // echo $code . PHP_EOL;
            $this->evaluateCache[$expression] = $this->evaluateMath($code);
            // echo $expression . PHP_EOL . $code . PHP_EOL . $this->evaluateCache[$expression] . PHP_EOL . PHP_EOL;
        }
        return $this->evaluateCache[$expression];
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