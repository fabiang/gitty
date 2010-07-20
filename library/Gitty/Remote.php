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
 */

/**
 * @namespace Gitty
 */
namespace Gitty;

/**
 * remote class class
 *
 * @package Gitty
 * @license http://www.gnu.org/licenses/gpl.html
 */
class Remote
{
     /**
     * default adapter
     */
    protected static $_default_adapter = null;

    /**
     * instance of the adapter
     */
    protected $_adapter = null;

    /**
     * helper function to make default adapter namespace-aware
     */
    protected static function _getDefaultAdapter()
    {
        if (self::$_default_adapter === null) {
            self::$_default_adapter = __NAMESPACE__.'\\Remotes\\Adapter\\Ftp';
        }

        return self::$_default_adapter;
    }

    /**
     * set default adapter
     *
     * @param String $adapter an adapter that implements the AdapterInterface
     * @throws \Gitty\Remote\Exception if adapter is not an instance of \Gitty\Remote\AdapterInterface
     * @todo make test better
     */
    public static function setDefaultAdapter($adapter)
    {
        if (!(new $adapter instanceof Remote\AdapterInterface)) {
            require_once \dirname(__FILE__).'/Remote/Exception.php';
            throw new Remote\Exception(get_class($data).' does not implement Gitty\Remote\AdapterInterface interface');
        }

        self::$_default_adapter = $adapter;
    }

    /**
     * initialize adapter
     */
    protected function _init()
    {
        $this->_adapter->init();
    }

    /**
     * get default adapter
     *
     * @return \Gitty\Remote\AdapterInterface the default adapter name
     */
    public static function getDefaultAdapter()
    {
        return self::_getDefaultAdapter();
    }

    /**
     * constructor
     *
     * @param \Gitty\Config $config configartion object
     */
    public function __construct(Config $config)
    {
        if (isset($config->adapter)) {

            try {
                $adapter = __NAMESPACE__.'\\Remote\\Adapter\\' . ucfirst($config->adapter);
                Loader::loadClass($adapter);
            } catch(Exception $e) {
                require_once \dirname(__FILE__).'/Remote/Exception.php';
                throw new Remote\Exception("adapter '$adapter' is unknown");
            }

            $remote = new $adapter($config);

        } else {
            $adapter = self::_getDefaultAdapter();
        }

        Loader::loadClass($adapter);
        $remote = new $adapter($config);

        $this->_adapter = $remote;

        $this->_init();
    }

    /**
     * get revisition id of remote server
     *
     * @return String revisition file content
     */
    public function getServerRevisitionId()
    {
        return $this->_adapter->getServerRevisitionId();
    }

    /**
     * put revisition id in file on remote server
     *
     * @param String $uid revisition id
     */
    public function putServerRevisitionId($uid)
    {
        $this->_adapter->putServerRevisitionId($uid);
    }

    /**
     * put a file onto remote
     *
     * @param String $file file name
     */
    public function put($file, $destination)
    {
        $this->_adapter->put($file, $destination);
    }

    /**
     * copy a file
     *
     * @param String $file file name
     * @param String $destination destination
     */
    public function copy($file, $destination)
    {
        $this->_adapter->copy($file, $destination);
    }

    /**
     * unlink file
     *
     * @param String $file file name
     */
    public function unlink($file)
    {
        $this->_adapter->unlink($file);
    }

    /**
     * make directory
     *
     * @param String $dir directory name
     */
    /*public function makeDir($dir)
    {
        $this->_adapter->makeDir($dir);
    }*/

    /**
     * rename/move a file
     *
     * @param String $file file name
     * @param String $destination destination
     */
    public function rename($file, $destination)
    {
        $this->_adapter->rename($file, $destination);
    }

    public function cleanUp()
    {
        $this->_adapter->cleanUp();
    }

    /**
     * get adapter
     *
     * @return Gitty\Remote\AdapterAbstract remote adapter
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * string representation of the adapter
     *
     * @return String __toString from adapter
     */
    public function __toString()
    {
        return $this->_adapter->__toString();
    }
}
