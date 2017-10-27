<?php
/**
 * XPathResult
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-XPath/xpath.html#XPathResult
 */
namespace Slothsoft\PT;

use w3c\dom\XPathException;
use w3c\dom\DOMException;

class XPathResult implements \w3c\dom\XPathResult
{

    /*
     * const ANY_TYPE = 0;
     * const NUMBER_TYPE = 1;
     * const STRING_TYPE = 2;
     * const BOOLEAN_TYPE = 3;
     * const UNORDERED_NODE_ITERATOR_TYPE = 4;
     * const ORDERED_NODE_ITERATOR_TYPE = 5;
     * const UNORDERED_NODE_SNAPSHOT_TYPE = 6;
     * const ORDERED_NODE_SNAPSHOT_TYPE = 7;
     * const ANY_UNORDERED_NODE_TYPE = 8;
     * const FIRST_ORDERED_NODE_TYPE = 9;
     */
    
    /**
     *
     * @return int
     */
    public function getResultType()
    {}

    /**
     *
     * @return float
     */
    public function getNumberValue()
    {}

    /**
     *
     * @return string
     */
    public function getStringValue()
    {}

    /**
     *
     * @return bool
     */
    public function getBooleanValue()
    {}

    /**
     *
     * @return Node
     */
    public function getSingleNodeValue()
    {}

    /**
     *
     * @return bool
     */
    public function getInvalidIteratorState()
    {}

    /**
     *
     * @return int
     */
    public function getSnapshotLength()
    {}

    /**
     *
     * @throws XPathException
     * @throws DOMException
     * @return Node
     */
    public function iterateNext()
    {}

    /**
     *
     * @param int $index
     * @throws XPathException
     * @return Node
     */
    public function snapshotItem($index)
    {}
}