<?php
namespace Slothsoft\Savegame;

declare(ticks = 1000);

class EditorElement
{
    private $type;
    private $attributes;
    private $children;

    /**
     * @param string $type
     * @param array $attributes
     * @param array $children
     */
    public function __construct(string $type, array $attributes, array $children) {
        $this->type = $type;
        $this->attributes = $attributes;
        $this->children = $children;
    }
    /**
     * @param string $type
     * @param array $attributes
     * @return \Slothsoft\Savegame\EditorElement
     */
    public function clone(string $type = null, array $attributes = null, array $children = null) {
        return new EditorElement(
            $type === null ? $this->type : $type,
            $attributes === null ? $this->attributes : $attributes + $this->attributes,
            $children === null ? $this->children : $children
        );
    }
    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }
    
    /**
     * @param string $key
     * @return bool
     */
    public function hasAttribute(string $key) : bool {
        return isset($this->attributes[$key]);
    }
    
    /**
     * @param string $key
     * @return mixed
     */
    public function getAttribute(string $key) {
        return $this->attributes[$key] ?? null;
    }
    
    /**
     * @return array
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }
    
    /**
     * @return array
     */
    public function getChildren() : array
    {
        return $this->children;
    }
}