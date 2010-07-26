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
 * along with Gitty. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP Version 5.3
 *
 * @category Gitty
 * @package  Config
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Loader
 */

/**
 * @namespace Gitty
 */
namespace Gitty;


/**
 * Loader class for Gitty
 *
 * @category Gitty
 * @package  Config
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Loader
 */
class Loader
{
    /**
     * namespace separator \ since PHP 5.3
     */
    const NAMESPACE_SEPARATOR = '\\';

    /**
     * loading a file by its class name
     *
     * @param String       $class Class name
     * @param String|Array $dirs  Directories for file lookup
     *
     * @return Null
     * @throws Gitty\Exception
     */
    public static function loadClass($class, $dirs = null)
    {
        if (\class_exists($class, false) || \interface_exists($class, false)) {
            return;
        }

        if ((null !== $dirs) && !\is_string($dirs) && !\is_array($dirs)) {
            include_once \dirname(__FILE__).'/Exception.php';
            throw new Exception('Directory argument must be a string or an array');
        }

        // autodiscover the path from the class name
        $file = \str_replace(
            self::NAMESPACE_SEPARATOR,
            \DIRECTORY_SEPARATOR,
            $class
        ) . '.php';

        // if class name begins with \ strip it
        if ('/' === $file[0]) {
            $file = \substr($file, 1);
        }

        if (!empty($dirs)) {
            // use the autodiscovered path
            $dirPath = \dirname($file);
            if (\is_string($dirs)) {
                $dirs = \explode(\PATH_SEPARATOR, $dirs);
            }

            foreach ($dirs as $key => $dir) {
                if ($dir == '.') {
                    $dirs[$key] = $dirPath;
                } else {
                    $dir = \rtrim($dir, '\\/');
                    $dirs[$key] = $dir . \DIRECTORY_SEPARATOR . $dirPath;
                }
            }
            $file = \basename($file);
            self::loadFile($file, $dirs, true);
        } else {
            self::securityCheck($file);
            include_once $file;
        }

        if (!\class_exists($class, false) && !\interface_exists($class, false)) {
            include_once \dirname(__FILE__).'/Exception.php';
            throw new Exception(
                "File \"$file\" does not exist or class\
                \"$class\" was not found in the file"
            );
        }
    }

    /**
     * loads a file
     *
     * @param String  $filename Filename
     * @param Array   $dirs     Directories for file lookup
     * @param Boolean $once     include the file only one time
     *
     * @return Null
     */
    public static function loadFile($filename, $dirs = null, $once = false)
    {
        self::securityCheck($filename);

        /**
         * Search in provided directories, as well as include_path
         */
        $incPath = false;
        if (!empty($dirs) && (\is_array($dirs) || \is_string($dirs))) {
            if (\is_array($dirs)) {
                $dirs = \implode(\PATH_SEPARATOR, $dirs);
            }
            $incPath = \get_include_path();
            \set_include_path($dirs . \PATH_SEPARATOR . $incPath);
        }

        /**
         * Try finding for the plain filename in the include_path.
         */
        if ($once) {
            include_once $filename;
        } else {
            include $filename;
        }

        /**
         * If searching in directories, reset include_path
         */
        if ($incPath) {
            \set_include_path($incPath);
        }

        return true;
    }

    /**
     * the autoloader function
     *
     * @param String $class Class name
     *
     * @return Object Instance when class can be loaded
     * @return Boolean false if error is thrown
     */
    public static function autoload($class)
    {
        try {
            self::loadClass($class);
            return $class;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * register this class to PHPs autoloader
     *
     * @param String  $class   The name of the autoloading class
     * @param Boolean $enabled autoloading enabled/disabled
     *
     * @return Null
     * @throws Gitty\Exception
     */
    public static function registerAutoload($class = null, $enabled = true)
    {
        // if no class is specified use this class
        if (null === $class) {
            $class = __NAMESPACE__.'\\Loader';
        }

        if (!\function_exists('spl_autoload_register')) {
            include_once \dirname(__FILE__).'/Exception.php';
            throw new Exception(
                'spl_autoload does not exist in this PHP installation'
            );
        }

        self::loadClass($class);
        $methods = \get_class_methods($class);
        if (!\in_array('autoload', (array)$methods)) {
            include_once \dirname(__FILE__).'/Exception.php';
            throw new Exception(
                "The class \"$class\" does not have an autoload() method"
            );
        }

        if (true === $enabled) {
            \spl_autoload_register(array($class, 'autoload'));
        } else {
            \spl_autoload_unregister(array($class, 'autoload'));
        }
    }

    /**
     * allow constructing this class
     *
     * @param Boolean $autoloading autoloading enabled/disabled
     */
    public function __construct($autoloading = true)
    {
        $this->registerAutoload(null, $autoloading);
    }

    /**
     * check for invalid filename charackters
     *
     * @param String $filename the filename
     *
     * @return Null
     * @throws Gitty\Exception
     */
    protected static function securityCheck($filename)
    {
        /**
         * Security check
         */
        if (\preg_match('/[^a-z0-9\\/\\\\_.-]/i', $filename)) {
            include_once \dirname(__FILE__).'/Exception.php';
            throw new Exception('Security check: Illegal character in filename');
        }
    }
}
