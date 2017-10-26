<?php
namespace Slothsoft\Core\Lambda;

class Closure extends Stackable
{

    protected $_work;

    protected $_args;

    public function __construct($work, $args)
    {
        $this->_work = $work;
        $this->_args = $args;
    }

    public function work()
    {
        switch (true) {
            case is_string($this->_work):
                return $this->workString($this->_work, (array) $this->_args);
            case is_callable($this->_work):
                return $this->workCallable($this->_work, (array) $this->_args);
        }
    }

    protected function workString($work, array $args)
    {
        return eval($work);
    }

    protected function workCallable($work, array $args)
    {
        return call_user_func($work, $args);
    }
}