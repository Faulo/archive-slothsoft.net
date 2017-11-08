<?php
namespace Slothsoft\Savegame\Node;


declare(ticks = 1000);

class FileContainer extends AbstractContainerContent
{

    /**
     * @var string
     */
    protected $content;
    
    protected $valueList = [];

    public function __construct()
    {
        parent::__construct();
        $this->strucData['file-name'] = '';
    }

    protected function initStrucAttributes(array $overrideData)
    {
        assert(isset($overrideData['file-path']) and file_exists($overrideData['file-path']), '$overrideData must contain file-path');
        
        $this->setContent(file_get_contents($overrideData['file-path']));
        unset($overrideData['file-path']);
        
        return parent::initStrucAttributes($overrideData);
    }
    protected function loadStruc()
    {
        $this->ownerFile = $this;
        
        parent::loadStruc();
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
        return $this->strucData['file-name'];
    }
    public function getValueByName(string $name) {
        if (!isset($this->valueList[$name])) {
            $this->valueList[$name] = parent::getValueByName($name);
        }
        return $this->valueList[$name];
    }
}