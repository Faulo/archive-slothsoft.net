<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

abstract class AbstractContentNode extends AbstractNode
{

    protected $valueOffset = 0;

    abstract protected function loadContent();

    public function __construct()
    {
        parent::__construct();
        $this->strucData['name'] = '';
        $this->strucData['position'] = '0';
        // $this->strucData['dict'] = '';
        // $this->strucData['title'] = '';
        
        // calculated
        // $this->strucData['offset'] = '';
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        $this->strucData['position'] = $this->parseInt($this->strucData['position']);
        $this->valueOffset = $this->strucData['position'];
        if ($this->parentNode instanceof AbstractContentNode) {
            $this->valueOffset += $this->parentNode->getOffset();
        }
    }

    protected function loadNode()
    {
        $this->loadContent();
    }

    public function getOffset()
    {
        return $this->valueOffset;
    }
    /*
     * protected function encode($val, $valueType = null, $strucData = null) {
     * $ret = null;
     * if ($valueType === null) {
     * $valueType = $this->valueType;
     * }
     * if ($strucData === null) {
     * $strucData = $this->strucData;
     * }
     * switch ($valueType) {
     * case self::TYPE_EMPTY:
     * break;
     * case self::TYPE_INTEGER:
     * case self::TYPE_INTEGER_SIGNED:
     * if ($valueType === self::TYPE_INTEGER_SIGNED) {
     * if ($val < 0) {
     * $ret += pow(256, $strucData['size']);
     * }
     * }
     * $val = (int) $val;
     * $ret = pack('N', $val);
     * $ret = substr($ret, - $strucData['size']);
     * break;
     * case self::TYPE_STRING:
     * $val = (string) $val;
     * if (isset($strucData['encoding']) and $strucData['encoding']) {
     * $val = mb_convert_encoding($val, $strucData['encoding'], 'UTF-8');
     * }
     * $ret = substr($val, 0, $strucData['size']);
     * for ($i = strlen($ret); $i < $strucData['size']; $i++) {
     * $ret .= pack('x');
     * }
     * break;
     * }
     * return $ret;
     * }
     * protected function decode($val, $valueType = null, $strucData = null) {
     * $ret = null;
     * if ($valueType === null) {
     * $valueType = $this->valueType;
     * }
     * if ($strucData === null) {
     * $strucData = $this->strucData;
     * }
     * switch ($valueType) {
     * case self::TYPE_EMPTY:
     * break;
     * case self::TYPE_INTEGER:
     * case self::TYPE_INTEGER_SIGNED:
     * switch ($strucData['size']) {
     * case 1:
     * $format = 'C';
     * break;
     * case 2:
     * $format = 'n';
     * break;
     * case 3:
     * $val = "\0" . $val;
     * $format = 'N';
     * break;
     * case 4:
     * $format = 'N';
     * break;
     * default:
     * my_dump('unknown integer size: ' . $strucData['size']);
     * die;
     * break;
     * }
     * $ret = unpack($format, $val)[1];
     * if ($valueType === self::TYPE_INTEGER_SIGNED) {
     * if ($ret > pow(256, $strucData['size']) / 2) {
     * $ret -= pow(256, $strucData['size']);
     * }
     * }
     * break;
     * case self::TYPE_STRING:
     * $hex = bin2hex($val);
     * $ret = '';
     * for ($i = 0, $j = strlen($hex); $i < $j; $i += 2) {
     * $c = hexdec($hex[$i] . $hex[$i+1]);
     * if ($c > 31) {
     * $ret .= chr($c);
     * }
     * }
     * if (isset($strucData['encoding']) and $strucData['encoding']) {
     * $ret = mb_convert_encoding($ret, 'UTF-8', $strucData['encoding']);
     * }
     * //$ret = trim($ret);
     * break;
     * case self::TYPE_SCRIPT:
     * $rowSize = 12;
     * $byteSize = 2;
     *
     * $ret = [];
     *
     * for ($i = 0; $i < strlen($val); $i += $rowSize) {
     * $row = substr($val, $i, $rowSize);
     * $res = [];
     * for ($j = 0; $j < strlen($row); $j += $byteSize) {
     * $byte = substr($row, $j, $byteSize);
     * $res[] = $this->decode($byte, self::TYPE_INTEGER_SIGNED, ['size' => $byteSize]);
     * }
     * $goto = array_pop($res);
     * $goto = $goto === -1
     * ? 'return;'
     * : sprintf('goto %d;', $goto);
     *
     * $ret[] = sprintf('%d: call(%s) %s', count($ret), implode(', ', $res), $goto);
     * }
     * $ret = implode(PHP_EOL, $ret);
     * break;
     * }
     * return $ret;
     * }
     * //
     */
    
    /**
     * @return string
     */
    public function getName() {
        return $this->strucData['name'];
    }
}