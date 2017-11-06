<?php
namespace Slothsoft\Savegame\Node;

use Exception;
declare(ticks = 1000);

class StringDictionaryInstruction extends AbstractInstructionContent
{

    const LIST_TYPE_NULL_DELIMITED = 'null-delimited';

    const LIST_TYPE_SIZE_INTERSPERSED = 'size-interspersed';

    const LIST_TYPE_SIZE_FIRST = 'size-first';

    const LIST_TYPE_SIZE_FIXED = 'size-fixed';

    public function __construct()
    {
        parent::__construct();
        $this->strucData['type'] = '';
        $this->strucData['encoding'] = '';
        $this->strucData['string-count'] = '';
        $this->strucData['string-size'] = '';
    }

    protected function loadInstruction()
    {
        $this->instructionList = [];
        
        // string-count
        switch ($this->strucData['type']) {
            case self::LIST_TYPE_NULL_DELIMITED:
                $this->strucData['string-count'] = $this->parseInt($this->strucData['string-count']);
                break;
            case self::LIST_TYPE_SIZE_INTERSPERSED:
            case self::LIST_TYPE_SIZE_FIRST:
                $countSize = 2;
                for ($countOffset = 0; $countOffset < 10; $countOffset += $countSize) {
                    $count = $this->ownerFile->extractContent($this->valueOffset + $countOffset, $countSize);
                    $count = $this->converter->decodeInteger($count, $countSize);
                    if ($count > 0) {
                        $this->strucData['string-count'] = $count;
                        break;
                    }
                }
                break;
            case self::LIST_TYPE_SIZE_FIXED:
                $this->strucData['string-size'] = $this->parseInt($this->strucData['string-size']);
                $this->strucData['string-count'] = $this->parseInt($this->strucData['string-count']);
                break;
            default:
                throw new Exception('unknown text-list type: ' . $this->strucData['type']);
        }
        
        switch ($this->strucData['type']) {
            case self::LIST_TYPE_NULL_DELIMITED:
                $textOffset = $this->valueOffset;
                for ($i = 0; $i < $this->strucData['string-count']; $i ++) {
                    $text = $this->ownerFile->extractContent($textOffset, 'auto');
                    $textLength = strlen($text);
                    
                    if (! $textLength) {
                        // break;
                    }
                    
                    $strucData = [];
                    $strucData['position'] = $textOffset - $this->valueOffset;
                    $strucData['size'] = $textLength;
                    $strucData['encoding'] = $this->strucData['encoding'];
                    
                    $this->instructionList[] = [
                        'tagName' => 'string',
                        'element' => $this->getStrucElement(),
                        'strucData' => $strucData,
                    ];
                    
                    $textOffset += $textLength + 1;
                }
                break;
            case self::LIST_TYPE_SIZE_INTERSPERSED:
                $countSize = 2;
                $textLengthSize = 1;
                
                $textOffset = $this->valueOffset + $countSize;
                for ($i = 0; $i < $this->strucData['string-count']; $i ++) {
                    $textLength = $this->ownerFile->extractContent($textOffset, $textLengthSize);
                    $textLength = $this->converter->decodeInteger($textLength, $textLengthSize);
                    
                    $textOffset += $textLengthSize;
                    
                    $strucData = [];
                    $strucData['position'] = $textOffset - $this->valueOffset;
                    $strucData['size'] = $textLength;
                    $strucData['encoding'] = $this->strucData['encoding'];
                    
                    $this->instructionList[] = [
                        'tagName' => 'string',
                        'element' => $this->getStrucElement(),
                        'strucData' => $strucData,
                    ];
                    
                    $textOffset += $textLength;
                }
                break;
            case self::LIST_TYPE_SIZE_FIRST:
                $countSize = 2;
                $textLengthSize = 2;
                
                $textOffset = $this->valueOffset + $countOffset + $countSize;
                $textLengthList = [];
                for ($i = 0; $i < $this->strucData['string-count']; $i ++) {
                    $textLength = $this->ownerFile->extractContent($textOffset, $textLengthSize);
                    $textLength = $this->converter->decodeInteger($textLength, $textLengthSize);
                    
                    $textLengthList[] = $textLength;
                    
                    $textOffset += $textLengthSize;
                }
                $textOffset = $this->valueOffset + $countOffset + $countSize + $this->strucData['string-count'] * $textLengthSize;
                foreach ($textLengthList as $textLength) {
                    $strucData = [];
                    $strucData['position'] = $textOffset - $this->valueOffset;
                    $strucData['size'] = $textLength;
                    $strucData['encoding'] = $this->strucData['encoding'];
                    
                    $this->instructionList[] = [
                        'tagName' => 'string',
                        'element' => $this->getStrucElement(),
                        'strucData' => $strucData,
                    ];
                    
                    $textOffset += $textLength;
                }
                break;
            case self::LIST_TYPE_SIZE_FIXED:
                $textPosition = 0;
                for ($i = 0; $i < $this->strucData['string-count']; $i ++) {
                    $strucData = [];
                    $strucData['position'] = $textPosition;
                    $strucData['size'] = $this->strucData['string-size'];
                    $strucData['encoding'] = $this->strucData['encoding'];
                    
                    $this->instructionList[] = [
                        'tagName' => 'string',
                        'element' => $this->getStrucElement(),
                        'strucData' => $strucData,
                    ];
                    
                    $textPosition += $this->strucData['string-size'];
                }
                break;
        }
    }
}
