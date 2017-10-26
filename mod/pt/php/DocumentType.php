<?php
/**
 * DocumentType
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#ID-412266927
 */
namespace Slothsoft\PT;

class DocumentType extends Node implements \w3c\dom\DocumentType
{

    /**
     *
     * @return string
     */
    public function getName()
    {
        if ($this->name === null) {
            $this->_loadName();
        }
        return $this->name;
    }

    /**
     *
     * @return array
     */
    public function getEntities()
    {}

    /**
     *
     * @return array
     */
    public function getNotations()
    {}

    /**
     *
     * @return string
     */
    public function getPublicId()
    {
        return '';
    }

    /**
     *
     * @return string
     */
    public function getSystemId()
    {
        return '';
    }

    /**
     *
     * @return string
     */
    public function getInternalSubset()
    {}
}