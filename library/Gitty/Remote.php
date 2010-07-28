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
 * @package  Remote
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Remote
 */

/**
 * @namespace Gitty
 */
namespace Gitty;

/**
 * remote class class
 *
 * @category Gitty
 * @package  Remote
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Remote
 */
class Remote
{
     /**
     * default adapter
     */
    protected static $defaultAdapter = null;

    /**
     *
     */
    protected static $registeredAdapterNamespaces = array();

    /**
     * instance of the adapter
     */
    protected $adapter = null;

    /**
     * set default adapter
     *
     * @param String $adapter an adapter that implements the AdapterInterface
     *
     * @return Null
     * @throws \Gitty\Remote\Exception adapter is not \Gitty\Remote\AdapterInterface
     * @todo create test for valid adapter
     */
    public static function setDefaultAdapter($adapter)
    {
        self::$defaultAdapter = $adapter;
    }

    /**
     * get default adapter
     *
     * @return \Gitty\Remote\AdapterInterface the default adapter name
     */
    public static function getDefaultAdapter()
    {
        if (null === self::$defaultAdapter) {
            self::$defaultAdapter = __NAMESPACE__.'\\Remote\\Adapter\\Ftp';
        }

        return self::$defaultAdapter;
    }

    /**
     * register a namespace for remotes
     *
     * @param String $namespace namespace name
     *
     * @return Boolean false when already registered, overwise true
     */
    public static function registerAdapterNamespace($adapter)
    {
        if (!\in_array($adapter, self::$registeredAdapterNamespaces)) {
            self::$registeredAdapterNamespaces[] = $adapter;
            return true;
        }

        return false;
    }

    /**
     * unregsiter a namespace for remotes
     *
     * @param String $namespace namespace name
     *
     * @return Boolean ture when removed, overwise false
     */
    public static function unregisterAdapterNamespace($adapter)
    {
        $index = \array_search($adapter, self::$registeredAdapterNamespaces);
        if (false !== $index) {
            unset(self::$registeredAdapterNamespaces[$index]);
            return true;
        }

        return false;
    }

    /**
     * returns all registered namespaces
     *
     * @return Array list of namespaces
     */
    public static function getAdapterNamespaces()
    {
        return self::$registeredAdapterNamespaces;
    }

    /**
     * constructor
     *
     * @param \Gitty\Config $config configartion object
     */
    public function __construct(Config $config)
    {
        if (isset($config->adapter)) {
            $adapter = __NAMESPACE__.'\\Remote\\Adapter\\'.\ucfirst($config->adapter);

            // first try loading any of the own adapters
            try {
                Loader::loadClass($adapter);
            } catch(Exception $e) {
                // throw exception when there are no registered namespaces
                if (0 === \count(self::getAdapterNamespaces())) {
                    include_once \dirname(__FILE__).'/Remote/Exception.php';
                    throw new Remote\Exception(
                        "can't load adapter '{$config->adapter}' and no namespaces \
                        registered for lookup"
                    );
                }

                // try loading the adapter from the registered namespaces
                $found = false;
                $search = 0;
                foreach(self::getAdapterNamespaces() as $namespace) {
                    try {
                        $class = $namespace.'\\'.\ucfirst($config->adapter);
                        Loader::loadClass($class);
                        $adapter = $class;
                        $found = true;
                    } catch (Exception $e) {
                        $search++;
                    }
                }

                // didn't find the adapter in any registered namespace
                // throw exception
                if (false === $found) {
                    include_once \dirname(__FILE__).'/Remote/Exception.php';
                    throw new Remote\Exception(
                        "can't load any adapter of the name {$config->adapter}. \
                        searched in $search namespaces: " .
                        \implode(\PATH_SEPARATOR, self::getAdapterNamespaces())
                    );
                }
            }
        } else {
            $adapter = self::getDefaultAdapter();

            try {
                Loader::loadClass($adapter);
            } catch(Exception $e) {
                include_once \dirname(__FILE__).'/Remote/Exception.php';
                throw new Remote\Exception(
                    "default adapter '$adapter' can't be found"
                );
            }
        }

        $remote = new $adapter($config);

        if (!($remote instanceof Remote\AdapterAbstract)) {
            include_once \dirname(__FILE__).'/Remote/Exception.php';
            throw new Remote\Exception(
                "invalid: adapter '$adapter' does not implement AdapterAbstract"
            );
        }

        $this->adapter = $remote;
    }

    /**
     * get revisition id of remote server
     *
     * @return String revisition file content
     */
    public function getServerRevisitionId()
    {
        return $this->adapter->getServerRevisitionId();
    }

    /**
     * put revisition id in file on remote server
     *
     * @param String $uid revisition id
     *
     * @return Null
     */
    public function putServerRevisitionId($uid)
    {
        $this->adapter->putServerRevisitionId($uid);
    }

    /**
     * initialize adapter
     *
     * @return Null
     */
    public function init()
    {
        $this->adapter->init();
    }

    /**
     * put a file onto remote
     *
     * @param String $file        file name
     * @param String $destination deistnation
     *
     * @return Null
     */
    public function put($file, $destination)
    {
        $this->adapter->put($file, $destination);
    }

    /**
     * copy a file
     *
     * @param String $file        file name
     * @param String $destination destination
     *
     * @return Null
     */
    public function copy($file, $destination)
    {
        $this->adapter->copy($file, $destination);
    }

    /**
     * unlink file
     *
     * @param String $file file name
     *
     * @return Null
     */
    public function unlink($file)
    {
        $this->adapter->unlink($file);
    }

    /**
     * make directory
     *
     * @param String $dir directory name
     */
    /*public function makeDir($dir)
    {
        $this->adapter->makeDir($dir);
    }*/

    /**
     * rename/move a file
     *
     * @param String $file        file name
     * @param String $destination destination
     *
     * @return Null
     */
    public function rename($file, $destination)
    {
        $this->adapter->rename($file, $destination);
    }

    /**
     * do some cleanup in the adapter
     *
     * @return Null
     */
    public function cleanUp()
    {
        $this->adapter->cleanUp();
    }

    /**
     * get adapter
     *
     * @return Gitty\Remote\AdapterAbstract remote adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * string representation of the adapter
     *
     * @return String __toString from adapter
     */
    public function __toString()
    {
        return $this->adapter->__toString();
    }
}
