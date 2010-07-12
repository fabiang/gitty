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
 * @namespace Gitty\Repositories
 */
namespace Gitty\Repositories;

/**
 * Adapters need to implement this interface
 *
 * @package Gitty
 * @license http://www.gnu.org/licenses/gpl.html
 */
abstract class AdapterAbstract
{
    /**
     * name of the project
     */
    protected $_name = '';

    /**
     * description of the project
     */
    protected $_description = null;

    /**
     * path to the project
     */
    protected $_path = '';

    /**
     * show branches of this project
     */
    protected $_showBranches = true;

    /**
     * registered remotes for this project (FTPs, SSHs etc)
     */
    protected $_remotes = array();

    /**
     * the owner
     */
    protected $_owner = null;

    /**
     * last change date
     */
    protected $_lastChange = null;

    /**
     * branches
     */
    protected $_branches = array();

    /**
     * constructor
     *
     * @param Array $options project options
     */
    public function __construct($options)
    {
        // set configuration
        $this->setOptions($options);
        $this->_showBranches = isset($options->showBranches) && !!$options->showBranches ? true : false;
    }

    /**
     * setter for configuration options
     *
     * @param String $name the name of the option
     * @param Mixed $value values of the option
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

        foreach($instance_methods as $method_name) {
            if (\substr($method_name, 0, 3) === 'get' && !isset($inherited_methods[$method_name])) {
                $column_name = \substr($method_name, 3);
                $column_name = \strtolower(\substr($column_name, 0, 1)) . \substr($column_name, 1);
                $options[$column_name] = $this->$method_name();
            }
        }

        return $options;
    }

    /** alias for getOptions() */
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
        $class_name = \get_class($this);
        return \substr($class_name, \strrpos($class_name, '\\') + 1);
    }

    /**
     * return name of the project
     *
     * @return String project name
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * set name of the project
     *
     * @param String $name project name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * get description of project
     *
     * @return String description
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * set description
     *
     * @param String $description description
     */
    public function setDescription($description)
    {
        $this->_description = $description;
    }

    /**
     * get path
     *
     * @return String path
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * set path
     *
     * @param String $path path
     */
    public function setPath($path)
    {
        $this->_path = $path;
    }

    /**
     * returns if showbranches should be visible
     *
     * @return Boolean show branches
     */
    public function showBranches()
    {
        return $this->_showBranches;
    }

    /**
     * get remotes
     *
     * @return Array remotes
     */
    public function getRemotes()
    {
        return $this->_remotes;
    }

    /**
     * register a remote
     *
     * @param \Gitty\Remote $remote a remote instance
     */
    public function registerRemote(\Gitty\Remote $remote)
    {
        $this->_remotes[] = $remote;
    }

    /**
     * unregister a remote
     *
     * @param \Gitty\Remote $remote a remote instance
     */
    public function unregisterRemote(\Gitty\Remote $remote)
    {
        $index = \array_search($remote, $this->_remotes, true);
        unset($this->_remotes[$index]);
        return !!$index;
    }

    /**
     * get owner of the project
     *
     * @abstract
     * @return String the owner's name
     */
    abstract public function getOwner();

    /**
     * set owner of the project
     *
     * @abstract
     * @param String $owner the owner's name
     */
    abstract public function setOwner($owner);

    /**
     * get last change date
     *
     * @abstract
     * @return \DataTime datetime object with date/time
     */
    abstract public function getLastChange();

    /**
     * set last change date
     *
     * @abstract
     * @param \DateTime $datetime a DateTime object
     */
    abstract public function setLastChange(\DateTime $datetime);

    /**
     * get branches
     *
     * @abstract
     * @return Array branches
     */
    abstract public function getBranches();

    /**
     * set branches
     *
     * @abstract
     * @param Array $branches branches
     */
    abstract public function setBranches($branches);

    /**
     * get updated files
     *
     * @param String $uid an unique id that represent the version
     * @return Array the files
     */
    abstract public function getUpdateFiles($uid);

    /**
     * get all files for installation
     *
     * @return Array the files
     */
    abstract public function getInstallFiles();
}
