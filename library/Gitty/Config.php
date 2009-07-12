<?php
/**
 * Gitty - web deployment tool
 * Copyright (C) 2009 Fabian Grutschus
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Gitty.  If not, see <http://www.gnu.org/licenses/>.
 */
class Gitty_Config
{
    protected $_data = array();
    protected $_nestSeparator = '.';

    private function array_merge_recusive ()
    {
        $aArrays = func_get_args();
        $aMerged = $aArrays[0];

        for($i = 1; $i < count($aArrays); $i++) {

            if (is_array($aArrays[$i])) {

                foreach ($aArrays[$i] as $key => $val) {

                    if (!isset($aMerged[$key])) {
                        $aMerged[$key] = $val;
                    } elseif (is_array($aArrays[$i][$key])) {
                        $aMerged[$key] = is_array($aMerged[$key]) ? $this->array_merge_recusive($aMerged[$key], $aArrays[$i][$key]) : $aArrays[$i][$key];
                    } else {
                        $aMerged[$key] = $val;
                    }

                }

            }

        }

        return $aMerged;
    }

    public function __construct($filename)
    {
        if (!file_exists($filename)) {
            require_once 'Gitty/Exception.php';
            new Gitty_Exception('Config file does not exist');
        }

        if (!is_readable($filename)) {
            require_once 'Gitty/Exception.php';
            new Gitty_Exception('Config file isn\'t readable');
        }

        try {
            $iniArray = parse_ini_file($filename, true);
        } catch(Exception $e) {
            require_once 'Gitty/Exception.php';
            new Gitty_Exception('Config could not be parsed');
        }

        $processArray = array();
        foreach($iniArray as $sectionName => $sectionData) {
            $sub = array();
            foreach ($sectionData as $key => $value) {
                $sub = array_merge_recursive($sub, $this->_processKey(array(), $key, $value));
            }
            $processArray[$sectionName] = $sub;
        }

        $processArray = $this->array_merge_recusive(Gitty_Config_Default::$DEFAULT, $processArray);

        $this->_data = $processArray;
    }

    protected function _processKey($config, $key, $value)
    {
        if (strpos($key, $this->_nestSeparator) !== false) {
            $pieces = explode($this->_nestSeparator, $key, 2);
            if (strlen($pieces[0]) && strlen($pieces[1])) {
                if (!isset($config[$pieces[0]])) {
                    $config[$pieces[0]] = array();
                } elseif (!is_array($config[$pieces[0]])) {
                    require_once 'Gitty/Config/Exception.php';
                    throw new Gitty_Config_Exception("Cannot create sub-key for '{$pieces[0]}' as key already exists");
                }
                $config[$pieces[0]] = $this->_processKey($config[$pieces[0]], $pieces[1], $value);
            } else {
                require_once 'Gitty/Config/Exception.php';
                throw new Gitty_Config_Exception("Invalid key '$key'");
            }
        } else {
            $config[$key] = $value;
        }
        return $config;
    }

    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    public function __unset($name)
    {
        if ($this->_allowModifications) {
            unset($this->_data[$name]);
            $this->_count = count($this->_data);
        } else {
            require_once 'Gitty/Config/Exception.php';
            throw new Gitty_Config_Exception('Gitty_Config is read only');
        }
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        if ($this->_allowModifications) {
            if (is_array($value)) {
                $this->_data[$name] = new self($value, true);
            } else {
                $this->_data[$name] = $value;
            }
            $this->_count = count($this->_data);
        } else {
            require_once 'Gitty/Config/Exception.php';
            throw new Gitty_Config_Exception('Gitty_Config is read only');
        }
    }

    public function get($name, $default = null)
    {
        $result = $default;
        if (array_key_exists($name, $this->_data)) {
            $result = $this->_data[$name];
        }
        return $result;
    }

    public function toArray()
    {
        $array = array();
        foreach ($this->_data as $key => $value) {
            if ($value instanceof Gitty_Config) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $value;
            }
        }
        return $array;
    }
}