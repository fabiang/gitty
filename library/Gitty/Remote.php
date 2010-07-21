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
    protected static $defaultadapter = null;

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
     * @todo make test better
     */
    public static function setDefaultAdapter($adapter)
    {
        if (!(new $adapter instanceof Remote\AdapterInterface)) {
            include_once \dirname(__FILE__).'/Remote/Exception.php';
            throw new Remote\Exception(
                get_class($data).' does not implement\
                Gitty\Remote\AdapterInterface interface'
            );
        }

        self::$defaultadapter = $adapter;
    }

    /**
     * initialize adapter
     *
     * @return Null
     */
    protected function init()
    {
        $this->adapter->init();
    }

    /**
     * get default adapter
     *
     * @return \Gitty\Remote\AdapterInterface the default adapter name
     */
    public static function getDefaultAdapter()
    {
        if (null === self::$defaultadapter) {
            self::$defaultadapter = __NAMESPACE__.'\\Remotes\\Adapter\\Ftp';
        }

        return self::getDefaultAdapter();
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
                $adapter = __NAMESPACE__.'\\Remote\\Adapter\\' .
                    ucfirst($config->adapter);
                Loader::loadClass($adapter);
            } catch(Exception $e) {
                include_once \dirname(__FILE__).'/Remote/Exception.php';
                throw new Remote\Exception("adapter '$adapter' is unknown");
            }

            $remote = new $adapter($config);

        } else {
            $adapter = self::getDefaultAdapter();
        }

        Loader::loadClass($adapter);
        $remote = new $adapter($config);

        $this->adapter = $remote;

        $this->init();
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
