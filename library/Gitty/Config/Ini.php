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
 * short hands
 */
use \Gitty as G;

/**
 * helper class for loading ini conigurations files
 *
 * @package Gitty
 * @license http://www.gnu.org/licenses/gpl.html
 */
class Ini extends G\Config
{
    /**
     * separator for nesting
     */
    protected $_nestSeparator = '.';

    /**
     * process a config key
     *
     * @param Array $config config as array
     * @param String $key key
     * @param String $value value
     * @return Array processed config array
     */
    protected function _processKey($config, $key, $value)
    {
        if (\strpos($key, $this->_nestSeparator) !== false) {

            $pieces = explode($this->_nestSeparator, $key, 2);

            if (\strlen($pieces[0]) && \strlen($pieces[1])) {

                if (!isset($config[$pieces[0]])) {

                    $config[$pieces[0]] = array();

                } elseif (!\is_array($config[$pieces[0]])) {
                    require_once \dirname(__FILE__).'/Exception.php';
                    throw new Exception("Cannot create sub-key for '{$pieces[0]}' as key already exists");
                }
                $config[$pieces[0]] = $this->_processKey($config[$pieces[0]], $pieces[1], $value);

            } else {
                require_once \dirname(__FILE__).'/Exception.php';
                throw new Exception("Invalid key '$key'");
            }
        } else {
            $config[$key] = $value;
        }
        return $config;
    }

    /**
     * class constructor
     *
     * @param String $filename the filepath to the ini file
     */
    public function __construct($filename)
    {
        if (!\file_exists($filename)) {
            require_once \dirname(__FILE__).'/Exception.php';
            throw new Exception("Config file does not exist: '$filename'");
        }

        if (!\is_readable($filename)) {
            require_once \dirname(__FILE__).'/Exception.php';
            throw new Exception("Config file isn't readable: '$filename'");
        }

        try {
            $iniArray = \parse_ini_file($filename, true);
        } catch(\Exception $e) {
            require_once \dirname(__FILE__).'/Exception.php';
            throw new Exception("Config could not be parsed: '$filename'");
        }

        $processArray = array();
        foreach($iniArray as $sectionName => $sectionData) {

            $sub = array();
            foreach ($sectionData as $key => $value) {
                $sub = \array_merge_recursive($sub, $this->_processKey(array(), $key, $value));
            }
            $processArray[$sectionName] = $sub;

        }

        parent::__construct($processArray);
    }
}
