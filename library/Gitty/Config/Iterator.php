<?php
/**
 * Gitty - web deployment tool
 * Copyright (C) 2010 Fabian Grutschus
 *
 * This file is part of Gitty.
 *
 * Gitty is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Gitty is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Gitty.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @namespace Gitty\Config
 */
namespace Gitty\Config;

/**
 * iterator class used by Gitty\Config
 *
 * @package Gitty
 * @license http://www.gnu.org/licenses/gpl.html
 */
class Iterator implements \Iterator
{
    /**
     * the current config data of Gitty\Config
     */
    private $_array = array();

    /**
     * the current position
     */
    private $_position = 0;

    /**
     * constructor class
     *
     * @param Gitty\Config $obj a Gitty\Config object
     */
    public function __construct(\Gitty\Config $obj)
    {
        $this->_array = $obj->toArray();
    }

    /**
     * reset position
     */
    function rewind() {
        $this->position = 0;
    }

    /**
     * get data from array at current position
     *
     * @return Mixed data from array
     */
    function current() {
        return $this->array[$this->position];
    }

    /**
     * get current position
     *
     * @return Integer current position
     */
    function key() {
        return $this->position;
    }

    /**
     * seek to next position
     */
    function next() {
        ++$this->position;
    }

    /**
     * check if current position in array exists
     *
     * @retrun Boolean true if position is valid
     */
    function valid() {
        return isset($this->array[$this->position]);
    }
}