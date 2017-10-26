<?php
/**
 * Element
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#ID-745549614
 */
namespace w3c\dom;

interface Element extends Node
{

    /**
     *
     * @return string
     */
    public function getTagName();

    /**
     *
     * @return TypeInfo
     */
    public function getSchemaTypeInfo();

    /**
     *
     * @param string $name
     * @return string
     */
    public function getAttribute($name);

    /**
     *
     * @param string $name
     * @param string $value
     * @throws DOMException
     * @return void
     */
    public function setAttribute($name, $value);

    /**
     *
     * @param string $name
     * @throws DOMException
     * @return void
     */
    public function removeAttribute($name);

    /**
     *
     * @param string $name
     * @return Attr
     */
    public function getAttributeNode($name);

    /**
     *
     * @param Attr $newAttr
     * @throws DOMException
     * @return Attr
     */
    public function setAttributeNode(Attr $newAttr);

    /**
     *
     * @param Attr $oldAttr
     * @throws DOMException
     * @return Attr
     */
    public function removeAttributeNode(Attr $oldAttr);

    /**
     *
     * @param string $name
     * @return array
     */
    public function getElementsByTagName($name);

    /**
     *
     * @param string $namespaceURI
     * @param string $localName
     * @throws DOMException
     * @return string
     */
    public function getAttributeNS($namespaceURI, $localName);

    /**
     *
     * @param string $namespaceURI
     * @param string $qualifiedName
     * @param string $value
     * @throws DOMException
     * @return void
     */
    public function setAttributeNS($namespaceURI, $qualifiedName, $value);

    /**
     *
     * @param string $namespaceURI
     * @param string $localName
     * @throws DOMException
     * @return void
     */
    public function removeAttributeNS($namespaceURI, $localName);

    /**
     *
     * @param string $namespaceURI
     * @param string $localName
     * @throws DOMException
     * @return Attr
     */
    public function getAttributeNodeNS($namespaceURI, $localName);

    /**
     *
     * @param Attr $newAttr
     * @throws DOMException
     * @return Attr
     */
    public function setAttributeNodeNS(Attr $newAttr);

    /**
     *
     * @param string $namespaceURI
     * @param string $localName
     * @throws DOMException
     * @return array
     */
    public function getElementsByTagNameNS($namespaceURI, $localName);

    /**
     *
     * @param string $name
     * @return bool
     */
    public function hasAttribute($name);

    /**
     *
     * @param string $namespaceURI
     * @param string $localName
     * @throws DOMException
     * @return bool
     */
    public function hasAttributeNS($namespaceURI, $localName);

    /**
     *
     * @param string $name
     * @param bool $isId
     * @throws DOMException
     * @return void
     */
    public function setIdAttribute($name, $isId);

    /**
     *
     * @param string $namespaceURI
     * @param string $localName
     * @param bool $isId
     * @throws DOMException
     * @return void
     */
    public function setIdAttributeNS($namespaceURI, $localName, $isId);

    /**
     *
     * @param Attr $idAttr
     * @param bool $isId
     * @throws DOMException
     * @return void
     */
    public function setIdAttributeNode(Attr $idAttr, $isId);
}