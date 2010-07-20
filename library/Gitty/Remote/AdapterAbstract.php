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
namespace Gitty\Remote;

/**
 * repository class
 *
 * @package Gitty
 * @license http://www.gnu.org/licenses/gpl.html
 * @todo implement some getter and setters
 */
abstract class AdapterAbstract
{
    /**
     * stream context
     */
    protected $_context = null;

    /**
     * adapter url
     */
    protected $_url = null;

    /**
     * the revisition file name
     */
    public $_revisitionFileName = 'revisition.txt';

    /**
     * file mode for new files
     */
    public $mode = 0755;

    /**
     * return the name of the adapter
     *
     * @return String the adapter name (Ftp, File, Sftp etc.)
     */
    public function getAdapterName()
    {
        $class_name = \get_class($this);
        return \substr($class_name, \strrpos($class_name, '\\') + 1);
    }

    /**
     * get revisition id of remote server
     *
     * @return String revisition file content
     */
    abstract public function getServerRevisitionId();

    /**
     * put revisition id in file on remote server
     *
     * @param String $uid revisition id
     */
    abstract public function putServerRevisitionId($uid);

    /**
     * adapter must have a copy function
     */
    abstract public function put($file, $destination);

    /**
     * adapter must have a rename function
     */
    abstract public function rename($source, $destination);

    /**
     * adapter must have an unlink function
     */
    abstract public function unlink($file, $remove_empty_directories = true);

    /**
     * adapter must have a copy function
     */
    abstract public function copy($source, $destination);

    /**
     * adapter must implement init
     *
     * @abstract
     */
    abstract public function init();

    /**
     * adapter must have cleanUp function
     */
    abstract public function cleanUp();

    /**
     * adapter must implement __toString
     *
     * @abstract
     */
    abstract public function __toString();
}
