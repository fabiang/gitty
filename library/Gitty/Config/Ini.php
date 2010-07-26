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
 * @package  Ini
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Config/Ini
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
 * @category Gitty
 * @package  Ini
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Config/Ini
 */
class Ini extends G\Config
{
    /**
     * separator for nesting
     */
    protected $nestSeparator = '.';

    /**
     * process a config key
     *
     * @param Array  $config config as array
     * @param String $key    key
     * @param String $value  value
     *
     * @return Array processed config array
     */
    protected function processKey($config, $key, $value)
    {
        if (false !== \strpos($key, $this->nestSeparator)) {

            $pieces = explode($this->nestSeparator, $key, 2);

            if (\strlen($pieces[0]) && \strlen($pieces[1])) {

                if (!isset($config[$pieces[0]])) {
                    $config[$pieces[0]] = array();
                }

                $config[$pieces[0]] = $this->processKey(
                    $config[$pieces[0]],
                    $pieces[1],
                    $value
                );

            } else {
                include_once \dirname(__FILE__).'/Exception.php';
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
            include_once \dirname(__FILE__).'/Exception.php';
            throw new Exception("Config file does not exist: '$filename'");
        }

        if (!\is_readable($filename)) {
            include_once \dirname(__FILE__).'/Exception.php';
            throw new Exception("Config file isn't readable: '$filename'");
        }

        try {
            $iniArray = \parse_ini_file($filename, true);
        } catch(\Exception $e) {
            include_once \dirname(__FILE__).'/Exception.php';
            throw new Exception("Config could not be parsed: '$filename'");
        }

        $processArray = array();
        foreach ($iniArray as $sectionName => $sectionData) {

            $sub = array();
            foreach ($sectionData as $key => $value) {
                $sub = \array_merge_recursive(
                    $sub, $this->processKey(array(), $key, $value)
                );
            }
            $processArray[$sectionName] = $sub;

        }

        parent::__construct($processArray);
    }
}
