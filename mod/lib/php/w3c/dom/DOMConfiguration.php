<?php
/**
 * DOMConfiguration
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#DOMConfiguration
 */
namespace w3c\dom;

interface DOMConfiguration
{

    /**
     *
     * @return array
     */
    public function getParameterNames();

    /**
     *
     * @param string $name
     * @param Object $value
     * @throws DOMException
     * @return void
     */
    public function setParameter($name, $value);

    /**
     *
     * @param string $name
     * @throws DOMException
     * @return Object
     */
    public function getParameter($name);

    /**
     *
     * @param string $name
     * @param Object $value
     * @return bool
     */
    public function canSetParameter($name, $value);
}