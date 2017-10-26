<?php
/**
 * Text
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#ID-1312295772
 */
namespace w3c\dom;

interface Text extends CharacterData
{

    /**
     *
     * @return bool
     */
    public function getIsElementContentWhitespace();

    /**
     *
     * @return string
     */
    public function getWholeText();

    /**
     *
     * @param int $offset
     * @throws DOMException
     * @return Text
     */
    public function splitText($offset);

    /**
     *
     * @param string $content
     * @throws DOMException
     * @return Text
     */
    public function replaceWholeText($content);
}