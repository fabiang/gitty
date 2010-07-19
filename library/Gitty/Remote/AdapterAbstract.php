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
     * get revisition id of remote server
     *
     * @return String revisition file content
     */
    public function getServerRevisitionId()
    {
        return \trim(\file_get_contents($this->_url . '/' . $this->_revisitionFileName, false, $this->_context));
    }

    /**
     * put revisition id in file on remote server
     *
     * @param String $uid revisition id
     */
    public function putServerRevisitionId($uid)
    {
        \file_put_contents($this->_url . '/' . $this->_revisitionFileName, \trim($uid), 0, $this->_context);
    }

    /**
     * copy a file
     *
     * @param String $sourceFile source file
     * @param String $destination_file desitnation
     */
    public function copy($source_file, $destination_file = null)
    {
        if (\is_array($source_file) && $destination_file === null) {
            $this->copyFiles($source_file);
            return;
        }

        $dirname = \dirname($destination_file);
        if (!\is_dir($this->_url . '/' . $dirname)) {
            $this->makeDir($dirname);
        }

        if (\is_file($this->_url . '/' . $destination_file)) {
            $this->unlink($destination_file);
        }

        return \copy($source_file, $this->_url . '/' . $destination_file, $this->_context);
    }

    /**
     * copy an array of files
     *
     * @param Array $files array of files: array('/source/foo' => '/destination/file.txt')
     */
    public function copyFiles($files)
    {
        foreach($files as $source => $destination) {
            $this->copy($source, $destination);
        }
    }

    /**
     * unlink a file
     *
     * @param String $file a file
     */
    public function unlink($file)
    {
        if (\is_array($file)) {
            $this->unlinkFiles($source_file);
            return;
        }

        return \unlink($this->_url . '/' .$file, $this->_context);
    }

    /**
     * unlink an array of files
     *
     * @param Array $files array of files
     */
    public function unlinkFiles($files)
    {
        foreach($files as $file) {
            $this->unlink($file);
        }
    }

    /**
     * rename/move a file
     *
     * @param String $file file name
     * @param String $destination the destination
     */
    public function rename($file, $destination = null)
    {
        if (\is_array($file) && $destination === null) {
            $this->renameFiles($source_file);
            return;
        }

        return \rename($file, $destination, $this->_context);
    }

    /**
     * rename/move an array of files
     *
     * @param Array $files an array of files
     */
    public function renameFiles($files)
    {
        foreach($files as $file => $destination) {
            $this->rename($file, $destination);
        }
    }

    /**
     * make a directory
     *
     * @param String $dir directory name
     */
    public function makeDir($dir)
    {
        if (\is_array($dir)) {
            $this->makeDirs($dir);
            return;
        }

        return \mkdir($this->_url . '/' . $dir, $this->mode, true, $this->_context);
    }

    /**
     * make directories from array
     *
     * @param Array $dirs array of directories
     */
    public function makeDirs($dirs)
    {
        foreach($dirs as $dir) {
            $this->makeDir($dir);
        }
    }

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
     * adapter must implement init
     *
     * @abstract
     */
    abstract public function init();

    /**
     * adapter must implement __toString
     *
     * @abstract
     */
    abstract public function __toString();
}
