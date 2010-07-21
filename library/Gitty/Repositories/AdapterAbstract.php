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
 * @package  AdapterAbstract
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Repositories/AdapterAbstract
 */

/**
 * @namespace Gitty\Repositories
 */
namespace Gitty\Repositories;

/**
 * storthands
 */
use \Gitty as G;

/**
 * Adapters need to implement this interface
 *
 * @category Gitty
 * @package  AdapterAbstract
 * @author   Fabian Grutschus <f.grutschus@lubyte.de>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     http://gitty.lubyte.de/docs/Gitty/Repositories/AdapterAbstract
 * @todo some getters and setters to access options
 */
abstract class AdapterAbstract
{
    /**
     * name of the project
     */
    protected $name = '';

    /**
     * description of the project
     */
    protected $description = '';

    /**
     * path to the project
     */
    protected $path = '';

    /**
     * show branches of this project
     */
    protected $showBranches = true;

    /**
     * registered remotes for this project (FTPs, SSHs etc)
     */
    protected $remoted = array();

    /**
     * the owner
     */
    protected $owner = null;

    /**
     * last change date
     */
    protected $lastChange = null;

    /**
     * branches
     */
    protected $branches = array();

    /**
     * the id of the newest revisition id
     */
    protected $newestRevisitionId = null;

    /**
     * rhe id of the oldest revisition id
     */
    protected $oldestRevisitionId = null;

    /**
     * modified files
     */
    protected $modified = array();

    /**
     * deleted files
     */
    protected $deleted = array();

    /**
     * copied files
     */
    protected $copied = array();

    /**
     * renamed files
     */
    protected $renamed = array();

    /**
     * added files
     */
    protected $added = array();

    /**
     * constructor
     *
     * @param Array $options project options
     */
    public function __construct($options)
    {
        // set configuration
        $this->setOptions($options);
        $this->showBranches = isset($options->showBranches)
                                    && !!$options->showBranches ? true : false;
    }

    /**
     * setter for configuration options
     *
     * @param String $name  the name of the option
     * @param Mixed  $value values of the option
     *
     * @return Null
     * @throws Gitty\Repositories\Exception
     */
    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || !\method_exists($this, $method)) {
            throw new Exception('Invalid property ' . $name);
        }
        $this->$method($value);
    }

    /**
     * getter for configuration options
     *
     * @param String $name the otion
     *
     * @return Mixed the configuration option value
     * @throws Gitty\Repositories\Exception
     */
    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !\method_exists($this, $method)) {
            throw new Exception('Invalid property ' . $name);
        }
        return $this->$method();
    }

    /**
     * sets configuration options
     *
     * @param Array $options configuration options
     *
     * @return \Gitty\Repositories\AdapterAbstract this class instance
     */
    public function setOptions($options)
    {
        $methods = \get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . \ucfirst($key);
            if (\in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * return configuration options
     *
     * @return Array configuration options
     */
    public function getOptions()
    {
        $instance_methods = \get_class_methods($this);
        $inherited_methods = \array_flip(\get_class_methods(__CLASS__));

        $options = array();

        foreach ($instance_methods as $methodname) {
            if ('get' === \substr($methodname, 0, 3)
                && !isset($inherited_methods[$methodname])
            ) {
                $columnname = \substr($methodname, 3);
                $columnname = \strtolower(
                    \substr($columnname, 0, 1)
                ) . \substr($columnname, 1);
                $options[$columnname] = $this->$methodname();
            }
        }

        return $options;
    }

    /**
     * alias for getOptions()
     *
     * @return Array configuration options
     */
    public function toArray()
    {
        return $this->getOptions();
    }

    /**
     * return the name of the adapter
     *
     * @return String the adapter name (Git, Svn etc)
     */
    public function getAdapterName()
    {
        $classname = \get_class($this);
        return \substr($classname, \strrpos($classname, '\\') + 1);
    }

    /**
     * return name of the project
     *
     * @return String project name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * set name of the project
     *
     * @param String $name project name
     *
     * @return Null
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * get description of project
     *
     * @return String description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * set description
     *
     * @param String $description description
     *
     * @return Null
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * get path
     *
     * @return String path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * set path
     *
     * @param String $path path
     *
     * @return Null
     */
    public function setPath($path)
    {
        if ('/' === \strlen($path) > 1 && \substr($path, -1)) {
            $path = \substr($path, 0, -1);
        }

        $this->path = $path;
    }

    /**
     * returns if showbranches should be visible
     *
     * @return Boolean show branches
     */
    public function showBranches()
    {
        return $this->showBranches;
    }

    /**
     * get remotes
     *
     * @return Array remotes
     */
    public function getRemotes()
    {
        return $this->remoted;
    }

    /**
     * register a remote
     *
     * @param \Gitty\Remote $remote a remote instance
     *
     * @return Null
     */
    public function registerRemote(G\Remote $remote)
    {
        $this->remoted[] = $remote;
    }

    /**
     * unregister a remote
     *
     * @param \Gitty\Remote $remote a remote instance
     *
     * @return Null
     */
    public function unregisterRemote(G\Remote $remote)
    {
        $index = \array_search($remote, $this->remoted, true);
        if (isset($this->remoted[$index])) {
            unset($this->remoted[$index]);
        }
        return !!$index;
    }

    /**
     * get owner of the project
     *
     * @return String the owner's name
     */
    abstract public function getOwner();

    /**
     * set owner of the project
     *
     * @param String $owner the owner's name
     *
     * @return Null
     */
    abstract public function setOwner($owner);

    /**
     * get last change date
     *
     * @return \DataTime datetime object with date/time
     */
    abstract public function getLastChange();

    /**
     * set last change date
     *
     * @param \DateTime $datetime a DateTime object
     *
     * @return Null
     */
    abstract public function setLastChange(\DateTime $datetime);

    /**
     * get branches
     *
     * @return Array branches
     */
    abstract public function getBranches();

    /**
     * set branches
     *
     * @param Array $branches branches
     *
     * @return Null
     */
    abstract public function setBranches($branches);

    /**
     * get updated files
     *
     * @param String $uid an unique id that represent the version
     *
     * @return Array updated files
     */
    abstract public function getUpdateFiles($uid);

    /**
     * get all files for installation
     *
     * @return Array the files
     */
    abstract public function getInstallFiles();

    /**
     * gets a file handle of a file from repository
     *
     * @param String $file file name
     *
     * @return Resource file hanlde
     * @return String   file source
     */
    abstract public function getFile($file);
}
