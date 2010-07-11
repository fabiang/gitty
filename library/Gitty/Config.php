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
 * @namespace Gitty
 */
namespace Gitty;
use \Gitty\Config as C;

/**
 * config class
 *
 * @package Gitty
 * @license http://www.gnu.org/licenses/gpl.html
 */
class Config implements \IteratorAggregate, \ArrayAccess
{
    /**
     * config file data as array
     */
    protected $_data = array();

    /**
     * allow modifications of the class
     */
    protected $_allowModifications = true;

    /**
     * current count of data entries
     */
    protected $_count = 0;

    /**
     * default config data
     */
    public static $defaultConfig = array(
        'global' => array(
            'git' => array(
                'defaultGitDir' => '.git',
                'descriptionFile' => 'description',
                'binLocation'=> '/usr/bin/git'
            ),
            'gitty' => array(
                'readDirectories' => '0',
                'dateFormat' => 'Y-m-d H:i:s',
                'revistionFile' => 'revision.txt',
                'tempDir' => '/tmp'
            )
        )
    );

    /**
     * constructor
     *
     * @param String $filename path to config file
     * @throws Gitty\Exception
     */
    public function __construct($data, $allowModifications = true)
    {
        if (\is_object($data)) {
            if (!($data instanceof C\Loader)) {
                require_once dirname(__FILE__).'/Config/Exception.php';
                throw new C\Exception(get_class($data).' does not implement Gitty\Config\ConfigLoader interface');
            }

            $data = $data->toArray();
            $data = array_merge_recursive(self::$defaultConfig, $data);
        }

        if (!\is_array($data)) {
            require_once dirname(__FILE__).'/Config/Exception.php';
            throw new C\Exception('first parameter has to be array');
        }

        foreach($data as $name => $value) {
            if (\is_array($value)) {
                $this->_data[$name] = new self($value, true);
            } else {
                $this->_data[$name] = $value;
            }
            $this->_count = \count($this->_data);
        }

        $this->_allowModifications = $allowModifications;
    }

    /**
     * support for <code>isset($config->option)</code>
     *
     * @param String $name
     */
    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    /**
     * support for <code>unset($config->option)</code>
     *
     * @param String $name
     */
    public function __unset($name)
    {
        if ($this->_allowModifications) {
            unset($this->_data[$name]);
            $this->_count = \count($this->_data);
        } else {
            require_once dirname(__FILE__).'/Config/Exception.php';
            throw new C\Exception('Gitty\Config is read only');
        }
    }

    /**
     * support for <code>$option = $config->option</code>
     *
     * @param String $name
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * support for <code>$config->option = "foo"</code>
     *
     * @param String $name key name
     * @param String $value value of key
     */
    public function __set($name, $value)
    {
        if ($this->_allowModifications) {
            if (\is_array($value)) {
                $this->_data[$name] = new self($value, true);
            } else {
                $this->_data[$name] = $value;
            }
            $this->_count = \count($this->_data);
        } else {
            require_once dirname(__FILE__).'/Config/Exception.php';
            throw new C\Exception('Gitty\Config is read only');
        }
    }

    /**
     * get an option or return defined default
     *
     * @param String $name the key name
     * @param Mixed $default use this value if key doesn't exists
     * @return Mixed the value or default
     */
    public function get($name, $default = null)
    {
        $result = $default;
        if (\array_key_exists($name, $this->_data)) {
            $result = $this->_data[$name];
        }
        return $result;
    }

    /**
     * return config as array
     *
     * @return Array config array
     */
    public function toArray()
    {
        $array = array();
        foreach ($this->_data as $key => $value) {
            if ($value instanceof Config) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $value;
            }
        }
        return $array;
    }

    /**
     * get iterator
     *
     * @return Iterator Iterator class
     */
    public function getIterator() {
        return new \ArrayObject($this->_data);
    }

    /**
     * ArrayAccess: set
     *
     * @param Mixed $offset the offset
     * @param Mixed $value the value
     */
    public function offsetSet($offset, $value) {
        $this->_data[$offset] = $value;
    }

    /**
     * ArrayAccess: test if offset exists
     *
     * @param Mixed $offset the offset
     * @return Boolean true if offset exists
     */
    public function offsetExists($offset) {
        return isset($this->_data[$offset]);
    }

    /**
     * ArrayAccess: unset()
     *
     * @param Mixed $offset the offset
     */
    public function offsetUnset($offset) {
        unset($this->_data[$offset]);
    }

    /**
     * ArrayAccess: get data
     *
     * @param Mixed $offset the offset
     * @return Mixed|Null returns the data if offset exists, otherwise null
     */
    public function offsetGet($offset) {
        return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
    }
}