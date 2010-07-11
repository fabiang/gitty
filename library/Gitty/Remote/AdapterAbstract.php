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
 */
abstract class AdapterAbstract
{
    protected $_context = null;
    protected $_url = null;
    public $mode = 0755;

    public function copy($sourceFile, $destination_file)
    {
        \copy($source_file, $this->url . $destination_file, $this->_content);
    }
    public function copyFiles($files)
    {
        foreach($files as $file) {
            $this->copy($file);
        }
    }

    public function unlink($file)
    {
        \unlink($file, $this->_context);
    }

    public function unlinkFiles($files)
    {
        foreach($files as $file) {
            $this->unlink($file);
        }
    }

    public function makeDir($dir)
    {
        \mkdir($dir, $this->mode, true, $this->_context);
    }
    public function makeDirs($dirs)
    {
        foreach($dirs as $dir) {
            $this->makeDir($dir);
        }
    }

    abstract public function init();
}
