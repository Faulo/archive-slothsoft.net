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

    protected function loadStruc()
    {
        parent::loadStruc();
    }

    protected function loadInstruction()
    {
        $this->instructionElements = [];
        
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
                /*
                 * $this->strucData['string-count'] = $this->ownerFile->extractContent($this->valueOffset, $countSize);
                 * $this->strucData['string-count'] = $this->decode($this->strucData['string-count'], self::TYPE_INTEGER, ['size' => $countSize]);
                 * if (!$this->strucData['string-count']) {
                 * $testOffset = 4;
                 * $this->strucData['string-count'] = $this->ownerFile->extractContent($this->valueOffset + $testOffset, $countSize);
                 * $this->strucData['string-count'] = $this->decode($this->strucData['string-count'], self::TYPE_INTEGER, ['size' => $countSize]);
                 * if ($this->strucData['string-count']) {
                 * $this->valueOffset += $testOffset;
                 * }
                 * } else {
                 * $testOffset = 0;
                 * }
                 * //
                 */
                break;
            case self::LIST_TYPE_SIZE_FIXED:
                $this->strucData['string-size'] = $this->parseInt($this->strucData['string-size']);
                $this->strucData['string-count'] = $this->parseInt($this->strucData['string-count']);
                break;
            default:
                throw new Exception('unknown text-list type: ' . $this->strucData['type']);
        }
        
        $parentNode = $this->createInstructionContainer();
        
        switch ($this->strucData['type']) {
            case self::LIST_TYPE_NULL_DELIMITED:
                $parentNode->appendChild($this->createInstructionElement('group', []));
                
                $textOffset = $this->valueOffset;
                for ($i = 0; $i < $this->strucData['string-count']; $i ++) {
                    $text = $this->ownerFile->extractContent($textOffset, 'auto');
                    $textLength = strlen($text);
                    
                    if (! $textLength) {
                        // break;
                    }
                    
                    $instruction = [];
                    $instruction['position'] = $textOffset - $this->valueOffset;
                    $instruction['size'] = $textLength;
                    $instruction['encoding'] = $this->strucData['encoding'];
                    
                    $parentNode->appendChild($this->createInstructionElement('string', $instruction));
                    
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
                    
                    $instruction = [];
                    $instruction['position'] = $textOffset - $this->valueOffset;
                    $instruction['size'] = $textLength;
                    $instruction['encoding'] = $this->strucData['encoding'];
                    
                    $parentNode->appendChild($this->createInstructionElement('string', $instruction));
                    
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
                    $instruction = [];
                    $instruction['position'] = $textOffset - $this->valueOffset;
                    $instruction['size'] = $textLength;
                    $instruction['encoding'] = $this->strucData['encoding'];
                    
                    $parentNode->appendChild($this->createInstructionElement('string', $instruction));
                    
                    $textOffset += $textLength;
                }
                break;
            case self::LIST_TYPE_SIZE_FIXED:
                $textPosition = 0;
                for ($i = 0; $i < $this->strucData['string-count']; $i ++) {
                    $instruction = [];
                    $instruction['position'] = $textPosition;
                    $instruction['size'] = $this->strucData['string-size'];
                    $instruction['encoding'] = $this->strucData['encoding'];
                    
                    $parentNode->appendChild($this->createInstructionElement('string', $instruction));
                    
                    $textPosition += $this->strucData['string-size'];
                }
                break;
        }
        
        $this->instructionElements[] = $parentNode;
    }
}
