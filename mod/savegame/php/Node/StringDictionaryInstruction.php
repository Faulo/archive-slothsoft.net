<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
use DomainException;
use Traversable;
use DS\Vector;

declare(ticks = 1000);

class StringDictionaryInstruction extends AbstractInstructionContent
{

    const LIST_TYPE_NULL_DELIMITED = 'null-delimited';

    const LIST_TYPE_SIZE_INTERSPERSED = 'size-interspersed';

    const LIST_TYPE_SIZE_FIRST = 'size-first';

    const LIST_TYPE_SIZE_FIXED = 'size-fixed';
    
    private $type;
    private $encoding;
    private $stringCount;
    private $stringSize;
    
    public function loadStruc()
    {
        parent::loadStruc();
        
        $this->encoding = $this->loadStringAttribute('encoding');
        $this->type = $this->loadStringAttribute('type');
        
        //$this->stringCount = $this->loadIntegerAttribute('string-count');
        //$this->stringSize = $this->loadIntegerAttribute('string-size');
    }

    protected function loadInstruction()
    {
        $instructionList = [];
        
        // string-count
        switch ($this->type) {
            case self::LIST_TYPE_NULL_DELIMITED:
                $this->stringCount = $this->loadIntegerAttribute('string-count');
                break;
            case self::LIST_TYPE_SIZE_INTERSPERSED:
            case self::LIST_TYPE_SIZE_FIRST:
                $countSize = 2;
                for ($countOffset = 0; $countOffset < 10; $countOffset += $countSize) {
                    $count = $this->getOwnerFile()->extractContent($this->valueOffset + $countOffset, $countSize);
                    $count = $this->getConverter()->decodeInteger($count, $countSize);
                    if ($count > 0) {
                        $this->stringCount = $count;
                        break;
                    }
                }
                break;
            case self::LIST_TYPE_SIZE_FIXED:
                $this->stringCount = $this->loadIntegerAttribute('string-count');
                $this->stringSize = $this->loadIntegerAttribute('string-size');
                break;
            default:
                throw new DomainException('unknown text-list type: ' . $this->type);
        }
        
        switch ($this->type) {
            case self::LIST_TYPE_NULL_DELIMITED:
                $textOffset = $this->valueOffset;
                for ($i = 0; $i < $this->stringCount; $i ++) {
                    $text = $this->getOwnerFile()->extractContent($textOffset, 'auto');
                    $textLength = strlen($text);
                    
                    if (! $textLength) {
                        // break;
                    }
                    
                    $strucData = [];
                    $strucData['position'] = $textOffset - $this->valueOffset;
                    $strucData['size'] = $textLength;
                    $strucData['encoding'] = $this->encoding;
                    
                    $instructionList[] = $this->getStrucElement()->clone(EditorElement::NODE_TYPES['string'], $strucData);
                    
                    $textOffset += $textLength + 1;
                }
                break;
            case self::LIST_TYPE_SIZE_INTERSPERSED:
                $countSize = 2;
                $textLengthSize = 1;
                
                $textOffset = $this->valueOffset + $countSize;
                for ($i = 0; $i < $this->stringCount; $i ++) {
                    $textLength = $this->getOwnerFile()->extractContent($textOffset, $textLengthSize);
                    $textLength = $this->getConverter()->decodeInteger($textLength, $textLengthSize);
                    
                    $textOffset += $textLengthSize;
                    
                    $strucData = [];
                    $strucData['position'] = $textOffset - $this->valueOffset;
                    $strucData['size'] = $textLength;
                    $strucData['encoding'] = $this->encoding;
                    
                    $instructionList[] = $this->getStrucElement()->clone(EditorElement::NODE_TYPES['string'], $strucData);
                    
                    $textOffset += $textLength;
                }
                break;
            case self::LIST_TYPE_SIZE_FIRST:
                $countSize = 2;
                $textLengthSize = 2;
                
                $textOffset = $this->valueOffset + $countOffset + $countSize;
                $textLengthList = [];
                for ($i = 0; $i < $this->stringCount; $i ++) {
                    $textLength = $this->getOwnerFile()->extractContent($textOffset, $textLengthSize);
                    $textLength = $this->getConverter()->decodeInteger($textLength, $textLengthSize);
                    
                    $textLengthList[] = $textLength;
                    
                    $textOffset += $textLengthSize;
                }
                $textOffset = $this->valueOffset + $countOffset + $countSize + $this->stringCount * $textLengthSize;
                foreach ($textLengthList as $textLength) {
                    $strucData = [];
                    $strucData['position'] = $textOffset - $this->valueOffset;
                    $strucData['size'] = $textLength;
                    $strucData['encoding'] = $this->encoding;
                    
                    $instructionList[] = $this->getStrucElement()->clone(EditorElement::NODE_TYPES['string'], $strucData);
                    
                    $textOffset += $textLength;
                }
                break;
            case self::LIST_TYPE_SIZE_FIXED:
                $textPosition = 0;
                for ($i = 0; $i < $this->stringCount; $i ++) {
                    $strucData = [];
                    $strucData['position'] = $textPosition;
                    $strucData['size'] = $this->stringSize;
                    $strucData['encoding'] = $this->encoding;
                    
                    $instructionList[] = $this->getStrucElement()->clone(EditorElement::NODE_TYPES['string'], $strucData);
                    
                    $textPosition += $this->stringSize;
                }
                break;
        }
        
        return count($instructionList)
        ? new Vector($instructionList)
        : null;
    }
}
