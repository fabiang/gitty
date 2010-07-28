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
 *
 * PHP Version 5.3
 *
 * @category Gitty
 * @package  Config
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Config
 */

/**
 * @namespace Gitty
 */
namespace Gitty;

/**
 * short hands
 */
use \Gitty\Config as C;

/**
 * config class
 *
 * @category Gitty
 * @package  Config
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/version
 */
class Config implements \IteratorAggregate, \ArrayAccess
{
    /**
     * config file data as array
     */
    protected $data = array();

    /**
     * allow modifications of the class
     */
    protected $allowModifications = true;

    /**
     * current count of data entries
     */
    protected $count = 0;

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
     * Merge defaultConfig and configuration together
     * in a distinct was
     *
     * @param Mixed $base   default config
     * @param Array $append configuration
     *
     * @return array Resulting array, once all have been merged
     */
    private function _mergeConfiguration($base, $append)
    {
        if (!\is_array($base)) {
            $base = empty($base) ? array() : array($base);
        }

        foreach ($append as $key => $value) {
            if (!\array_key_exists($key, $base) and !\is_numeric($key)) {
                $base[$key] = $append[$key];
                continue;
            }

            if (\is_array($value) or isset($base[$key]) and \is_array($base[$key])) {
                // modified from original
                $f = __FUNCTION__;
                $base[$key] = $this->$f(
                    $base[$key],
                    $append[$key]
                );
            } else {
                $base[$key] = $value;
            }
        }
        return $base;
    }

    /**
     * constructor
     *
     * @param Array   $data               config array
     * @param Boolean $allowModifications allow modifications of the object
     * @param Boolean $merge              merge default data to the array
     *
     * @throws Gitty\Exception
     */
    public function __construct($data, $allowModifications = true, $merge = true)
    {
        if (\is_object($data)) {
            if (!($data instanceof self)) {
                include_once \dirname(__FILE__).'/Config/Exception.php';
                throw new C\Exception(
                    get_class($data).' does not implement\
                    Gitty\Config\ConfigLoader interface'
                );
            }

            $data = $data->toArray();
        }

        if (!\is_array($data)) {
            include_once \dirname(__FILE__).'/Config/Exception.php';
            throw new C\Exception('first parameter has to be array');
        }

        // merge default data
        if (true === $merge) {
            $data = $this->_mergeConfiguration(self::$defaultConfig, $data);
        }

        foreach ($data as $name => $value) {
            if (\is_array($value)) {
                $this->data[$name] = new self($value, $allowModifications, false);
            } else {
                $this->data[$name] = $value;
            }
            $this->count = \count($this->data);
        }

        $this->allowModifications = $allowModifications;
    }

    /**
     * support for <code>isset($config->option)</code>
     *
     * @param String $name name of option
     *
     * @return Boolean true if option isset
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * support for <code>unset($config->option)</code>
     *
     * @param String $name name of option
     *
     * @return Null
     */
    public function __unset($name)
    {
        if ($this->allowModifications) {
            unset($this->data[$name]);
            $this->count = \count($this->data);
        } else {
            include_once \dirname(__FILE__).'/Config/Exception.php';
            throw new C\Exception('Gitty\Config is read only');
        }
    }

    /**
     * support for <code>$option = $config->option</code>
     *
     * @param String $name name of option
     *
     * @return Mixed option value
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * support for <code>$config->option = "foo"</code>
     *
     * @param String $name  key name
     * @param String $value value of key
     *
     * @return Null
     */
    public function __set($name, $value)
    {
        if ($this->allowModifications) {
            if (\is_array($value)) {
                $this->data[$name] = new self($value, true, false);
            } else {
                $this->data[$name] = $value;
            }
            $this->count = \count($this->data);
        } else {
            include_once \dirname(__FILE__).'/Config/Exception.php';
            throw new C\Exception('Gitty\Config is read only');
        }
    }

    /**
     * get an option or return defined default
     *
     * @param String $name    the key name
     * @param Mixed  $default use this value if key doesn't exists
     *
     * @return Mixed the value or default
     */
    public function get($name, $default = null)
    {
        $result = $default;
        if (\array_key_exists($name, $this->data)) {
            $result = $this->data[$name];
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
        foreach ($this->data as $key => $value) {
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
     * @return ArrayObject ArrayObject holding the data
     */
    public function getIterator()
    {
        return new \ArrayObject($this->data);
    }

    /**
     * ArrayAccess: set
     *
     * @param Mixed $offset the offset
     * @param Mixed $value  the value
     *
     * @return Null
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * ArrayAccess: test if offset exists
     *
     * @param Mixed $offset the offset
     *
     * @return Boolean true if offset exists
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * ArrayAccess: unset()
     *
     * @param Mixed $offset the offset
     *
     * @return Null
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * ArrayAccess: get data
     *
     * @param Mixed $offset the offset
     *
     * @return Mixed|Null returns the data if offset exists, otherwise null
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
}
