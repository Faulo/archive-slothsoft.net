<?php
/**
 * DocumentType
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#ID-412266927
 */
namespace w3c\dom;

interface DocumentType extends Node
{

    /**
     *
     * @return string
     */
    public function getName();

    /**
     *
     * @return array
     */
    public function getEntities();

    /**
     *
     * @return array
     */
    public function getNotations();

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

    /**
     *
     * @return string
     */
    public function getInternalSubset();
}