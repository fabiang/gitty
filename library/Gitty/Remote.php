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
    protected static $_default_adapter = '\\Gitty\\Remotes\\Adapter\\Ftp';

    /**
     * instance of the adapter
     */
    protected $_adapter = null;

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
     * get default adapter
     *
     * @return \Gitty\Remote\AdapterInterface the default adapter name
     */
    public static function getDefaultAdapter()
    {
        return self::$_default_adapter;
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
                $adapter = '\\Gitty\\Remote\\Adapter\\' . ucfirst($config->adapter);
                Loader::loadClass($adapter);
            } catch(Exception $e) {
                require_once \dirname(__FILE__).'/Remote/Exception.php';
                throw new Remote\Exception("adapter '$adapter' is unknown");
            }

            $remote = new $adapter($config);

        } else {
            Loader::loadClass(self::$_default_adapter);
            $remote = new self::$_default_adapter($config);
        }

        $this->_adapter = $remote;

        $this->_init();
    }

    /**
     * initialize adapter
     */
    protected function _init()
    {
        $this->_adapter->init();
    }

    /**
     * copy a file
     *
     * @param String $file file name
     */
    public function copy($file)
    {
        $this->_adapter->copy($file);
    }

    /**
     * copy an array of files
     *
     * @param Array $files array of files
     */
    public function copyFiles($files)
    {
        $this->_adapter->copyFiles($files);
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
     * unlink an array for files
     *
     * @param Array $files array of file names
     */
    public function unlinkFiles($files)
    {
        $this->_adapter->unlinkFiles($files);
    }

    /**
     * make directory
     *
     * @param String $dir directory name
     */
    public function makeDir($dir)
    {
        $this->_adapter->makeDir($dir);
    }

    /**
     * make directories from array
     *
     * @param Array $dirs array with filenames
     */
    public function makeDirs($dirs)
    {
        $this->_adapter->madeDirs($dirs);
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
