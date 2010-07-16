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
     * file mode for new files
     */
    public $mode = 0755;

    /**
     * copy a file
     *
     * @param String $sourceFile source file
     * @param String $destination_file desitnation
     */
    public function copy($sourceFile, $destination_file)
    {
        return \copy($source_file, $this->url . $destination_file, $this->_content);
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
        return \unlink($file, $this->_context);
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
     * make a directory
     *
     * @param String $dir directory name
     */
    public function makeDir($dir)
    {
        return \mkdir($dir, $this->mode, true, $this->_context);
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
     * initialize adapter
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
