<?php
/**
 * @namespace Gitty\Config
 */
namespace Gitty\Config;

class Iterator implements \Iterator
{
    private $_array = array();
    private $_position = 0;

    public function __construct($obj)
    {
        $this->_array = $obj->toArray();
    }

    function rewind() {
        $this->position = 0;
    }

    function current() {
        return $this->array[$this->position];
    }

    function key() {
        return $this->position;
    }

    function next() {
        ++$this->position;
    }

    function valid() {
        return isset($this->array[$this->position]);
    }
}