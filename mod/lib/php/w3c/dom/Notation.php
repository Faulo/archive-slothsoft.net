<?php
/**
 * Notation
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#ID-5431D1B9
 */
namespace w3c\dom;

interface Notation extends Node
{

    /**
     *
     * @return string
     */
    public function getPublicId();

    /**
     *
     * @return string
     */
    public function getSystemId();
}